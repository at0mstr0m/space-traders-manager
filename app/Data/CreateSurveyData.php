<?php

declare(strict_types=1);

namespace App\Data;

use App\Interfaces\UpdatesShip;
use App\Models\Ship;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class CreateSurveyData extends Data implements UpdatesShip
{
    public function __construct(
        #[MapInputName('cooldown.remainingSeconds')]
        public int $cooldown,
        #[MapInputName('surveys')]
        public array|Collection $surveys,
    ) {
        $this->surveys = SurveyData::collect(Arr::map(
            $surveys,
            fn (array $survey) => $survey = [
                ...$survey,
                'rawResponse' => json_encode($survey),
            ]
        ), Collection::class);
    }

    public function updateShip(Ship $ship): Ship
    {
        return $ship->fill(['cooldown' => $this->cooldown]);
    }
}
