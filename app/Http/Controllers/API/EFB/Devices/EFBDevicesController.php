<?php

namespace App\Http\Controllers\API\EFB\Devices;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class EFBDevicesController extends Controller
{
    public function check_request(Request $request)
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
            $exist = DB::table('pilot_devices')
                ->where([['hub', '=', $request->query('hub')], ['request_user', '=', $request->query('request_user')], ['status', '<>', 'returned']])
                ->exists();

            echo $exist;

            if ($exist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Request already exists.',
                ], 409);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No existing request found.',
                ], 200);
            }

        } catch (\Throwable $th) {
            echo 'Failed to fetch devices: '.$th->getMessage();
            return response()->json(['error' => 'Failed to fetch device'], 500);
        }
    }
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
            'request_user_name' => 'required',
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
                'request_user_name' => $request->input('request_user_name'),
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

    public function get_confirmation_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hub' => 'required',
            'request_user' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $devices = DB::table('pilot_devices')
                ->where([['hub', '=', $request->query('hub')], ['status', '=', $request->query('status')], ['request_user', '=', $request->query('request_user')]])
                ->get();

            if ($devices->isNotEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Fetched data successfully.',
                    'data' => $devices,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No waiting devices found.',
                ], 404);
            }
        } catch (\Throwable $th) {
            echo 'Failed to fetch devices: '.$th->getMessage();
            return response()->json(['error' => 'Failed to fetch device'], 500);
        }
    }

    public function get_pilot_devices(Request $request)
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
            $devices = DB::table('pilot_devices')
                ->where([['hub', '=', $request->query('hub')], ['request_user', '=', $request->query('request_user')], ['deviceno', '=', $request->query('deviceno')]])
                ->first();

            if ($devices) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Retrieved pilot devices successfully.',
                    'data' => $devices,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No pilot devices found.',
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Contact your IT Support'], 500);
        }
    }

    public function cancel_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $delete = DB::table('pilot_devices')
                ->where([
                    ['id', '=', $request->input('request_id')],
                ])
                ->delete();

            $update = DB::table('devices')
                ->where('deviceno', '=', $request->input('deviceno'))
                ->update(['status' => true]);

            if ($delete) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request cancelled successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to cancel request or request not found.',
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Failed to cancel request'], 500);
        }
    }
}
