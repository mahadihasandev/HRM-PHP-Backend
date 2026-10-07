<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\FactoryProduction;
use App\Models\FactorySafety;
use App\Models\FactorySetup;
use App\Repositories\Contracts\FactoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FactoryRepository implements FactoryRepositoryInterface
{
    private function model(string $type): string
    {
        return match ($type) {
            'setup' => FactorySetup::class, 'production' => FactoryProduction::class, 'safety' => FactorySafety::class
        };
    }

    public function list(int $companyId, string $type): Collection
    {
        $model = $this->model($type);

        return $model::where('company_id', $companyId)->orderByDesc('id')->limit(1000)->get();
    }

    public function save(int $companyId, string $type, array $data, ?int $id = null): Model
    {
        $model = $this->model($type);
        if ($id) {
            $record = $model::where('company_id', $companyId)->findOrFail($id);
            $record->update($data);

            return $record->refresh();
        }

        return $model::create($data + ['company_id' => $companyId]);
    }

    public function setup(int $companyId, string $kind, string $code): ?Model
    {
        return FactorySetup::where('company_id', $companyId)->where('kind', $kind)->where('code', $code)->where('active', true)->first();
    }
}
