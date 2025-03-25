<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class GoogleSignIn extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogle(Request $request)
    {
        $user = Socialite::driver('google')->user();
        $email = $user->getEmail();

        if (!str_ends_with($email, '@airasia.com')) {
            return redirect('/')->with('error', 'Hanya pengguna dengan email @airasia.com yang diizinkan.');
        }

        $db_user = User::where('EMAIL', $email)->first();

        if (!$db_user) {
            return redirect('/')->with('error', 'Akun tidak ditemukan.');
        }

        if (!$db_user['TYPE'] == 'SUPERADMIN') {
            return redirect('/')->with('error', 'Akun anda tidak diizinkan.');
        }

        Auth::login($db_user);
        return redirect('/dashboard')->with('success', 'Login berhasil.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('success', 'Logout berhasil.');
    }
}
