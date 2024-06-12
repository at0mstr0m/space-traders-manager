<?php

declare(strict_types=1);

namespace App\Actions\Firebase;

use App\Models\Ship;
use App\Models\Task;
use App\Services\Firebase;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadTaskShipRelationsAction
{
    use AsAction;

    private Firebase $firebase;

    public function __construct()
    {
        $this->firebase = app(Firebase::class);
    }

    public function handle(bool $purge = false)
    {
        if ($purge) {
            Ship::getQuery()->update(['task_id' => null]);
        }

        $this->firebase
            ->getShipTaskRelations()
            ->each(function (string $taskKey, string $shipSymbol) {
                $taskId = Task::whereRelation('fireBaseReference', 'key', $taskKey)
                    ->pluck('id')
                    ->first();

                Ship::query()
                    ->where('symbol', $shipSymbol)
                    ->update(['task_id' => $taskId]);
            });
    }
}
