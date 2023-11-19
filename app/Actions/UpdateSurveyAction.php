<?php

namespace App\Actions;

use App\Data\DepositData;
use App\Data\SurveyData;
use App\Models\Agent;
use App\Models\Deposit;
use App\Models\Survey;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSurveyAction
{
    use AsAction;

    public function handle(SurveyData $surveyData, Agent $agent)
    {
        /** @var Survey */
        $survey = $agent->surveys()->updateOrCreate(
            ['signature' => $surveyData->signature],
            [
                'signature' => $surveyData->signature,
                'waypoint_symbol' => $surveyData->waypointSymbol,
                'expiration' => $surveyData->expiration,
                'size' => $surveyData->size,
            ]
        );

        $surveyData->deposits
            ->toCollection()
            ->map(fn (DepositData $depositData) => Deposit::firstOrCreate(['symbol' => $depositData->symbol])->id)
            ->unique()
            ->pipe(fn ($depositIds) => $survey->deposits()->sync($depositIds));

        // delete expired
        Survey::where('expiration', '<', now())->delete();
    }
}
