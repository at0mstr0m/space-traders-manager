<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Model;

interface WithModelInstance
{
    public function makeModelInstance(): Model;
}
