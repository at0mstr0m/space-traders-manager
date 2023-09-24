<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContractTypes;
use App\Enums\FactionSymbols;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'identification',
        'faction_symbol',
        'type',
        'fulfilled',
        'deadline',
        'payment_on_accepted',
        'payment_on_fulfilled',
    ];

    protected $casts = [
        'identification' => 'string',
        'faction_symbol' => FactionSymbols::class,
        'type' => ContractTypes::class,
        'fulfilled' => 'boolean',
        'deadline' => 'datetime',
        'payment_on_accepted' => 'integer',
        'payment_on_fulfilled' => 'integer',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
