<?php

namespace App\Http\Controllers\API\TS_1\Home;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Illuminate\Http\Request;


class TS1HomeController extends Controller
{
    public function get_user_by_name(Request $request)
    {
        $validation = $request->validate([
            'name' => 'required',
        ]);

        if ($validation) {
            $searchName = $request->query('name');
            $limit = 5;

            try {
                $users = DB::table('users')
                    ->select('name', 'id_number', 'license_number', 'license_expiry', 'rank')
                    ->limit($limit)
                    ->whereLike('name', '%'.$searchName.'%')
                    ->get();

                if ($users) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'User found.',
                        'data' => [
                            'user' => $users,
                        ],
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User not found.',
                    ], 404);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to fetch users'], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }
    }

    public function get_flight_details(Request $request)
    {
        try {
            $flightDetails = DB::table('flight_details')->get();
            if ($flightDetails) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Flight details found.',
                    'data' => $flightDetails,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Flight details not found.',
                ], 404);
            }
        } catch (\Exception $th) {
            return response()->json(['error' => 'Failed to fetch flight details'], 500);
        }
    }

}


