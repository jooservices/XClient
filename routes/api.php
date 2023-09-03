<?php

use App\Http\Controllers\CrawlingController;
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

Route::prefix('v1/crawling')
    ->name('crawling.')
    ->group(function () {
        Route::post('/', [CrawlingController::class, 'crawling'])
            ->name('request.create');
        Route::post('/queues', [CrawlingController::class, 'queue'])
            ->name('queues.create');
    });

