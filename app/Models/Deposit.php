<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositSymbols;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Deposit.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property DepositSymbols $symbol
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Module> $modules
 * @property-read int|null $modules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mount> $mounts
 * @property-read int|null $mounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Survey> $surveys
 * @property-read int|null $surveys_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Deposit extends Model
{
    protected $fillable = [
        'symbol',
    ];

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class);
    }

    public function mounts(): BelongsToMany
    {
        return $this->belongsToMany(Mount::class);
    }

    public function surveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'symbol' => DepositSymbols::class,
        ];
    }
}
