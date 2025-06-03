<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\EFB\Devices\EFBDevicesController;
use App\Http\Controllers\API\EFB\Home\EFBHomeController;
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

    Route::prefix('efb')->group(function () {
        Route::get('get-count-devices', [EFBHomeController::class, 'get_count_devices']);
        Route::get('get-devices', [EFBDevicesController::class, 'get_devices']);
        Route::get('get-device-by-id', [EFBDevicesController::class, 'get_device_by_id']);
        Route::get('get-device-by-name', [EFBDevicesController::class, 'get_device_by_name']);
        Route::get('get-confirmation-status', [EFBDevicesController::class, 'get_confirmation_status']);
        Route::get('get-pilot-devices', [EFBDevicesController::class, 'get_pilot_devices']);
        Route::get('check-request', [EFBDevicesController::class, 'check_request']);
        Route::post('submit-request', [EFBDevicesController::class, 'submit_request']);
        Route::delete('cancel-request', [EFBDevicesController::class, 'cancel_request']);
    });
});