<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorySetup extends Model
{
    protected $table = 'factory_setups';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['details' => 'array', 'active' => 'boolean'];
    }
}
