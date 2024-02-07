<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TaskTypes;
use App\Models\Cargo;
use App\Models\Ship;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MultipleMineAndPassOn extends MultipleShipsJob implements ShouldBeUniqueUntilProcessing
{
    protected ?EloquentCollection $companions = null;

    private string $extractionLocation = '';

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $taskId)
    {
        $this->constructorParams = func_get_args();
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return static::class . ':' . $this->taskId;
    }

    /**
     * Execute the job.
     */
    protected function handleShips(): void
    {
        $this->extractionLocation = $this->task->payload['extraction_location'];
        dump("extraction location: {$this->extractionLocation}");
        /** @var Task */
        $companionTask = Task::firstWhere([
            'type' => TaskTypes::SUPPORT_COLLECTIVE_MINERS,
            'payload->waiting_location' => $this->extractionLocation,
        ]);

        dump($companionTask->attributesToArray());

        $this->companions = $companionTask->ships
            ->reject(function (Ship $companion) {
                $result = $companion->is_fully_loaded
                    || $companion->is_in_transit
                    || $companion->cooldown
                    || $companion->waypoint_symbol !== $this->extractionLocation;

                if ($result) {
                    dump("companion {$companion->symbol} is not available");
                    WaitAndSell::dispatch($companion->symbol);
                }

                return $result;
            });
        $this->ships->each(fn (Ship $ship) => $this->handleShip($ship));
        dump('done handling ships, self dispatching...');
        $this->selfDispatch()->delay($this->ships->max('cooldown'));
        dump("cooldown: {$this->ships->max('cooldown')}");
    }

    private function handleShip(Ship $ship): void
    {
        dump("handling {$ship->symbol}");
        if ($ship->waypoint_symbol !== $this->extractionLocation) {
            $ship->navigateTo($this->extractionLocation);
            dump("navigate {$ship->symbol} to {$this->extractionLocation}");

            return;
        }
        $this->transferCargoToCompanionShip($ship);
        if ($ship->is_fully_loaded) {
            dump("{$ship->symbol} is fully loaded, cannot extract resources");

            return;
        }
        dump("{$ship->symbol} extracting resources");
        $ship = $ship->extractResources()->refresh();
        dump("{$ship->symbol} done extracting resources");
        $this->transferCargoToCompanionShip($ship);
    }

    private function transferCargoToCompanionShip(Ship $ship): void
    {
        dump("{$ship->symbol} transferring cargo to companion");
        if ($this->noCompanionPresent()) {
            dump("{$ship->symbol} no companion present");

            return;
        }

        $ship->cargos->each(fn (Cargo $cargo) => $this->handleTransferCargoToCompanionShip($ship, $cargo));
    }

    private function handleTransferCargoToCompanionShip(Ship $ship, Cargo $cargo): void
    {
        dump("handling cargo {$cargo->symbol->value} for {$ship->symbol}");
        if ($this->noCompanionPresent()) {
            dump("{$ship->symbol} no companion present");

            return;
        }

        /** @var Ship */
        $companion = $this->companions
            ->sortByDesc('cargo_units') // prefer fullest for efficiency
            ->first();

        if (!$companion) {
            dump("{$ship->symbol} no companion present");

            return;
        }

        dump("companion: {$companion->symbol}");

        $companion = $companion->refetch();

        if ($companion->is_fully_loaded) {
            WaitAndSell::dispatch($companion->symbol);
            $this->companions = $this->companions->whereNotIn('id', [$companion->id]);
        }

        $ship->transferCargoTo($companion, $cargo);
        $companion = $companion->refetch();

        if ($companion->is_fully_loaded) {
            WaitAndSell::dispatch($companion->symbol);
            $this->companions = $this->companions->whereNotIn('id', [$companion->id]);
        }
    }

    private function noCompanionPresent(): bool
    {
        return $this->companions->isEmpty();
    }
}
