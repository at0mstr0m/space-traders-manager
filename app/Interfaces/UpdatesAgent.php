<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\Agent;

interface UpdatesAgent
{
    public function updateAgent(Agent $agent): Agent;
}
