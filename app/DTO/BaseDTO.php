<?php

declare(strict_types=1);

namespace App\DTO;

use Illuminate\Http\Request;

abstract class BaseDTO
{
    /**
     * Convert DTO into an array.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * Create DTO instance from an associative array.
     *
     * @param array<string, mixed> $data
     * @return static
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Create DTO instance from an incoming HTTP Request.
     *
     * @param Request $request
     * @return static
     */
    public static function fromRequest(Request $request): static
    {
        $data = method_exists($request, 'validated') ? $request->validated() : $request->all();

        return static::fromArray($data);
    }
}
