<?php

declare(strict_types=1);

namespace App\Services\Employees;

use App\DTOs\EmployeeEmailVerificationData;
use App\Mail\EmployeeVerificationCode;
use App\Repositories\Contracts\EmployeeEmailVerificationRepositoryInterface;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EmployeeEmailVerificationService extends BaseService
{
    public function __construct(private EmployeeEmailVerificationRepositoryInterface $repository) {}

    public function send(EmployeeEmailVerificationData $data): array
    {
        abort_if(!app()->environment('testing') && in_array(config('mail.mailers.'.config('mail.default').'.transport'), ['log', 'array', 'failover', 'roundrobin', null], true), 503, 'A delivery mailer must be configured.');

        DB::transaction(function () use ($data): void {
            $this->repository->lockRequester($data->userId);
            $record = $this->repository->find($data->userId, $data->email);
            abort_if($record && Carbon::parse($record->created_at)->addSeconds(60)->isFuture(), 429, 'Please wait 60 seconds before requesting another code.');
            $code = sprintf('%06d', random_int(0, 999999));
            $this->repository->save($data->userId, $data->email, [
                'code_hash' => Hash::make($code), 'attempts' => 0,
                'expires_at' => now()->addMinutes(15), 'verified_at' => null, 'token_hash' => null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            try {
                Mail::to($data->email)->send(new EmployeeVerificationCode($code));
            } catch (\Throwable $e) {
                Log::error('Employee verification email delivery failed.');
                abort(503, 'Verification email could not be sent. Please contact your administrator.');
            }
        });

        return ['status' => true, 'email' => $data->email, 'expires_in_minutes' => 15, 'resend_after_seconds' => 60, 'message' => 'Verification code sent. Check the employee email inbox and spam folder.'];
    }

    public function verify(EmployeeEmailVerificationData $data): array
    {
        $token = DB::transaction(function () use ($data): ?string {
            $record = $this->repository->find($data->userId, $data->email);
            if (!$record || Carbon::parse($record->expires_at)->isPast() || $record->attempts >= 5 || $record->verified_at) return null;
            if (!Hash::check($data->code, $record->code_hash)) {
                $this->repository->update($record->id, ['attempts' => $record->attempts + 1, 'updated_at' => now()]);
                return null;
            }
            $token = bin2hex(random_bytes(32));
            $this->repository->update($record->id, ['token_hash' => hash('sha256', $token), 'verified_at' => now(), 'updated_at' => now()]);
            return $token;
        });
        if (!$token) throw ValidationException::withMessages(['code' => 'Invalid, expired or already used code. Request a new code after five failed attempts.']);

        return ['status' => true, 'verified' => true, 'verification_token' => $token, 'message' => 'Email verified. You can now register this employee.'];
    }

    // Called inside the employee creation transaction: rollback preserves proof if creation fails.
    public function consume(int $userId, string $email, string $token): void
    {
        $record = $this->repository->find($userId, $email);
        if (!$record || !$record->verified_at || Carbon::parse($record->expires_at)->isPast()
            || !$record->token_hash || !hash_equals($record->token_hash, hash('sha256', $token))) {
            throw ValidationException::withMessages(['email_verification_token' => 'Verify this email with a new code before registering the employee.']);
        }
        $this->repository->delete($record->id);
    }
}
