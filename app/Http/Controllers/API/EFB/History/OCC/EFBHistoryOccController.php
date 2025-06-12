<?php

namespace App\Http\Controllers\API\EFB\History\OCC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;

class EFBHistoryOccController extends Controller
{
    public function get_history(Request $request)
    {
        try {
            $history = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('pilot_devices')
                ->aggregate([
                    [
                        '$match' => [
                            'status' => ['$in' => ['returned', 'handover']]
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
                            'request_user' => 1,
                            'request_user_name' => 1,
                            'request_date' => 1,
                            'status' => 1,
                            'approved_at' => 1,
                            'approved_by' => 1,
                            'receive_category' => 1,
                            'receive_remark' => 1,
                            'received_at' => 1,
                            'received_by' => 1,
                            'received_signature' => 1,
                            'returned_device_picture' => 1,

                            'user_id' => '$user_info.id_number',
                            'user_name' => '$user_info.name',
                            'user_email' => '$user_info.email',
                            'user_photo' => '$user_info.photo_url',
                            'user_rank' => '$user_info.rank'
                        ]]
                ]);

            if ($history) {
                $history = iterator_to_array($history);

                return response()->json([
                    'status' => 'success',
                    'message' => 'History fetched successfully.',
                    'data' => $history,
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No history found.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch history.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}