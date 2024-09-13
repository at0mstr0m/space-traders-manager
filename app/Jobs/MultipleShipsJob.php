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
use Illuminate\Queue\Middleware\WithoutOverlapping;
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
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->taskId, 60, 60)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->log('START');
        $this->release();
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
        $this->log('END');
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        $this->log('FAILED :' . $exception ? $exception->getMessage() : 'null');
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
        /** @var Task */
        $task = is_int($task) ? Task::find($task) : $task;

        $pages = $task->ships()
            ->getBaseQuery()
            ->select(['page'])
            ->distinct()
            ->pluck('page');

        // some ship's page is not available yet, must refetch all
        if ($pages->contains(0)) {
            UpdateShips::dispatchSync();
        } else {  // only refetch certain pages when this is more efficient
            UpdateShips::dispatchSync($pages);
        }

        return $task->ships
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
