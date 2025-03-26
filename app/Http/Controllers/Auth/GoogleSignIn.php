<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

/*
    |--------------------------------------------------------------------------
    | Google Sign In Controller
    |--------------------------------------------------------------------------
    | This controller handles the Google sign in process.
    | It uses the Socialite package to authenticate the user.
    | 
    |
*/

class GoogleSignIn extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Redirect to Google
    |--------------------------------------------------------------------------
    |
    | This function redirects the user to the Google login page.
    |
    */
    public function redirectToGoogle()
    {
        return Inertia::location(Socialite::driver('google')->stateless()->redirect()->getTargetUrl());
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Google Callback
    |--------------------------------------------------------------------------
    |
    | This function handles the callback from Google.
    | It checks if the user is allowed to login and logs the user in.
    |
    */
    public function handleGoogle(Request $request)
    {
        $user = Socialite::driver('google')->stateless()->user();
        $email = $user->getEmail();

        /*
         * For Development only.
         * Uncomment for production.
         * 
         * 
         * if (!str_ends_with($email, '@airasia.com')) {
         *    return redirect('/')->with('error', 'Only users with @airasia.com email are allowed.');
         * }
         */
        

        $db_user = User::where('EMAIL', $email)->first();

        if ($db_user) {
            if (!$db_user['TYPE'] == 'SUPERADMIN') {
                return redirect('/')->with('error', 'Your account is not allowed.');
            }
        } else {
            return redirect('/')->with('error', 'Your account is not available.');
        }

        Auth::login($db_user);
        return redirect('/dashboard')->with('success', 'Login succesfull.');
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    |
    | This function logs out the user.
    |
    */
    public function logout()
    {
        Auth::logout();
        Session::flush();

        return redirect('/')->with('success', 'Logout succesfull.');
    }
}
