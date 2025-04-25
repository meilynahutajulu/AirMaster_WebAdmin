<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;

        /*
         * For Development only.
         * Uncomment for production.
         * 
         * 
         * if (!str_ends_with($email, '@airasia.com')) {
         *    return redirect('/')->with('error', 'Only users with @airasia.com email are allowed.');
         * }
         */

        $db_user = User::where('email', $email)->first();

        if ($db_user) {
            if ($db_user['status'] == 'VALID') {
                $token = $db_user->createToken('auth_token')->plainTextToken;
                
                $update = User::where('email', '=', $email)->update([
                    'name' => $request->name,
                    'photo_url' => $request->photo_url,  
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful.',
                    'data' => [
                        'token' => $token,
                        'token_type' => 'Bearer',
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User is not allowed to login.',
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

    }
}
