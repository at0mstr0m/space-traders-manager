<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ship;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;

class Firebase
{
    private Auth $auth;

    private Carbon $expiresAt;

    private string $userId;

    private Database $database;

    public function __construct()
    {
        $this->auth = app('firebase.auth');
        // todo: use from cache or sign in
        $this->signIn();
        $this->database = app('firebase.database');
    }

    public function getTaskData(): Collection
    {
        return collect($this->taskReference()->getValue());
    }

    public function uploadTask(Task $task): Reference
    {
        $data = $task->only(['payload', 'type']);

        if ($task->fireBaseReference()->exists()) {
            return $this->taskReference($task->fireBaseReference->key)
                ->set($data);
        }

        // let the database generate a new key
        $newKey = $this->taskReference()->push()->getKey();
        // save key for future updates
        $task->fireBaseReference()->create(['key' => $newKey]);

        return $this->taskReference($newKey)->set($data);
    }

    public function deleteTask(string $key): void
    {
        $this->taskReference($key)->remove();
    }

    public function getShipTaskRelations(): Collection
    {
        return collect($this->shipTaskReference()->getValue());
    }

    public function setShipTaskRelation(Ship $ship): Reference
    {
        return $this->shipTaskReference($ship->symbol)
            ->set($ship?->task?->fireBaseReference?->key);
    }

    private function taskReference(string $path = ''): Reference
    {
        return $this->database
            ->getReference('tasks/' . $this->userId . '/' . $path);
    }

    private function shipTaskReference(string $path = ''): Reference
    {
        return $this->database
            ->getReference('ship_task/' . $this->userId . '/' . $path);
    }

    private function signIn(): void
    {
        /** @var SignInResult */
        $signInResult = $this->auth->signInWithEmailAndPassword(
            env('FIREBASE_USER_EMAIL'),
            env('FIREBASE_USER_PASSWORD')
        );

        $this->userId = $signInResult->firebaseUserId();
        $this->expiresAt = now()->addSeconds($signInResult->ttl());
    }
}
