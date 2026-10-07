<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrRecordFile extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['path'];
}
