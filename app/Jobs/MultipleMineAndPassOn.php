<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
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
        dump(now()->toTimeString() . " extraction location: {$this->extractionLocation}");
        $this->initCompanions();

        if ($this->noCompanionPresent() && $this->ships->every(fn (Ship $ship) => $ship->is_fully_loaded)) {
            dump(now()->toTimeString() . ' no companion present and all ships are fully loaded, self dispatching...');
            $this->selfDispatch()->delay(60);

            return;
        }

        $this->ships->each(fn (Ship $ship) => $this->handleShip($ship));
        dump(now()->toTimeString() . ' done handling ships, self dispatching...');
        $this->selfDispatch()->delay($this->ships->max('cooldown'));
        dump(now()->toTimeString() . " cooldown: {$this->ships->max('cooldown')}");
    }

    private function handleShip(Ship $ship): void
    {
        dump(now()->toTimeString() . " handling {$ship->symbol}");

        if ($ship->waypoint_symbol !== $this->extractionLocation) {
            $ship->navigateTo($this->extractionLocation);
            dump(now()->toTimeString() . " navigate {$ship->symbol} to {$this->extractionLocation}");

            return;
        }
        $this->transferCargoToCompanionShip($ship);
        if ($ship->is_fully_loaded) {
            dump(now()->toTimeString() . " {$ship->symbol} is fully loaded, cannot extract resources");

            return;
        }
        if ($this->survey) {
            dump(now()->toTimeString() . " {$ship->symbol} extracting resources with survey {$this->survey->id}");
            $ship->extractResourcesWithSurvey($this->survey)->refresh();
        } else {
            dump(now()->toTimeString() . " {$ship->symbol} extracting resources normally");
            $ship = $ship->extractResources()->refresh();
        }
        dump(now()->toTimeString() . " {$ship->symbol} done extracting resources");
        $this->transferCargoToCompanionShip($ship);
    }

    private function transferCargoToCompanionShip(Ship $ship): void
    {
        dump(now()->toTimeString() . " {$ship->symbol} transferring cargo to companion");
        if ($this->noCompanionPresent()) {
            dump(now()->toTimeString() . " {$ship->symbol} no companion present");

            return;
        }

        $ship->cargos->each(fn (Cargo $cargo) => $this->handleTransferCargoToCompanionShip($ship, $cargo));
    }

    private function handleTransferCargoToCompanionShip(Ship $ship, Cargo $cargo): void
    {
        dump(now()->toTimeString() . " handling cargo {$cargo->symbol->value} for {$ship->symbol}");
        if ($this->noCompanionPresent()) {
            dump(now()->toTimeString() . " {$ship->symbol} no companion present");

            return;
        }

        /** @var Ship */
        $companion = $this->companions
            ->sortByDesc('cargo_units') // prefer fullest for efficiency
            ->first();

        if (!$companion) {
            dump(now()->toTimeString() . " {$ship->symbol} no companion present");

            return;
        }
        dump(now()->toTimeString() . " companion: {$companion->symbol}");
        $companion = $companion->refresh();

        if ($companion->is_fully_loaded) {
            dump(now()->toTimeString() . " companion {$companion->symbol} is fully loaded already");
            WaitAndSell::dispatch($companion->symbol);
            $this->companions = $this->companions->whereNotIn('id', [$companion->id]);

            return;
        }

        $ship->transferCargoTo($companion, $cargo);
        $companion = $companion->refresh();

        if ($companion->is_fully_loaded) {
            dump(now()->toTimeString() . " companion {$companion->symbol} is fully loaded now");
            WaitAndSell::dispatch($companion->symbol);
            $this->companions = $this->companions->whereNotIn('id', [$companion->id]);
        }
    }

    private function initCompanions(): void
    {
        /** @var ?Task */
        $companionTask = Task::firstWhere([
            'type' => TaskTypes::SUPPORT_COLLECTIVE_MINERS,
            'payload->waiting_location' => $this->extractionLocation,
        ]);

        if (!$companionTask) {
            dump(now()->toTimeString() . ' no companion task available');

            $this->companions = new EloquentCollection();

            return;
        }

        $this->companions = $companionTask->ships
            ->reject(function (Ship $companion) {
                $result = $companion->is_fully_loaded
                    || $companion->is_in_transit
                    || $companion->cooldown
                    || $companion->waypoint_symbol !== $this->extractionLocation;

                if ($result) {
                    dump(now()->toTimeString() . " companion {$companion->symbol} is not available");
                    WaitAndSell::dispatch($companion->symbol);
                }

                return $result;
            });
    }

    private function noCompanionPresent(): bool
    {
        return $this->companions->isEmpty();
    }

    private function initSurveyor(): void
    {
        $surveyor = Ship::firstWhere([
            'waypoint_symbol' => $this->extractionLocation,
            'role' => ShipRoles::SURVEYOR,
            'status' => ShipNavStatus::IN_ORBIT,
            'cooldown' => 0,
        ]);

        if ($surveyor) {
            dump(now()->toTimeString() . " surveyor {$surveyor->symbol} is available");
            $surveyor->survey();
        } else {
            dump(now()->toTimeString() . ' no surveyor available');
        }

        /** @var Builder */
        $query = Survey::orderBy('expiration');

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
}
