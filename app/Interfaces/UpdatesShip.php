<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Ship;

interface UpdatesShip
{
    public function updateShip(Ship $ship): Ship;
}
