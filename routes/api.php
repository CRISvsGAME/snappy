<?php

use App\Http\Controllers\Api\StoreController;
use App\Http\Middleware\EnsureJsonResponse;
use Illuminate\Support\Facades\Route;

Route::apiResource('stores', StoreController::class)->middleware(EnsureJsonResponse::class);
