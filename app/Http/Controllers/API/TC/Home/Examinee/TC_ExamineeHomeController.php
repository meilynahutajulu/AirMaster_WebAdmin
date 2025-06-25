<?php

namespace App\Http\Controllers\API\TC\Home\Examinee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TC_ExamineeHomeController extends Controller
{
    public function get_class_open(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'subject' => 'required|string',
                'userId' => 'required|string',
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], status: 422);
        }

        try {
            $isAttendanceAvailable = DB::table('attendance')
                ->where([['subject', '=', $request->input('subject')], ['status', '=', 'pending']])->get();


            if ($isAttendanceAvailable->isNotEmpty()) {
                foreach ($isAttendanceAvailable as $key) {
                    $isClassOpen = DB::table('attendance-detail')
                        ->where([['idattendance', '=', $key->id], ['idtraining', '=', $request->userId]])->exists();
                    if ($isClassOpen) {

                        return response()->json([
                            'status' => 'success',
                            'message' => 'You have already registered for this class.',
                        ], status: 200);
                    }
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'You can register for this class.',
                ], status: 204);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'No pending attendance found for this subject.',
            ], status: 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching open classes.',
                'error' => $e->getMessage(),
            ], status: 500);
        }

    }

    public function check_class_password(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'subject' => 'required|string',
                'password' => 'required|string',
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], status: 422);
        }

        try {
            $data = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance')
                ->aggregate([
                    [
                        '$match' => [
                            'subject' => $request->input('subject'),
                            'keyAttendance' => $request->input('password'),
                            'status' => ['$in' => ['pending']],
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'users',
                            'localField' => 'instructor',
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
                            'date' => 1,
                            'trainingType' => 1,
                            'venue' => 1,
                            'subject' => 1,
                            'idTrainingType' => 1,
                            'room' => 1,
                            'is_delete' => 1,
                            'instructor' => 1,
                            'department' => 1,
                            'keyAttendance' => 1,
                            'status' => 1,

                            'user_id' => '$user_info.id_number',
                            'user_name' => '$user_info.name',
                            'user_email' => '$user_info.email',
                            'user_photo' => '$user_info.PHOTOURL',
                            'user_rank' => '$user_info.rank',
                            'user_loaNo' => '$user_info.loa_number',
                        ]
                    ]
                ]);

            $data = iterator_to_array($data);
            if (!empty($data)) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'History fetched succesfully.',
                    'data' => $data[0],
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
