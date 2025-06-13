<?php

namespace App\Http\Controllers\API\EFB\History\OCC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Storage;
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
                            'feedback' => 1,
                            'receive_category' => 1,
                            'receive_remark' => 1,
                            'received_at' => 1,
                            'received_by' => 1,
                            'received_user_name' => 1,
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

    public function get_device_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'img_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            $disk = Storage::disk('device_pictures');

            if ($disk->exists($request->img_name)) {
                $img = $disk->get($request->img_name);
                return response($img, 200)->header('Content-Type', 'image/jpeg');
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Device image not found.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch device image.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function get_feedback_detail(Request $request)
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
            $feedback = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('pilot_devices')
                ->aggregate([
                    [
                        '$addFields' => [
                            '_id_str' => ['$toString' => '$_id']
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'feedback',
                            'localField' => '_id_str',
                            'foreignField' => 'request_id',
                            'as' => 'feedback_detail'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$feedback_detail',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 1,
                            'q1' => '$feedback_detail.q1',
                            'q2' => '$feedback_detail.q2',
                            'q3' => '$feedback_detail.q3',
                            'q4' => '$feedback_detail.q4',
                            'q5' => '$feedback_detail.q5',
                            'q6' => '$feedback_detail.q6',
                            'q7' => '$feedback_detail.q7',
                            'q8' => '$feedback_detail.q8',
                            'q9' => '$feedback_detail.q9',
                            'q10' => '$feedback_detail.q10',
                            'q11' => '$feedback_detail.q11',
                            'q12' => '$feedback_detail.q12',
                            'q13' => '$feedback_detail.q13',
                            'q14' => '$feedback_detail.q14',
                            'q15' => '$feedback_detail.q15',
                            'q16' => '$feedback_detail.q16',
                            'q17' => '$feedback_detail.q17',
                            'q18' => '$feedback_detail.q18',
                            'q19' => '$feedback_detail.q19',
                            'q20' => '$feedback_detail.q20',
                            'q21' => '$feedback_detail.q21',
                            'q22' => '$feedback_detail.q22',
                            'q23' => '$feedback_detail.q23',
                        ]
                    ]
                ]);


            if ($feedback) {
                $feedback = iterator_to_array($feedback);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Feedback fetched successfully.',
                    'data' => $feedback,
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No feedback found.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch feedback details.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function get_feedback_format_pdf(Request $request)
    {

        try {
            $format = DB::table('efb_format_pdf')->where('id', '=', 'feedback-form')->first();

            if ($format ) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Feedback format PDF fetched successfully.',
                    'data' => $format,
                ], 200);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback format PDF not found.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get feedback format PDF.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}