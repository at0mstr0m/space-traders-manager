<?php

declare(strict_types=1);

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
                'waypoint_symbol' => $surveyData->waypointSymbol,
                'expiration' => $surveyData->expiration,
                'size' => $surveyData->size,
                'raw_response' => $surveyData->rawResponse,
            ]
        );

        $surveyData->deposits
            ->map(fn (DepositData $depositData) => Deposit::firstOrCreate(['symbol' => $depositData->symbol])->id)
            ->unique()
            ->pipe(fn ($depositIds) => $survey->deposits()->sync($depositIds));
    }
}
