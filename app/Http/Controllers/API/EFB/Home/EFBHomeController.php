<?php

namespace App\Http\Controllers\API\EFB\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class EFBHomeController extends Controller
{
    public function get_count_devices(Request $request)
    {
        try {
            $available = DB::table('devices')->where([['status', '=', true], ['hub', '=', $request->query('hub')]])->count();
            $used = DB::table('devices')->where([['status', '=', false], ['hub', '=', $request->query('hub')]])->count();
            if ($available > 0 || $used > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Available devices fetched successfully.',
                    'data' => ['available' => $available, 'used' => $used],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No devices available.',
                ], 404);
            }
        } catch (\Exception $th) {
            return response()->json(['error' => 'Failed to fetch flight details'], 500);
        }
    }
}
