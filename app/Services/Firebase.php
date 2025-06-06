<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use App\Models\System;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;

class Firebase
{
    private const EXPIRES_AT_KEY = 'firebase:expires_at';

    private const USER_ID_KEY = 'firebase:user_id';

    private Auth $auth;

    private Carbon $expiresAt;

    private string $userId;

    private Database $database;

    public function __construct()
    {
        $this->auth = app('firebase.auth');
        $this->expiresAt = Cache::get(static::EXPIRES_AT_KEY, now()->subSecond());
        if ($this->expiresAt->isBefore(now())) {
            $this->signIn();
        } else {
            $this->userId = Cache::get(static::USER_ID_KEY);
        }
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

    public function getPotentialTradeRouteData(): Collection
    {
        return collect($this->potentialTradeRouteReference()->getValue());
    }

    public function uploadPotentialTradeRoute(
        PotentialTradeRoute $potentialTradeRoute
    ): Reference {
        $data = [
            ...$potentialTradeRoute->only([
                'trade_symbol',
                'origin',
                'destination',
            ]),
            'ship_symbol' => $potentialTradeRoute->ship?->symbol,
        ];

        if ($potentialTradeRoute->fireBaseReference()->exists()) {
            return $this->potentialTradeRouteReference(
                $potentialTradeRoute->fireBaseReference->key
            )->set($data);
        }

        // let the database generate a new key
        $newKey = $this->potentialTradeRouteReference()->push()->getKey();
        // save key for future updates
        $potentialTradeRoute->fireBaseReference()->create(['key' => $newKey]);

        return $this->potentialTradeRouteReference($newKey)->set($data);
    }

    public function deletePotentialTradeRoute(string $key): void
    {
        $this->potentialTradeRouteReference($key)->remove();
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

    public function uploadSystem(System $system): Reference
    {
        $data = $system->only($system->getFillable());
        $data['connections'] = $system->connections->pluck('symbol')->all();

        return $this->systemReference($data['symbol'])
            ->set($data);
    }

    public function getSystemSymbols(): Collection
    {
        return collect($this->systemReference()->getChildKeys());
    }

    public function getSystem(string $systemSymbol): Collection
    {
        return collect($this->systemReference($systemSymbol)->getValue());
    }

    public function deleteAll(): Reference
    {
        return $this->database
            ->getReference()
            ->remove();
    }

    private function potentialTradeRouteReference(string $path = ''): Reference
    {
        return $this->database
            ->getReference(
                'potential_trade_routes/'
                . $this->userId
                . '/'
                . $path
            );
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

    private function systemReference(string $systemSymbol = ''): Reference
    {
        return $this->database
            ->getReference('systems/' . $systemSymbol);
    }

    private function signIn(): void
    {
        /** @var SignInResult */
        $signInResult = $this->auth->signInWithEmailAndPassword(
            env('FIREBASE_USER_EMAIL'),
            env('FIREBASE_USER_PASSWORD')
        );

        $this->expiresAt = now()->addSeconds($signInResult->ttl());
        Cache::put(
            static::EXPIRES_AT_KEY,
            $this->expiresAt,
            $this->expiresAt,
        );
        $this->userId = Cache::remember(
            static::USER_ID_KEY,
            $this->expiresAt,
            fn () => $signInResult->firebaseUserId(),
        );
    }
}
