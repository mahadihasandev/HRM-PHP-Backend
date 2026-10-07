<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\DTO\PayrollImportDTO;
use App\Models\PayrollRun;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PayrollRunService extends BaseService
{
    public const EARNINGS = ['basic_salary', 'house_rent', 'medical_allowance', 'conveyance', 'food_allowance', 'attendance_bonus', 'production_bonus', 'festival_bonus'];

    public const DEDUCTIONS = ['absence_deduction', 'loan_deduction', 'pf_deduction', 'tax_deduction', 'other_deductions'];

    public function __construct(private PayrollRepositoryInterface $repository) {}

    public function preview(PayrollImportDTO $dto, object $actor): array
    {
        $employees = $this->repository->employees((int) $actor->company_id);
        $existing = $this->repository->existingEmployees((int) $actor->company_id, $dto->month);
        $seen = [];
        $rows = [];
        $errors = [];
        foreach ($dto->rows as $index => $input) {
            $messages = [];
            $row = [];
            $id = is_scalar($input['employee_full_id'] ?? null) ? trim((string) $input['employee_full_id']) : '';
            $employee = $employees->get($id);
            if (! $employee || $employee->status !== 'Active') {
                $messages[] = 'Employee ID must match an active employee in your company.';
            }
            if (isset($seen[$id])) {
                $messages[] = 'Duplicate employee in this file.';
            }
            if (in_array($id, $existing, true)) {
                $messages[] = 'Employee already has a payroll batch for this month.';
            }
            $seen[$id] = true;
            foreach (array_merge(self::EARNINGS, self::DEDUCTIONS, ['overtime_hours', 'overtime_rate']) as $field) {
                $value = $input[$field] ?? ($field === 'basic_salary' ? null : 0);
                if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0 || (float) $value > 10000000 || round((float) $value, 2) != (float) $value) {
                    $messages[] = "$field must be a non-negative number with at most two decimals (maximum 10,000,000).";
                    $value = 0;
                }
                $row[$field] = round((float) $value, 2);
            }
            if ($row['overtime_hours'] > 744) {
                $messages[] = 'Overtime hours cannot exceed 744 per month.';
            }
            if ($row['overtime_hours'] > 0 && $row['overtime_rate'] <= 0) {
                $messages[] = 'Enter the approved overtime rate for overtime hours.';
            }
            $row['overtime_amount'] = round($row['overtime_hours'] * $row['overtime_rate'], 2);
            if ($row['overtime_amount'] > 1000000000) {
                $messages[] = 'Overtime amount exceeds the supported limit.';
            }
            $row['total_earnings'] = $this->sum($row, self::EARNINGS) + $row['overtime_amount'];
            $row['total_deductions'] = $this->sum($row, self::DEDUCTIONS);
            $row['net_payable'] = round($row['total_earnings'] - $row['total_deductions'], 2);
            if ($row['net_payable'] <= 0) {
                $messages[] = 'Net payable must be greater than zero.';
            }
            if (isset($input['net_payable']) && $input['net_payable'] !== '') {
                if (! is_numeric($input['net_payable']) || abs((float) $input['net_payable'] - $row['net_payable']) > 0.009) {
                    $messages[] = 'Source net payable does not match calculated salary.';
                }
            }
            $row += ['employee_full_id' => $id, 'employee_name' => $employee->name ?? $id, 'department' => $employee->department ?? '', 'designation' => $employee->designation ?? ''];
            foreach (['factory', 'section', 'line', 'grade', 'bank_name', 'bank_account_no', 'routing_no', 'payment_method'] as $field) {
                $fallback = match ($field) {
                    'bank_name' => $employee->bank_name ?? '', 'bank_account_no' => $employee->bank_account_no ?? '', 'routing_no' => $employee->routing_name ?? '', 'payment_method' => 'bank', default => ''
                };
                $value = $input[$field] ?? $fallback;
                if (! is_scalar($value) || strlen((string) $value) > 255) {
                    $messages[] = "$field must be text with at most 255 characters.";
                    $value = '';
                }
                $row[$field] = trim((string) $value);
            }
            if (! in_array($row['payment_method'], ['bank', 'cash'], true)) {
                $messages[] = 'Payment method must be bank or cash.';
            }
            if ($row['payment_method'] === 'bank' && (! $row['bank_name'] || ! preg_match('/^[0-9]{5,34}$/', $row['bank_account_no']) || ! preg_match('/^[0-9]{9}$/', $row['routing_no']))) {
                $messages[] = 'Bank payments require bank name, a 5–34 digit account number, and a 9-digit routing number. Store account numbers as text to retain leading zeroes.';
            }
            if ($messages) {
                $errors[] = ['row' => $index + 2, 'employee_full_id' => $id, 'messages' => $messages];
            }
            $rows[] = $row;
        }

        $summary = $this->summary($rows);
        if ($summary['bank_total'] > 0) {
            $bank = $dto->bank;
            if (empty($bank['bank_name']) || empty($bank['bank_branch']) || ! preg_match('/^[0-9]{5,34}$/', $bank['debit_account'] ?? '') || empty($bank['signatory']) || empty($bank['signatory_title'])) {
                $errors[] = ['row' => 1, 'employee_full_id' => 'Bank forwarding details', 'messages' => ['Bank payroll needs forwarding bank, branch, debit account and authorized signatory details.']];
            }
        }

        return ['rows' => $rows, 'errors' => $errors, 'valid' => count($errors) === 0, 'summary' => $summary];
    }

    public function discard(object $actor, int $id): void
    {
        $paths = DB::transaction(function () use ($actor, $id) {
            $run = $this->repository->run((int) $actor->company_id, $id, true);
            abort_unless($run->status === 'draft', 409, 'Approved and paid payroll cannot be discarded.');
            $paths = $run->attachments->pluck('path')->all();
            $run->delete();

            return $paths;
        });
        Storage::disk('local')->delete($paths);
    }

    private function sum(array $row, array $fields): float
    {
        return array_sum(array_map(fn ($field) => (int) round($row[$field] * 100), $fields)) / 100;
    }

    public function summary(array $rows): array
    {
        $summary = ['employees' => count($rows), 'total_earnings' => 0, 'total_deductions' => 0, 'net_payable' => 0, 'overtime_amount' => 0, 'bank_total' => 0, 'cash_total' => 0];
        foreach ($rows as $row) {
            foreach (['total_earnings', 'total_deductions', 'net_payable', 'overtime_amount'] as $key) {
                $summary[$key] += (int) round((float) $row[$key] * 100);
            }
            $summary[$row['payment_method'] === 'bank' ? 'bank_total' : 'cash_total'] += (int) round((float) $row['net_payable'] * 100);
        }
        foreach (array_keys($summary) as $key) {
            if ($key !== 'employees') {
                $summary[$key] /= 100;
            }
        }

        return $summary;
    }

    public function create(PayrollImportDTO $dto, object $actor, int $userId): PayrollRun
    {
        try {
            return DB::transaction(function () use ($dto, $actor, $userId) {
                $preview = $this->preview($dto, $actor);
                if (! $preview['valid']) {
                    throw ValidationException::withMessages(['rows' => array_map(fn ($error) => 'Row '.$error['row'].': '.implode(' ', $error['messages']), $preview['errors'])]);
                }
                $attributes = $dto->toArray() + ['company_id' => $actor->company_id, 'company_name' => $actor->company, 'created_by' => $userId, 'status' => 'draft'];
                $rows = array_map(fn ($row) => $row + ['company_id' => $actor->company_id, 'month' => $dto->month], $preview['rows']);

                return $this->repository->create($attributes, $rows);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['rows' => 'Another payroll batch already includes this employee for this month. Refresh and review.']);
        }
    }

    public function transition(object $actor, int $id, int $userId, array $data): PayrollRun
    {
        return DB::transaction(function () use ($actor, $id, $userId, $data) {
            $run = $this->repository->run((int) $actor->company_id, $id, true);
            if ($data['action'] === 'approve') {
                abort_unless($run->status === 'draft', 409, 'Only draft payroll can be approved.');
                $hasBank = $run->items->contains('payment_method', 'bank');
                if ($hasBank && (! $run->bank_name || ! $run->bank_branch || ! preg_match('/^[0-9]{5,34}$/', $run->debit_account ?? '') || ! $run->signatory || ! $run->signatory_title)) {
                    throw ValidationException::withMessages(['bank' => 'Bank payroll needs forwarding bank, branch, debit account and authorized signatory details.']);
                }
                $run->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
            } else {
                abort_unless($run->status === 'approved', 409, 'Only approved payroll can be marked paid.');
                $run->update(['status' => 'paid', 'payment_date' => $data['payment_date'], 'payment_reference' => $data['payment_reference']]);
            }

            return $run->refresh()->load('items', 'attachments');
        });
    }

    public function attach(object $actor, int $id, UploadedFile $file): PayrollRun
    {
        $run = $this->repository->run((int) $actor->company_id, $id);
        $path = $file->store('payroll/'.$run->id, 'local');
        if (! $path) {
            throw ValidationException::withMessages(['file' => 'The file could not be saved to private storage.']);
        }
        try {
            $run->attachments()->create(['name' => mb_substr(basename($file->getClientOriginalName()), 0, 255), 'path' => $path, 'size' => $file->getSize()]);
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($path);
            throw $error;
        }

        return $run->load('items', 'attachments');
    }
}
