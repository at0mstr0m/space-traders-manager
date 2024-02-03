<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SurveySizes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Survey extends Model
{
    protected $fillable = [
        'signature',
        'waypoint_symbol',
        'expiration',
        'size',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signature' => 'string',
        'waypoint_symbol' => 'string',
        'expiration' => 'datetime',
        'size' => SurveySizes::class,
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
            'expiration' => $this->expiration->toDateTimeString(),
            'size' => $this->size->value,
            'deposits' => $this->deposits
                ->map(
                    fn (Deposit $deposit) => ['symbol' => $deposit->symbol->value]
                )->all(),
        ];
    }
}
