<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\About\Http\Controllers\AboutController;

Route::group([
    'prefix' => 'api/v1/admin',
    'middleware' => ['api', 'throttle:30,1', 'jwt.verify'],
], static function () {
    Route::get('/about', [AboutController::class, 'show']);
    Route::post('/about', [AboutController::class, 'upsert']);
});
