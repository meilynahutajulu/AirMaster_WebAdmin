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
        date_default_timezone_set('Asia/Jakarta');
        $email = $request->email;

        /*
         * For Development only.
         * Uncomment for production.
         * 
         * if (!str_ends_with($email, '@airasia.com')) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not allowed to login.',
            ], 403);
         * }
         * 
         */



        $db_user = User::where('email', $email)->first();

        if ($db_user) {
            if ($db_user['status'] == 'VALID') {
                $db_user->tokens()->delete();

                $token = $db_user->createToken('auth_token')->plainTextToken;

                $update = User::where('email', '=', $email)->update([
                    'name' => $request->name,
                    'photo_url' => $request->photo_url,
                    'last_login' => date("Y-m-d H:i:s"),
                    'token' => $token,
                    'token_type' => 'Bearer Token',
                ]);

                $user = User::where('email', operator: $email)->first();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful.',
                    'data' => [
                        'user' => $user,
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
