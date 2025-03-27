<?php

use App\Http\Controllers\Api\ClinikoController;
use App\Http\Controllers\Api\GoHighLevelController;
use App\Http\Controllers\Api\Triggers\VoucherFormController;
use App\Http\Middleware\StaticBasicAuthMiddleware;
use Illuminate\Support\Facades\Route;


Route::middleware(StaticBasicAuthMiddleware::class)->group(function () {
    Route::match(['get', 'post', 'put', 'delete', 'patch',], '/portal/go-high-level/v1/{any}', GoHighLevelController::class)->where('any', '.*');

    Route::match(['get', 'post', 'put', 'delete', 'patch',], '/portal/cliniko/v1/{any}', ClinikoController::class)->where('any', '.*');
});
