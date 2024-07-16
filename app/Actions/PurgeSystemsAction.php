<?php

namespace App\Actions;

use App\Models\Agent;
use App\Models\System;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PurgeSystemsAction
{
    use AsAction;

    public function handle()
    {
        $id = Agent::first()->starting_system->id;
        DB::table('system_connections')->truncate();
        DB::table('faction_system')
            ->whereNot('system_id', $id)
            ->truncate();
        System::whereNot('id', $id)->delete();
    }
}
