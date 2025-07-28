<?php

namespace App\Http\Controllers\API\EFB\Analytics\OCC;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Validator;
use function PHPUnit\Framework\returnArgument;

class EFBAnalyticsOccController extends Controller
{
    public function get_hub(Request $request)
    {
        try {
            $data = DB::table('devices')->select('hub')->distinct()->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hubs found.',
                ], 404);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Fetched hub data successfully.',
                    'data' => $data,
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch hub data.',
                'error' => $th->getMessage(),
            ], 500);
        }

    }

    public function get_count_hub(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hub' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $count = DB::table('devices')
                ->where('hub', $request->hub)
                ->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Count fetched successfully.',
                'data' => $count,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch count.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function get_all_pilot_devices(Request $request)
    {
        try {
            $data = DB::table('pilot_devices')->whereNotIn('status', ['rejected'])->get();

            if ($data->isNotEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Fetched pilot devices successfully.',
                    'data' => $data,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No pilot devices found.',
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch pilot devices.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
