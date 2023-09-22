<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cargo extends Model
{
    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}
