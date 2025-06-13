<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\EFB\History\OCC\EFBHistoryOccController;
use App\Http\Controllers\API\EFB\Home\OCC\EFBHomeOccController;
use App\Http\Controllers\API\EFB\Home\Pilot\EFBHomePilotController;
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
        Route::get('get-user-by-id', [EFBHomePilotController::class, 'get_user_by_id']);
        Route::get('get-devices', [EFBHomePilotController::class, 'get_devices']);
        Route::get('get-device-by-id', [EFBHomePilotController::class, 'get_device_by_id']);
        Route::get('get-device-by-name', [EFBHomePilotController::class, 'get_device_by_name']);
        Route::get('get-confirmation-status', [EFBHomePilotController::class, 'get_confirmation_status']);
        Route::get('get-pilot-devices', [EFBHomePilotController::class, 'get_pilot_devices']);
        Route::get('check-request', [EFBHomePilotController::class, 'check_request']);
        Route::post('submit-request', [EFBHomePilotController::class, 'submit_request']);
        Route::delete('cancel-request', [EFBHomePilotController::class, 'cancel_request']);

        Route::post('pilot-handover', [EFBHomePilotController::class, 'pilot_handover']);
        Route::post('occ-return', [EFBHomePilotController::class, 'occ_return']);
    })->name('efb_home_pilot');


    Route::prefix('efb')->group(function () {
        Route::get('get-count-devices', [EFBHomeOccController::class, 'get_count_devices']);
        Route::get('get-confirmation-occ', action: [EFBHomeOccController::class, 'get_confirmation']);
        Route::get('reject-request-device', action: [EFBHomeOccController::class, 'reject_request']);
        Route::get('approve-request-device', action: [EFBHomeOccController::class, 'approve_request']);
        Route::post('confirm-return', action: [EFBHomeOccController::class, 'confirm_return']);
    })->name('efb_home_occ');


    Route::prefix('efb')->group(function () {

    })->name('efb_history_pilot');


    Route::prefix('efb')->group(function () {
        Route::get('get-history-occ', [EFBHistoryOccController::class, 'get_history']);
        Route::get('get-device-image', [EFBHistoryOccController::class, 'get_device_image']);
        Route::get('get-feedback-detail', [EFBHistoryOccController::class, 'get_feedback_detail']);
        Route::get('get-feedback-format-pdf', [EFBHistoryOccController::class, 'get_feedback_format_pdf']);
    })->name('efb_history_occ');
});