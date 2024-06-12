<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\FireBaseReference;
use App\Models\Task;
use App\Services\FireBase;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadTasksAction
{
    use AsAction;

    private FireBase $firebase;

    public function __construct()
    {
        $this->firebase = app(FireBase::class);
    }

    public function handle(bool $purge = false)
    {
        // using deleteQuietly, to not trigger TaskObserver
        if ($purge) {
            FireBaseReference::query()->delete();
            Task::query()->deleteQuietly();
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
