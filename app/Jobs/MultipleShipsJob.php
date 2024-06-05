<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\SpaceTraders;
use App\Models\Ship;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class MultipleShipsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected ?Task $task = null;

    protected array $constructorParams = [];

    protected ?SpaceTraders $api = null;

    protected ?EloquentCollection $ships = null;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $taskId)
    {
        $this->constructorParams = func_get_args();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->task = Task::find($this->taskId);
        UpdateShips::dispatchSync();
        $this->ships = $this->initTasksShips($this->task);

        // self dispatch with delay when no ships are available
        if ($this->ships->isEmpty()) {
            $delay = $this->task->ships()->max('cooldown');
            $this->selfDispatch()->delay($delay ?: 60);

            return;
        }

        $this->handleShips();
    }

    abstract protected function handleShips(): void;

    protected function selfDispatch(array $arguments = []): PendingDispatch
    {
        return static::dispatch(
            ...$this->constructorParams,
            ...$arguments
        );
    }

    protected function initApi(): void
    {
        $this->api ??= app(SpaceTraders::class);
    }

    protected function initTasksShips(int|Task $task): EloquentCollection
    {
        return (is_int($task) ? Task::find($task) : $task)
            ->ships
            ->reject(function (Ship $ship) {
                $result = $ship->is_in_transit || $ship->cooldown;
                if ($result) {
                    $this->log('is in transit or on cooldown', $ship);
                }

                return $result;
            });
    }

    protected function log(string $message, ?Ship $ship = null): void
    {
        $symbol = $ship?->symbol;
        Log::channel('ship_jobs')
            ->info(
                ($symbol ? $symbol . ' ' : '')
                . static::class
                . ' "'
                . $message
                . '"'
            );
    }
}
