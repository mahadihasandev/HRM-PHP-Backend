<?php

declare(strict_types=1);

namespace App\DTO;

class PeopleRecordDTO extends BaseDTO
{
    public function __construct(public array $data) {}

    public function toArray(): array
    {
        return $this->data;
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }
}
