<?php

use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// https://stackoverflow.com/a/60203237
Route::get('/{any}', FrontendController::class)->where('any', '^(?!api).*$');

require __DIR__ . '/auth.php';
