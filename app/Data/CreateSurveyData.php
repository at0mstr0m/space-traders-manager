<?php

namespace App\Data;

use App\Interfaces\GeneratableFromResponse;
use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CreateSurveyData extends Data implements GeneratableFromResponse, UpdatesShip
{
    public function __construct(
        public int $cooldown,
        #[DataCollectionOf(SurveyData::class)]
        public ?DataCollection $surveys = null,
    ) {}

    public static function fromResponse(array $response): static
    {
        return new static(
            cooldown: $response['cooldown']['remainingSeconds'],
            surveys: SurveyData::collectionFromResponse(data_get($response, 'surveys', [])),
        );
    }

    public function updateShip(Ship $ship): Ship
    {
        return $ship->fill(['cooldown' => $this->cooldown]);
    }
}
