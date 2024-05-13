<?php

declare(strict_types=1);

use App\Http\Controllers\ContractController;
use App\Http\Controllers\LiveDataController;
use App\Http\Controllers\PotentialTradeRouteController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TradeOpportunityController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WaypointController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('contracts')
        ->as('contracts.')
        ->controller(ContractController::class)
        ->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('refetch', 'refetch')->name('refetch');
            Route::post('{contract}/accept', 'accept')->name('accept');
        });

    Route::prefix('live-data')
        ->as('live-data.')
        ->controller(LiveDataController::class)
        ->group(function () {
            Route::get('purchasable-ships-in-system', 'purchasableShipsInSystem')
                ->name('purchasable-ships-in-system');
            Route::get('construction-site-in-starting-system', 'constructionSiteInStartingSystem')
                ->name('construction-site-in-starting-system');
        });

    Route::prefix('potential-trade-routes')
        ->as('potential-trade-routes.')
        ->controller(PotentialTradeRouteController::class)
        ->group(function () {
            Route::get('refetch', 'refetch')->name('refetch');
        });

    Route::apiResource('potential-trade-routes', PotentialTradeRouteController::class);

    Route::prefix('ships')
        ->as('ships.')
        ->controller(ShipController::class)
        ->group(function () {
            Route::get('refetch', 'refetch')->name('refetch');
            Route::post('buy', 'buy')->name('buy');
        });

    Route::apiResource('ships', ShipController::class)
        ->only(['index', 'show']);

    Route::prefix('ships/{ship}')
        ->as('ships.')
        ->controller(ShipController::class)
        ->group(function () {
            Route::patch('update-flight-mode', 'updateFlightMode')->name('update-flight-mode');
            Route::patch('update-task', 'updateTask')->name('update-task');
            Route::post('purchase', 'purchase')->name('purchase');
            Route::post('sell', 'sell')->name('sell');
        });

    Route::apiResource('systems', SystemController::class)
        ->only(['index', 'show']);

    Route::prefix('systems/{system}')
        ->as('systems.')
        ->controller(SystemController::class)
        ->group(function () {
            Route::get('waypoints', 'waypoints')->name('waypoints');
        });

    Route::prefix('trade-opportunities')
        ->as('trade-opportunities.')
        ->controller(TradeOpportunityController::class)
        ->group(function () {
            Route::get('refetch', 'refetch')->name('refetch');
        });

    Route::apiResource('trade-opportunities', TradeOpportunityController::class);

    Route::prefix('tasks')
        ->as('tasks.')
        ->controller(TaskController::class)
        ->group(function () {
            Route::get('trigger-all', 'triggerAll')->name('trigger-all');
        });

    Route::apiResource('transactions', TransactionController::class)
        ->only(['index', 'show']);

    Route::apiResource('tasks', TaskController::class);

    Route::prefix('waypoints')
        ->as('waypoints.')
        ->controller(WaypointController::class)
        ->group(function () {
            Route::get('without-satellite', 'withoutSatellite')->name('without-satellite');
        });

    Route::apiResource('waypoints', WaypointController::class)
        ->only(['index', 'show']);

    Route::prefix('waypoints/{waypoint}')
        ->as('waypoints.')
        ->controller(WaypointController::class)
        ->group(function () {
            Route::get('market', 'market')->name('market');
            Route::get('ships', 'ships')->name('ships');
        });
});
