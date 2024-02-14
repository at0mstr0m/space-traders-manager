<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SurveySizes;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Survey extends Model
{
    use Prunable;

    protected $fillable = [
        'signature',
        'waypoint_symbol',
        'expiration',
        'size',
        'raw_response',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signature' => 'string',
        'waypoint_symbol' => 'string',
        'expiration' => 'datetime',
        'size' => SurveySizes::class,
        'raw_response' => 'string',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(Deposit::class);
    }

    public function toRequestableObject(): array
    {
        return [
            'signature' => $this->signature,
            'symbol' => $this->waypoint_symbol,
            'deposits' => $this->deposits
                ->map(
                    fn (Deposit $deposit) => ['symbol' => $deposit->symbol->value]
                )->all(),
            'expiration' => $this->expiration->toIsoString(),
            'size' => $this->size->value,
        ];
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('expiration', '<=', now()->subMinute());
    }
}
