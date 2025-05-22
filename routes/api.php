<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\TS_1\Home\TS1HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('check-token', [AuthController::class, 'isTokenValid']);

    Route::prefix('ts1')->group(function () {
        Route::get('get-user-by-name', [TS1HomeController::class, 'get_user_by_name']);
        Route::get('get-flight-details', [TS1HomeController::class, 'get_flight_details']);
    });
});