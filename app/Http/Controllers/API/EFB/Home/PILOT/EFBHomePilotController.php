<?php

namespace App\Http\Controllers\API\EFB\Home\PILOT;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use MongoDB\BSON\ObjectId;

class EFBHomePilotController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }
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
            $check_handover = DB::table('pilot_devices')
                ->where([
                    ['hub', '=', $request->query('hub')],
                    ['handover_to', '=', $request->query('request_user')],
                    ['status', '=', 'handover_confirmation'],
                ])
                ->exists();

            if ($check_handover) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Request already exists.',
                ], 409);
            } else {
                $exist = DB::table('pilot_devices')
                    ->where([
                        ['hub', '=', $request->query('hub')],
                        ['request_user', '=', $request->query('request_user')],
                    ])
                    ->whereNotIn('status', ['returned', 'rejected', 'handover'])
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

            DB::table('devices')
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
                ->first();

            if ($devices) {
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

    public function get_handover_device(Request $request)
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
                ->where([
                    ['hub', '=', $request->query('hub')],
                    ['status', '=', $request->query('status')],
                    ['handover_to', '=', $request->query('request_user')]])
                ->first();

            if ($devices) {
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

    public function get_handover_device_detail(Request $request)
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
            $devices = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('pilot_devices')
                ->aggregate([
                    [
                        '$match' => [
                            '_id' => new ObjectId($request->query('request_id')),
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'users',
                            'localField' => 'request_user',
                            'foreignField' => 'id_number',
                            'as' => 'user_info'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$user_info',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 1,
                            'deviceno' => 1,
                            'ios_version' => 1,
                            'fly_smart' => 1,
                            'doc_version' => 1,
                            'lido_version' => 1,
                            'hub' => 1,
                            'category' => 1,
                            'remark' => 1,
                            'request_date' => 1,
                            'status' => 1,
                            'approved_at' => 1,
                            'approved_by' => 1,
                            'approved_user_hub' => 1,
                            'approved_user_name' => 1,
                            'approved_user_rank' => 1,
                            'feedback' => 1,
                            'receive_category' => 1,
                            'receive_remark' => 1,
                            'received_at' => 1,
                            'received_by' => 1,
                            'received_user_name' => 1,
                            'received_user_hub' => 1,
                            'received_signature' => 1,
                            'returned_device_picture' => 1,
                            'handover_date' => 1,
                            'handover_to' => 1,
                            'handover_user_name' => 1,
                            'handover_user_hub' => 1,
                            'handover_user_rank' => 1,
                            'isHandover' => 1,

                            'request_user' => '$user_info.id_number',
                            'request_user_name' => '$user_info.name',
                            'request_user_email' => '$user_info.email',
                            'request_user_photo' => '$user_info.photo_url',
                            'request_user_hub' => '$user_info.hub',
                            'request_user_rank' => '$user_info.rank',
                            'request_user_signature' => 1,
                        ]]
                ]);

            if ($devices) {
                $devices = iterator_to_array($devices);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Fetched data successfully.',
                    'data' => $devices[0],
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

    public function confirm_pilot_handover(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
            'deviceno' => 'required',
            'handover_to' => 'required',
            'handover_date' => 'required',
            'handover_device_category' => 'required',
            'signature' => 'required',
        ]);

        if ($validator->fails()) {
            echo 'Validation failed: '.$validator->errors();
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $signature_img_name = null;
            if ($request->hasFile('signature')) {
                $signature_img_name = $request->handover_to.'_'.$request->request_id.'.'.$request->signature->getClientOriginalExtension();
                Storage::disk('signatures')->put($signature_img_name, file_get_contents($request->signature));
            }

            $device = DB::table('pilot_devices')
                ->where('deviceno', '=', $request->deviceno)->first();

            $user = DB::table('users')
                ->where('id_number', '=', $request->handover_to)->first();

            if ($request->damage_img) {
                $dmg_img_name = $request->deviceno.'_'.$request->request_id.'_damage.'.$request->damage_img->getClientOriginalExtension();
                Storage::disk('device_pictures')->put($dmg_img_name, file_get_contents($request->damage_img));
                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'damage_img' => $dmg_img_name,
                ]);
            }

            $update = DB::table('pilot_devices')->where([['id', '=', $request->request_id]])
                ->update([
                    'status' => 'handover',
                    'handover_date' => $request->handover_date,
                    'handover_signature' => $signature_img_name,
                ]);

            DB::table('pilot_devices')->insert([
                'deviceno' => $request->deviceno,
                'ios_version' => $device->ios_version,
                'fly_smart' => $device->fly_smart,
                'doc_version' => $device->doc_version,
                'lido_version' => $device->lido_version,
                'hub' => $device->hub,
                'category' => $request->handover_device_category,
                'remark' => $request->handover_device_remark,
                'request_user' => $request->handover_to,
                'request_user_name' => $user->name,
                'request_date' => $request->handover_date,
                'status' => 'used',
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
            return response()->json(['error' => 'Failed to update request'], 500);
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
            'handover_date' => 'required',
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
                    'feedback_date' => date('Y-m-d H:i:s'),
                ]);

                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'feedback' => true,
                ]);
            } else {
                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'feedback' => false,
                ]);
            }

            $user = DB::table('users')->where('id_number', $request->handover_to)->first();

            $update = DB::table('pilot_devices')->where([['id', '=', $request->request_id]])->update([
                'status' => 'handover_confirmation',
                'isHandover' => true,
                'handover_to' => $request->handover_to,
                'handover_user_name' => $user->name,
                'handover_user_hub' => $user->hub,
                'handover_user_rank' => $user->rank,
                'handover_date' => $request->handover_date,
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
                    'feedback_date' => date('Y-m-d H:i:s'),
                ]);

                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'feedback' => true,
                ]);
            } else {
                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'feedback' => false,
                ]);
            }

            if ($request->remark) {
                DB::table('pilot_devices')->where('id', '=', $request->request_id)->update([
                    'return_remark_via_fo' => $request->remark,
                ]);
            }

            $update = DB::table('pilot_devices')->where([['id', '=', $request->request_id]])->update([
                'status' => 'occ_returned',
                'isHandover' => false,
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
