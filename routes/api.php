<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\PotentialTradeRouteController;
use App\Http\Controllers\ShipController;
use Illuminate\Http\Response;
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
    Route::prefix('ships')
        ->as('ships.')
        ->controller(ShipController::class)
        ->group(function () {
            Route::get('refetch', 'refetch')->name('refetch');
        });
    Route::apiResource('ships', ShipController::class);

    Route::apiResource('contracts', ContractController::class);

    Route::prefix('potential-trade-routes')
        ->as('potential-trade-routes.')
        ->controller(PotentialTradeRouteController::class)
        ->group(function () {
            Route::get('refetch', 'refetch')->name('refetch');
        });
    Route::apiResource('potential-trade-routes', PotentialTradeRouteController::class);
});

