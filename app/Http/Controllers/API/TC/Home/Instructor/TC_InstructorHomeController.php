<?php

namespace App\Http\Controllers\API\TC\Home\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TC_InstructorHomeController extends Controller
{
    public function get_training_overview(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'instructor_id' => 'required|string',
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
            $pending = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance')
                ->aggregate([
                    [
                        '$match' => [
                            'instructor' => ['$in' => [$request->input('instructor_id')]],
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

            if ($pending) {
                $pending = iterator_to_array($pending);

                return response()->json([
                    'status' => 'success',
                    'message' => 'History fetched succesfully.',
                    'data' => ['pending' => $pending]
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

    public function check_trainee_score (Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'idattendance' => 'required|string',
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
            $data = DB::table('attendance-detail')
                ->where('idattendance', $request->input('idattendance'))
                ->where('score',  "PASS")
                ->count();

            if ($data > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Trainee has passed the training.',
                    'score' => $data
                ], 200);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Trainee has not passed the training.',
                    'score' => 0
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check trainee score.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}