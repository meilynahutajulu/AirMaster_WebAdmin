<?php

namespace App\Http\Controllers\API\EFB\Home\PILOT;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
class EFBHomePilotController extends Controller
{
    public function get_user_by_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $user = DB::table('users')
                ->where('id_number', '=', $request->query('id'))
                ->get();

            if ($user) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User fetched successfully.',
                    'data' => $user,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.',
                ], 404);
            }

        } catch (\Throwable $th) {
            echo 'Failed to fetch devices: '.$th->getMessage();
            return response()->json(['error' => 'Failed to fetch device'], 500);
        }
    }
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
                ->where([['hub', '=', $request->query('hub')], ['request_user', '=', $request->query('request_user')]])
                ->whereNotIn('status', ['returned', 'rejected'])
                ->exists();

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
        $validator = Validator::make($request->all(), [
            'hub' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

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

    public function get_device_by_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceno' => 'required',
            'hub' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        $deviceNumber = $request->query('deviceno');
        $hub = $request->query('hub');

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

    public function get_device_by_name(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceno' => 'required',
            'hub' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        $deviceNumber = $request->query('deviceno');
        $hub = $request->query('hub');
        $limit = 5;

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
            'deviceno' => 'required',
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

    public function pilot_handover(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
            'request_user' => 'required',
            'handover_to' => 'required',
            'signature' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }


        try {
            $img_name = null;
            if ($request->hasFile('signature')) {
                $img_name = $request->request_user.'_'.$request->request_id.'.'.$request->signature->getClientOriginalExtension();
                Storage::disk('signatures')->put($img_name, file_get_contents($request->signature));
            }


            if ($request->input('feedback')) {
                $decoded = json_decode($request->feedback, true); // true -> associative array

                DB::table('feedback')->insert([
                    'request_id' => $request->request_id,
                    'q1' => $decoded['q1'],
                    'q2' => $decoded['q2'],
                    'q3' => $decoded['q3'],
                    'q4' => $decoded['q4'],
                    'q5' => $decoded['q5'],
                    'q6' => $decoded['q6'],
                    'q7' => $decoded['q7'],
                    'q8' => $decoded['q8'],
                    'q9' => $decoded['q9'],
                    'q10' => $decoded['q10'],
                    'q11' => $decoded['q11'],
                    'q12' => $decoded['q12'],
                    'q13' => $decoded['q13'],
                    'q14' => $decoded['q14'],
                    'q15' => $decoded['q15'],
                    'q16' => $decoded['q16'],
                    'q17' => $decoded['q17'],
                    'q18' => $decoded['q18'],
                    'q19' => $decoded['q19'],
                    'q20' => $decoded['q20'],
                    'q21' => $decoded['q21'],
                    'q22' => $decoded['q22'],
                    'q23' => $decoded['q23'],
                ]);

                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'feedback' => true,
                ]);
            }

            $update = DB::table('pilot_devices')->where([['id', '=', $request->request_id]])->update([
                'status' => 'handover',
                'handover_to' => $request->handover_to,
                'handover_date' => date('Y-m-d H:i:s'),
                'request_user_signature' => $img_name,
            ]);

            if ($update) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request updated successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update request or request not found.',
                ], 404);
            }

        } catch (\Throwable $th) {
            echo 'Failed to update request: '.$th->getMessage();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update request.',
            ], 500);
        }
    }

    public function occ_return(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
            'signature' => 'required',
            'request_user' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }


        try {
            $img_name = null;
            if ($request->hasFile('signature')) {
                $img_name = $request->request_user.'_'.$request->request_id.'.'.$request->signature->getClientOriginalExtension();
                Storage::disk('signatures')->put($img_name, file_get_contents($request->signature));
            }


            if ($request->input('feedback')) {
                $decoded = json_decode($request->feedback, true); // true -> associative array

                DB::table('feedback')->insert([
                    'request_id' => $request->request_id,
                    'q1' => $decoded['q1'],
                    'q2' => $decoded['q2'],
                    'q3' => $decoded['q3'],
                    'q4' => $decoded['q4'],
                    'q5' => $decoded['q5'],
                    'q6' => $decoded['q6'],
                    'q7' => $decoded['q7'],
                    'q8' => $decoded['q8'],
                    'q9' => $decoded['q9'],
                    'q10' => $decoded['q10'],
                    'q11' => $decoded['q11'],
                    'q12' => $decoded['q12'],
                    'q13' => $decoded['q13'],
                    'q14' => $decoded['q14'],
                    'q15' => $decoded['q15'],
                    'q16' => $decoded['q16'],
                    'q17' => $decoded['q17'],
                    'q18' => $decoded['q18'],
                    'q19' => $decoded['q19'],
                    'q20' => $decoded['q20'],
                    'q21' => $decoded['q21'],
                    'q22' => $decoded['q22'],
                    'q23' => $decoded['q23'],
                ]);

                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'feedback' => true,
                ]);
            }

            if ($request->remark) {
                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'remark' => $request->remark,
                ]);
            }

            $update = DB::table('pilot_devices')->where([['id', '=', $request->request_id]])->update([
                'status' => 'occ_returned',
                'request_user_signature' => $img_name,
            ]);

            if ($update) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request updated successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update request or request not found.',
                ], 404);
            }

        } catch (\Throwable $th) {
            echo 'Failed to update request: '.$th->getMessage();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update request.',
            ], 500);
        }
    }
}
