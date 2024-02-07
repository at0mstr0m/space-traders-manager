<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskTypes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'type',
        'payload',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'type' => TaskTypes::class,
        'payload' => 'json',
    ];

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }
}
