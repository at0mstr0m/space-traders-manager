<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Task;
use App\Services\FireBase;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->uploadTask($task);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $this->uploadTask($task);
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        $key = $task->fireBaseReference?->key;

        dispatch(function () use ($key) {
            /** @var FireBase */
            $firebase = app(FireBase::class);
            $firebase->deleteTask($key);
        })->afterResponse();
    }

    private function uploadTask(Task $task): void
    {
        dispatch(function () use ($task) {
            /** @var FireBase */
            $firebase = app(FireBase::class);
            $firebase->uploadTask($task);
        })->afterResponse();
    }
}
