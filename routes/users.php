<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('users', [UserController::class,'index']); 
    Route::get('users/index', [UserController::class,'index']);
    Route::get('users/edit/{id}', [UserController::class, 'edit']);
    Route::get('users/delete', [UserController::class, 'delete']);
});
