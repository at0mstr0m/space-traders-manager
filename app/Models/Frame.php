<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Frame extends Model
{
    use FindableBySymbol;

    public function ships()
    {
        return $this->hasMany(Ship::class);
    }
}
