<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrRecord extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['details' => 'array', 'version' => 'integer'];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(HrRecordParticipant::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(HrRecordFile::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(HrRecordEvent::class);
    }
}
