<?php

namespace App\Http\Controllers\API\EFB\Devices;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class EFBDevicesController extends Controller
{
    public function submit_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceno' => 'required',
            'iosver' => 'required',
            'flysmart' => 'required',
            'docuversion' => 'required',
            'lidoversion' => 'required',
            'hub' => 'required',
            'category' => 'required',
            'remark' => 'nullable',
            'request_user' => 'required',
            'request_date' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $add = DB::table('pilot_devices')->insert([
                'deviceno' => $request->input('deviceno'),
                'ios_version' => $request->input('iosver'),
                'fly_smart' => $request->input('flysmart'),
                'doc_version' => $request->input('docuversion'),
                'lido_version' => $request->input('lidoversion'),
                'hub' => $request->input('hub'),
                'category' => $request->input('category'),
                'remark' => $request->input('remark', null),
                'request_user' => $request->input('request_user'),
                'request_date' => $request->input('request_date'),
                'status' => $request->input('status'),
            ]);

            $update = DB::table('devices')
                ->where('deviceno', '=', $request->input('deviceno'))
                ->update(['status' => false]);

            if (! $add) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to submit request.',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Request submitted successfully.',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit request.',
                'details' => $th->getMessage()
            ], 500);
        }
    }

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

        $deviceNumber = $request->query('deviceno');
        $hub = $request->query('hub');

        if ($validation) {
            try {
                $devices = DB::table('devices')
                    ->where([['hub', '=', $hub], ['status', '=', true]])
                    ->where('deviceno', '=', $deviceNumber)
                    ->get();
                ;

                if ($devices) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Device fetched successfully.',
                        'data' => $devices,
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

    public function get_device_by_name(Request $request)
    {
        $validation = $request->validate([
            'deviceno' => 'required',
            'hub' => 'required',
        ]);

        $deviceNumber = $request->query('deviceno');
        $hub = $request->query('hub');
        $limit = 5;

        if ($validation) {
            try {
                $devices = DB::table('devices')
                    ->where([['hub', '=', $hub], ['status', '=', true]])
                    ->limit($limit)
                    ->whereLike('deviceno', '%'.$deviceNumber.'%')
                    ->get();
                ;

                if ($devices) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Device fetched successfully.',
                        'data' => ['device' => $devices],
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

    public function get_waiting_confirmation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hub' => 'required',
            'request_user' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $waitingDevices = DB::table('pilot_devices')
                ->where([['hub', '=', $request->input('hub')], ['status', '=', 'waiting']])
                ->get();

            if ($waitingDevices) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Fetched data successfully.',
                    'data' => $waitingDevices,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No waiting devices found.',
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Failed to fetch device'], 500);
        }
    }
}
