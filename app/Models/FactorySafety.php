<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorySafety extends Model
{
    protected $table = 'factory_safety';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
