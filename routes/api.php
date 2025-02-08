<?php

use App\Http\Controllers\Api\StoreController;
use App\Http\Middleware\EnsureJsonResponse;
use Illuminate\Support\Facades\Route;

Route::middleware(EnsureJsonResponse::class)->group(function () {
    Route::apiResource('stores', StoreController::class);
    Route::get('stores/near/{postcode}', [StoreController::class, 'nearByPostcode']);
    Route::get('stores/delivery/{postcode}', [StoreController::class, 'deliveryByPostcode']);
});
