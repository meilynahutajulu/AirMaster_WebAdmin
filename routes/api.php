<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\EFB\Analytics\OCC\EFBAnalyticsOccController;
use App\Http\Controllers\API\EFB\History\OCC\EFBHistoryOccController;
use App\Http\Controllers\API\EFB\History\PILOT\EFBHistoryPilotController;
use App\Http\Controllers\API\EFB\Home\OCC\EFBHomeOccController;
use App\Http\Controllers\API\EFB\Home\Pilot\EFBHomePilotController;
use App\Http\Controllers\API\TC\Home\Administrator\TC_AdministratorHomeController;
use App\Http\Controllers\API\TS_1\Home\TS1HomeController;
use App\Http\Controllers\API\TC\Training\TC_TrainingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TC\Home\Instructor\TC_InstructorHomeController;
use App\Http\Controllers\API\TC\Home\Examinee\TC_ExamineeHomeController;
use App\Http\Controllers\API\TC\Home\TC_HomeController;
use App\Http\Controllers\API\TC\Home\CPTS\TC_CPTSHomeController;

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
        Route::get('get-handover-device', [EFBHomePilotController::class, 'get_handover_device']);
        Route::get('get-handover-device-detail', [EFBHomePilotController::class, 'get_handover_device_detail']);
        Route::get('get-pilot-devices', [EFBHomePilotController::class, 'get_pilot_devices']);
        Route::get('check-request', [EFBHomePilotController::class, 'check_request']);
        Route::post('submit-request', [EFBHomePilotController::class, 'submit_request']);
        Route::post('fo-submit-request', [EFBHomePilotController::class, 'fo_submit_request']);
        Route::delete('cancel-request', [EFBHomePilotController::class, 'cancel_request']);
        Route::post('pilot-handover', [EFBHomePilotController::class, 'pilot_handover']);
        Route::post('confirm-pilot-handover', [EFBHomePilotController::class, 'confirm_pilot_handover']);
        Route::post('occ-return', [EFBHomePilotController::class, 'occ_return']);
    })->name('efb_home_pilot');


    Route::prefix('efb')->group(function () {
        Route::get('get-count-devices', [EFBHomeOccController::class, 'get_count_devices']);
        Route::get('get-confirmation-occ', action: [EFBHomeOccController::class, 'get_confirmation']);
        Route::get('reject-request-device', action: [EFBHomeOccController::class, 'reject_request']);
        Route::post('approve-request-device', action: [EFBHomeOccController::class, 'approve_request']);
        Route::post('confirm-return', action: [EFBHomeOccController::class, 'confirm_return']);
    })->name('efb_home_occ');


    Route::prefix('efb')->group(function () {

    })->name('efb_history_pilot');


    Route::prefix('efb')->group(function () {
        Route::get('get-history-occ', [EFBHistoryOccController::class, 'get_history']);
        Route::get('get-history-other', [EFBHistoryPilotController::class, 'get_other_history']);
        Route::get('get-device-image', [EFBHistoryOccController::class, 'get_device_image']);
        Route::get('get-signature-image', [EFBHistoryOccController::class, 'get_signature_image']);
        Route::get('get-feedback-detail', [EFBHistoryOccController::class, 'get_feedback_detail']);
        Route::get('get-format-pdf', [EFBHistoryOccController::class, 'get_format_pdf']);
        Route::post('update-format-pdf', [EFBHistoryOccController::class, 'update_format_pdf']);
    })->name('efb_history_occ');

    Route::prefix('efb')->group(function () {
        Route::get('get-hub', [EFBAnalyticsOccController::class, 'get_hub']);
        Route::get('get-count-hub', [EFBAnalyticsOccController::class, 'get_count_hub']);
        Route::get('get-all-pilot-devices', [EFBAnalyticsOccController::class, 'get_all_pilot_devices']);
    })->name('efb_analytics_occ');

    Route::prefix('efb')->group(function () {
        Route::get('get-hub', [EFBAnalyticsOccController::class, 'get_hub']);
        Route::get('get-count-hub', [EFBAnalyticsOccController::class, 'get_count_hub']);
        Route::get('get-all-pilot-devices', [EFBAnalyticsOccController::class, 'get_all_pilot_devices']);
    })->name('efb_analytics_occ');

    Route::prefix('tc')->group(function () {
        Route::get('get-training-cards', [TC_HomeController::class, 'get_training_cards']);
        Route::post('new-training-card', [TC_TrainingController::class, 'new_training_card']);
        Route::get('get-att-instructor', [TC_TrainingController::class, 'get_att_instructor']);
        Route::post('new-training-attendance', [TC_TrainingController::class, 'new_training_attendance']);
        Route::get('get-attendance-list', [TC_TrainingController::class, 'get_attendance_list']);
        Route::delete('delete-training-card', [TC_TrainingController::class, 'delete_training_card']);
        Route::get('get-status-confirmation', [TC_TrainingController::class, 'get_status_confirmation']);
        Route::get('get-need-feedback', [TC_HomeController::class, 'get_need_feedback']);
        Route::get('get-att-trainees', [TC_HomeController::class, 'get_att_trainees']);


        Route::get('get-training-overview', [TC_InstructorHomeController::class, 'get_training_overview']);

        Route::get('get-class-open', [TC_ExamineeHomeController::class, 'get_class_open']);
        Route::get('check-class-password', [TC_ExamineeHomeController::class, 'check_class_password']);

        Route::post('create-attendance-detail', action: [TC_ExamineeHomeController::class, 'create_attendance_detail']);
        Route::get('get-attendance', [TC_ExamineeHomeController::class, 'get_attendance']);
        Route::get('get-trainee-details', [TC_ExamineeHomeController::class, 'get_trainee_details']);

        Route::get('trainee-details', [TC_ExamineeHomeController::class, 'trainee_details']);
        Route::post('save-trainee-score', [TC_ExamineeHomeController::class, 'save_trainee_score']);
        Route::post('confirm-attendance', [TC_ExamineeHomeController::class, 'confirm_attendance']);

        // Administrator Routes
        Route::get('get-total-participant', [TC_AdministratorHomeController::class, 'get_total_participant']);
        Route::get('get-pilot-list', [TC_AdministratorHomeController::class, 'get_pilot_list']);
        Route::get('recurrent-date-training', [TC_AdministratorHomeController::class, 'recurrent_date_training']);
        Route::post('confirm-class-attendance', [TC_AdministratorHomeController::class, 'confirm_class_attendance']);
        Route::get('get-absent-participant', [TC_AdministratorHomeController::class, 'get_absent_participant']);
        Route::get('get-total-absent-trainee', [TC_AdministratorHomeController::class, 'get_total_absent_trainee']);
        Route::get('get-attendance-detail-done', [TC_AdministratorHomeController::class, 'get_attendance_detail_done']);
        Route::get('get-administrator-data', [TC_AdministratorHomeController::class, 'get_administrator_data']);
        Route::get('get-participant-detail', [TC_AdministratorHomeController::class, 'get_participant_detail']);
        Route::get('get-tc-signature', [TC_AdministratorHomeController::class, 'getSignature']);
        Route::get('get-participant-history', [TC_AdministratorHomeController::class, 'get_participant_history']);
        Route::get('get-participant-training-history', [TC_AdministratorHomeController::class, 'get_participant_training_history']);
        Route::get('get-instructor-training', [TC_AdministratorHomeController::class, 'get_instructor_training']);
        Route::get('get-trainee-training', [TC_AdministratorHomeController::class, 'get_trainee_training']);
        Route::get('check-trainee-score', [TC_InstructorHomeController::class, 'check_trainee_score']);
        Route::get('get-history-training-trainee', [TC_AdministratorHomeController::class, 'get_history_training_trainee']);
        Route::get('get-training-remarks', [TC_AdministratorHomeController::class, 'get_training_remarks']);
        Route::get('get-all-pilot', [TC_AdministratorHomeController::class, 'get_all_pilot']);

        // examinee routes
        Route::get('get-need-feedback', [TC_ExamineeHomeController::class, 'get_need_feedback']);
        Route::post('examinee-feedback', [TC_ExamineeHomeController::class, 'examinee_feedback']);
        Route::get('check-feedback', [TC_ExamineeHomeController::class, 'check_feedback']);
        Route::get('get-trainee-profile', [TC_ExamineeHomeController::class, 'get_trainee_profile']);

        // CPTS Home
        Route::get('get-pilot-only', [TC_CPTSHomeController::class, 'get_pilot_only']);
        Route::get('get-pilot-detail', [TC_CPTSHomeController::class, 'get_pilot_detail']);
        Route::get('get-attendance-list-cpts', [TC_CPTSHomeController::class, 'get_attendance_list_cpts']);
    });


});