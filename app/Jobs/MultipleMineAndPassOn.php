<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ShipNavStatus;
use App\Enums\SurveySizes;
use App\Enums\TaskTypes;
use App\Models\Cargo;
use App\Models\Ship;
use App\Models\Survey;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;

class MultipleMineAndPassOn extends MultipleShipsJob implements ShouldBeUniqueUntilProcessing
{
    protected ?EloquentCollection $companions = null;

    private string $extractionLocation = '';

    private ?Survey $survey = null;

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
        $this->initSurveyor();
        $this->log("extraction location: {$this->extractionLocation}");
        $this->initCompanions();

        if ($this->noCompanionPresent() && $this->ships->every(fn (Ship $ship) => $ship->is_fully_loaded)) {
            $this->log('no companion present and all ships are fully loaded, self dispatching...');
            $this->selfDispatch()->delay(60);

            return;
        }

        $this->ships->each(fn (Ship $ship) => $this->handleShip($ship));
        $this->log('done handling ships, self dispatching...');
        $cooldown = $this->ships
            ->first()
            ?->refresh()
            ?->cooldown
            ?: 69;
        $this->selfDispatch()->delay($cooldown);
        $this->log("cooldown: {$cooldown}");
    }

    protected function initCompanions(): void
    {
        /** @var ?Task */
        $companionTask = Task::firstWhere([
            'type' => TaskTypes::SUPPORT_COLLECTIVE_MINERS,
            'payload->waiting_location' => $this->extractionLocation,
        ]);

        if (!$companionTask) {
            $this->log('no companion task available');

            return;
        }

        $this->companions = $companionTask->ships
            ->reject(function (Ship $companion) {
                $result = $companion->is_fully_loaded
                    || $companion->is_in_transit
                    || $companion->cooldown
                    || $companion->waypoint_symbol !== $this->extractionLocation;

                if ($result) {
                    $this->log("companion {$companion->symbol} is not available");
                    WaitAndSell::dispatch($companion->symbol);
                }

                return $result;
            });
    }

    protected function initSurveyor(): void
    {
        $surveyors = Ship::canSurvey()
            ->where([
                'waypoint_symbol' => $this->extractionLocation,
                'cooldown' => 0,
            ])
            ->whereNot('status', ShipNavStatus::IN_TRANSIT)
            ->get();

        if ($surveyors->isEmpty()) {
            $this->log('no surveyor available');
        } else {
            $surveyors->each(function (Ship $surveyor) {
                $this->log("surveyor {$surveyor->symbol} is available");
                $surveyor->survey();
            });
        }

        /** @var Builder */
        $query = Survey::orderByDesc('expiration');

        $this->survey = $query->clone()
            ->firstwhere([
                'waypoint_symbol' => $this->extractionLocation,
                'size' => SurveySizes::LARGE,
            ])
            ?? $query->clone()->firstwhere([
                'waypoint_symbol' => $this->extractionLocation,
                'size' => SurveySizes::MODERATE,
            ])
            ?? $query->clone()->firstwhere([
                'waypoint_symbol' => $this->extractionLocation,
                'size' => SurveySizes::SMALL,
            ]);
    }

    protected function performExtraction(Ship $ship): void
    {
        if ($this->survey) {
            $this->log("extracting resources with survey {$this->survey->id}");
            $ship->extractResourcesWithSurvey($this->survey)
                ->refresh();
        } else {
            $this->log('extracting resources normally');
            $ship = $ship->extractResources()
                ->refresh();
        }
    }

    private function handleShip(Ship $ship): void
    {
        $this->log('handling', $ship);

        if ($ship->waypoint_symbol !== $this->extractionLocation) {
            $ship->navigateTo($this->extractionLocation);
            $this->log("navigate to {$this->extractionLocation}", $ship);

            return;
        }
        $this->transferCargoToCompanionShip($ship);
        if ($ship->is_fully_loaded) {
            $this->log('is fully loaded, cannot extract resources', $ship);

            return;
        }
        $this->performExtraction($ship);
        $this->log('done extracting resources');
        $this->transferCargoToCompanionShip($ship->refresh());
    }

    private function transferCargoToCompanionShip(Ship $ship): void
    {
        $this->log('transferring cargo to companion', $ship);
        if ($this->noCompanionPresent()) {
            $this->log('no companion present', $ship);

            return;
        }

        $ship->cargos->each(fn (Cargo $cargo) => $this->handleTransferCargoToCompanionShip($ship, $cargo));
    }

    private function handleTransferCargoToCompanionShip(Ship $ship, Cargo $cargo): void
    {
        $this->log("handling {$cargo->units} units of cargo {$cargo->symbol->value}", $ship);

        /** @var Ship */
        $companion = $this->companions->firstWhere(
            fn (Ship $ship) => !$ship->refresh()->is_fully_loaded
                // prefer ship already loaded with this cargo
                && $ship->isLoadedWith($cargo->symbol)
        )
            ?? $this->companions
                ->sortByDesc('cargo_units') // prefer fullest for efficiency
                ->firstWhere(fn (Ship $ship) => !$ship->is_fully_loaded);

        if (!$companion) {
            $this->log('no companion present', $ship);

            return;
        }

        $this->log("companion: {$companion->symbol}", $ship);
        $ship->transferCargoTo($companion, $cargo);
        $companion = $companion->refresh();

        if ($companion->is_fully_loaded) {
            $this->log("companion {$companion->symbol} is fully loaded now", $ship);
            WaitAndSell::dispatch($companion->symbol);
            $this->companions = $this->companions->whereNotIn('id', [$companion->id]);
        }

        // check if all cargo coult be transferred to compaion or if there is still cargo left to transfer to a companion
        if ($cargo = $ship->cargos()->firstWhere('symbol', $cargo->symbol)) {
            $this->log("still has {$cargo->units} units of {$cargo->symbol->value} left to transfer to a companion", $ship);
            $this->handleTransferCargoToCompanionShip($ship, $cargo);
        }
    }

    private function noCompanionPresent(): bool
    {
        return $this->companions->isEmpty();
    }
}
