<?php

declare(strict_types=1);

namespace App\Jobs\Firebase;

use App\Models\Task;

class UploadTaskJob extends FirebaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $taskId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $task = Task::find($this->taskId);
        if (!$task) {
            throw new \Exception("Task with id {$this->taskId} does not exist anymore", 1);
        }
        parent::handle();
        $this->firebase->uploadTask($task);
    }
}
