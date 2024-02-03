<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FactionTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FactionTrait extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'symbol' => FactionTraits::class,
        'name' => 'string',
        'description' => 'string',
    ];

    public function faction()
    {
        return $this->belongsToMany(Faction::class);
    }
}
