<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'symbol',
        'headquarters',
        'credits',
        'starting_faction',
        'ship_count',
    ];

    protected $casts = [
        'account_id' => 'string',
        'symbol' => 'string',
        'headquarters' => 'string',
        'credits' => 'integer',
        'starting_faction' => 'string',
        'ship_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
