<?php

declare(strict_types=1);

namespace App\DTO;

class PayrollImportDTO extends BaseDTO
{
    public function __construct(public string $month, public string $title, public ?string $sourceName, public array $rows, public array $bank) {}

    public static function fromArray(array $data): static
    {
        return new static($data['month'], $data['title'], $data['source_name'] ?? null, $data['rows'], array_intersect_key($data, array_flip(['company_address', 'bank_name', 'bank_branch', 'debit_account', 'signatory', 'signatory_title'])));
    }

    public function toArray(): array
    {
        return ['month' => $this->month, 'title' => $this->title, 'source_name' => $this->sourceName] + $this->bank;
    }
}
