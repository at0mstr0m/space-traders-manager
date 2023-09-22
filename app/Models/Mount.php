<?php

declare(strict_types=1);

namespace App\Models;

class Mount extends Model
{
    public function ships()
    {
        return $this->belongsToMany(Ship::class);
    }
}
