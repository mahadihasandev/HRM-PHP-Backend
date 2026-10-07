<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactoryProduction extends Model
{
    protected $table = 'factory_production';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
