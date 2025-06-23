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
                        '$addFields' => [
                            'request_user' => '$user_info.id_number',
                            'request_user_name' => '$user_info.name',
                            'request_user_email' => '$user_info.email',
                            'request_user_photo' => '$user_info.photo_url',
                            'request_user_hub' => '$user_info.hub',
                            'request_user_rank' => '$user_info.rank',
                        ]
                    ],
                    [
                        '$project' => [
                            'user_info' => 0
                        ]
                    ]
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

    public function get_signature_image(Request $request)
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
            $disk = Storage::disk('signatures');

            if ($disk->exists($request->img_name)) {
                $img = $disk->get($request->img_name);
                return response($img, 200)->header('Content-Type', 'image/jpeg');
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Signature image not found.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch signature image.',
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
            $feedback = DB::table('feedback')->where('request_id', $request->request_id)->first();

            if ($feedback) {

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

    public function get_format_pdf(Request $request)
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
            $format = DB::table('efb_format_pdf')->where('id', '=', $request->id)->first();

            if ($format) {
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

    public function update_format_pdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'rec_number' => 'required',
            'date' => 'required',
            'left_footer' => 'required',
            'right_footer' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
            ], 422);
        }

        try {
            DB::table('efb_format_pdf')
                ->where('id', $request->id)
                ->update([
                    'rec_number' => $request->rec_number,
                    'date' => $request->date,
                    'left_footer' => $request->left_footer,
                    'right_footer' => $request->right_footer,
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Feedback format PDF updated successfully.',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update feedback format PDF.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}