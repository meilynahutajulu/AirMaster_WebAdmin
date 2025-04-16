<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {

    Route::get('users', [UserController::class,'index'])->name('users'); 
    Route::get('users/index', [UserController::class,'index']);
    Route::get('users/create', [UserController::class,'create']);

    Route::post('users', [UserController::class,'store']);
    Route::delete('users/{id}', [UserController::class,'delete'])->name('users.delete');
    Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
});
