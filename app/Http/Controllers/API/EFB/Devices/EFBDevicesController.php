<?php

namespace App\Http\Controllers\API\EFB\Devices;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class EFBDevicesController extends Controller
{
    public function get_devices(Request $request)
    {
        $validation = $request->validate([
            'hub' => 'required',
        ]);

        if ($validation) {
            try {
                $devices = DB::table('devices')->where('hub', '=', $request->query('hub'))->get();

                if ($devices) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Devices fetched successfully.',
                        'data' => $devices,
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No devices found.',
                    ], 404);
                }
            } catch (\Throwable $th) {
                return response()->json(['error' => 'Failed to fetch devices'], 500);
            }
        }
    }

    public function get_device_by_id(Request $request)
    {
        $validation = $request->validate([
            'deviceno' => 'required',
            'hub' => 'required',
        ]);

        if ($validation) {
            try {
                $device = DB::table('devices')
                    ->where('deviceno', '=', $request->deviceno)
                    ->where('hub', '=', $request->hub)
                    ->first();

                if ($device) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Device fetched successfully.',
                        'data' => $device,
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Device not found.',
                    ], 404);
                }
            } catch (\Throwable $th) {
                return response()->json(['error' => 'Failed to fetch device'], 500);
            }
        }
    }
}
