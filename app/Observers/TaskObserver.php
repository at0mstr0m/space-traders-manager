<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Firebase\DeleteTaskJob;
use App\Jobs\Firebase\UploadTaskJob;
use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        UploadTaskJob::dispatch($task->id)->afterResponse();
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        UploadTaskJob::dispatch($task->id)->afterResponse();
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        $key = $task->fireBaseReference?->key;
        DeleteTaskJob::dispatch($key)->afterResponse();
    }
}
