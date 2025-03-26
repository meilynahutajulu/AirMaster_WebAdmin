<?php
use App\Http\Controllers\Auth\GoogleSignIn;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
	Route::get('login', [GoogleSignIn::class, 'redirectToGoogle'])->name('login');
	Route::get('auth/google-callback', [GoogleSignIn::class, 'handleGoogle']);
});

Route::middleware('auth')->group(function () {
	Route::post('logout', [GoogleSignIn::class, 'logout'])->name('logout');
});
