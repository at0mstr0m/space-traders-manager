<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Frame extends Model
{
    public function ships()
    {
        return $this->hasMany(Ship::class);
    }
}
