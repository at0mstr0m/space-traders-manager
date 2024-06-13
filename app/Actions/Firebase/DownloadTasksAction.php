<?php

declare(strict_types=1);

namespace App\Actions\Firebase;

use App\Models\FireBaseReference;
use App\Models\Task;
use App\Services\Firebase;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadTasksAction
{
    use AsAction;

    private Firebase $firebase;

    public function __construct()
    {
        $this->firebase = app(Firebase::class);
    }

    public function handle(bool $purge = false)
    {
        // using deleteQuietly, to not trigger TaskObserver
        if ($purge) {
            FireBaseReference::query()->delete();
            Task::getQuery()->delete();
        }

        $this->firebase
            ->getTaskData()
            ->each(function (array $taskData, string $id) {
                $reference = FireBaseReference::firstWhere(['key' => $id]);
                if ($reference) {
                    $reference->model->updateQuietly($taskData);

                    return;
                }
                $task = Task::new($taskData);
                $task->saveQuietly();
                $task->fireBaseReference()->create(['key' => $id]);
            });
    }
}
