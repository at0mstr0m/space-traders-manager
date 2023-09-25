<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FactionTrait extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    public function faction()
    {
        return $this->belongsToMany(Faction::class);
    }
}
