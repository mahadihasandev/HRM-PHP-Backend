<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\HrRecord;
use App\Repositories\Contracts\PeopleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PeopleRepository implements PeopleRepositoryInterface
{
    public function records(int $companyId): Builder
    {
        return HrRecord::where('company_id', $companyId);
    }

    public function record(int $companyId, int $id, bool $lock = false): HrRecord
    {
        $query = $this->records($companyId)->whereKey($id);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    public function employees(int $companyId): Collection
    {
        return DB::table('employees')->where('company_id', $companyId)->where('status', 'Active')
            ->orderBy('name')->get(['employee_full_id', 'name', 'department']);
    }

    public function employee(int $companyId, string $fullId, bool $lock = false): ?object
    {
        $query = DB::table('employees')->where('company_id', $companyId)->where('employee_full_id', $fullId);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }
}
