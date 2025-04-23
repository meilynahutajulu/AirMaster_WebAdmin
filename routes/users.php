<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('users', [UserController::class, 'index']);
    Route::post('users/store', [UserController::class, 'store'])->name('store');
    Route::get('users/index', [UserController::class, 'index']);
    Route::get('users/add', [UserController::class,'create']);
    Route::get('users/edit/{id}', [UserController::class, 'edit']);
    Route::post('users/update/{id}', [UserController::class,'update'])->name('update');
    Route::post('users/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
});
