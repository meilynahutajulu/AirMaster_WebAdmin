<?php

namespace App\Http\Controllers\API\EFB\Home\OCC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;
use Validator;

class EFBHomeOccController extends Controller
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

    public function get_confirmation(Request $request)
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
            $devices = DB::table('pilot_devices')
                ->where([['hub', '=', $request->query('hub')], ['status', '=', $request->query('status')]])
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
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch request.',
            ], 500);
        }
    }

    public function reject_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
            'rejected_by' => 'required',
            'rejected_at' => 'required',
            'deviceno' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {

            $device = DB::table('pilot_devices')->where('id', $request->request_id)->exists();

            if (! $device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found.',
                ], 404);
            }

            DB::table('pilot_devices')->where('id', $request->request_id)->update([
                'status' => 'rejected',
                'rejected_by' => $request->rejected_by,
                'rejected_at' => $request->rejected_at,
            ]);

            DB::table('devices')->where('id', $request->deviceno)->update(['status' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'Request rejected successfully.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject request.',
            ], 500);
        }
    }

    public function approve_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
            'approved_by' => 'required',
            'approved_at' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {

            $device = DB::table('pilot_devices')->where('id', $request->request_id)->exists();

            if (! $device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found.',
                ], 404);
            }

            DB::table('pilot_devices')->where('id', $request->request_id)->update([
                'status' => 'used',
                'approved_by' => $request->approved_by,
                'approved_at' => $request->approved_at,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Request confirmed successfully.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm request.',
            ], 500);
        }
    }

    public function confirm_return(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
            'received_by' => 'required',
            'received_at' => 'required',
            'deviceno' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $signature_img_name = null;
            $returned_device_img_name = null;

            if ($request->hasFile('signature')) {
                $signature_img_name = $request->received_by.'_'.$request->request_id.'.'.$request->signature->getClientOriginalExtension();
                Storage::disk('signatures')->put($signature_img_name, file_get_contents($request->signature));
            }

            if ($request->hasFile('returned_device')) {
                $returned_device_img_name = $request->deviceno.'_'.$request->request_id.'.'.$request->returned_device->getClientOriginalExtension();
                Storage::disk('device_pictures')->put($returned_device_img_name, file_get_contents($request->returned_device));
            }

            $device = DB::table('pilot_devices')->where('id', $request->request_id)->exists();

            if (! $device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found.',
                ], 404);
            }

            $confirm_user = DB::table('users')->where('id_number', $request->received_by)->first();

            DB::table('pilot_devices')->where('id', $request->request_id)->update([
                'status' => 'returned',
                'receive_category' => $request->category,
                'receive_remark' => $request->remark,
                'received_by' => $request->received_by,
                'received_user_name' => $confirm_user->name,
                'received_at' => $request->received_at,
                'received_signature' => $signature_img_name,
                'returned_device_picture' => $returned_device_img_name,
            ]);

            DB::table('devices')->where('id', $request->deviceno)->update(['status' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'Device returned successfully.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm return.',
            ], 500);
        }
    }
}
