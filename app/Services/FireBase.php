<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;

class FireBase
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

    public function getTasks()
    {
        return $this->database
            ->getReference('jobs/' . $this->userId)
            ->getValue();
    }

    public function uploadTask(Task $task): Reference
    {
        $data = $task->only(['payload', 'type']);

        if ($task->fireBaseReference()->exists()) {
            return $this->database
                ->getReference('tasks/' . $this->userId . '/' . $task->fireBaseReference->key)
                ->set($data);
        }

        $newKey = $this->database
            ->getReference('tasks/' . $this->userId)
            ->push()
            ->getKey();

        $task->fireBaseReference()->create(['key' => $newKey]);

        return $this->database
            ->getReference('tasks/' . $this->userId . '/' . $newKey)
            ->set($data);
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
