<?php

namespace App\Http\Controllers\API\TC\Training;

use App\Http\Controllers\Controller;
use app\Models\TrainingType;
use Illuminate\Support\Facades\Validator;
use DB;

use Illuminate\Http\Request;

class TC_TrainingController extends Controller
{
    public function new_training_card(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'training' => 'required|string',
            'recurrent' => 'required|string',
            'training_description' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], 422);
        }

        if (DB::table('trainingType')->where('training', $request->input('training'))->exists()) {

            DB::table('trainingType')
                ->where('training', $request->input('training'))
                ->update([
                    'recurrent' => $request->input('recurrent'),
                    'training_description' => $request->input('training_description'),
                    'is_delete' => false,
                ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Training type already exists.',
            ], 409);
        } else {

            try {
                $add = DB::table('trainingType')->insert([
                    'id' => $request->input('training'),
                    'training' => $request->input('training'),
                    'recurrent' => $request->input('recurrent'),
                    'training_description' => $request->input('training_description'),
                    'is_delete' => false,
                ]);

                if (!$add) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to submit request.',
                    ], 500);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Training type created successfully.',
                    'data' => $add,
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred while processing your request.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }


    }

    public function get_att_instructor()
    {
        $data = DB::table('users')
            ->whereIn('INSTRUCTOR', ['FIS', 'FIA', 'GI', 'CCP'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Instructors retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    public function new_training_attendance(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'subject' => 'required|string',
            'date' => 'required|date',
            'trainingType' => 'required|string',
            'department' => 'required|string',
            'room' => 'required|string',
            'venue' => 'required|string',
            'instructor' => 'required|string',
            'keyAttendance' => 'required|string',
            'idTrainingType' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            $add = DB::table('attendance')->insert([
                'id' => 'attendance-' . $request->input('idTrainingType') . '-' . date('YmdHis', timestamp: strtotime($request->input('date'))),
                'subject' => $request->input('subject'),
                'date' => date('Y-m-d H:i:s', strtotime($request->input('date'))),
                'trainingType' => $request->input('trainingType'),
                'department' => $request->input('department'),
                'room' => $request->input('room'),
                'venue' => $request->input('venue'),
                'instructor' => $request->input('instructor'),
                'keyAttendance' => $request->input('keyAttendance'),
                'status' => 'pending',
                'idTrainingType' => $request->input('idTrainingType'),
                'is_delete' => false,

            ]);

            if (!$add) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to submit request.',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Training attendance created successfully.',
                'data' => $add,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_attendance_list(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'subject' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], 422);
        }

        if ($request->input('subject') == 'ALL') {
            $attendanceList = DB::table('attendance')
                ->where([['is_delete', false], ['status', 'pending']])
                ->get();

            if ($attendanceList->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No attendance found.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance list retrieved successfully.',
                'data' => $attendanceList,
            ], 200);
        } else {


            try {
                $pending = DB::connection('mongodb')
                    ->getMongoDB()
                    ->selectCollection('attendance')
                    ->aggregate([
                        [
                            '$match' => [
                                'subject' => ['$in' => [$request->input('subject')]],
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

                                'instructor_id' => '$user_info.id_number',
                                'instructor_name' => '$user_info.name',
                                'instructor_email' => '$user_info.email',
                                'instructor_photo' => '$user_info.photo_url',
                                'instructor_rank' => '$user_info.rank'
                            ]
                        ]
                    ]);

                $confirmed = DB::connection('mongodb')
                    ->getMongoDB()
                    ->selectCollection('attendance')
                    ->aggregate([
                        [
                            '$match' => [
                                'subject' => ['$in' => [$request->input('subject')]],
                                'status' => ['$in' => ['confirmation']],
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

                                'instructor_id' => '$user_info.id_number',
                                'instructor_name' => '$user_info.name',
                                'instructor_email' => '$user_info.email',
                                'instructor_photo' => '$user_info.photo_url',
                                'instructor_rank' => '$user_info.rank',
                                'instructor_loaNo' => '$user_info.loa_number'
                            ]
                        ]
                    ]);

                $done = DB::connection('mongodb')
                    ->getMongoDB()
                    ->selectCollection('attendance')
                    ->aggregate([
                        [
                            '$match' => [
                                'subject' => ['$in' => [$request->input('subject')]],
                                'status' => ['$in' => ['done']],
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
                                'attendanceType' => 1,
                                'room' => 1,
                                'is_delete' => 1,
                                'instructor' => 1,
                                'idPilotAdministrator' => 1,
                                'department' => 1,
                                'keyAttendance' => 1,
                                'status' => 1,
                                'signatureInstructor' => 1,
                                'signaturePilotAdministrator' => 1,

                                'instructor_name' => '$user_info.name',
                                'instructor_email' => '$user_info.email',
                                'instructor_photo' => '$user_info.PHOTOURL',
                                'instructor_rank' => '$user_info.rank',
                                'instructor_loaNo' => '$user_info.loa_number'
                            ]
                        ]
                    ]);

                if ($pending || $confirmed || $done) {
                    $pending = iterator_to_array($pending);
                    $confirmed = iterator_to_array($confirmed);
                    $done = iterator_to_array($done);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'History fetched successfully.',
                        'data' => ['pending' => $pending, 'confirmed' => $confirmed, 'done' => $done],
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

    public function delete_training_card(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'training' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            $delete = DB::table('trainingType')
                ->where('id', $request->input('training'))
                ->update(['is_delete' => true]);

            if (!$delete) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete training card.',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Training card deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_status_confirmation()
    {
        try {
            $pending = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance')
                ->aggregate([
                    [
                        '$match' => [
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
                            'user_rank' => '$user_info.rank'
                        ]
                    ]
                ]);

            $confirmed = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance')
                ->aggregate([
                    [
                        '$match' => [
                            'status' => ['$in' => ['confirmation']],
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
                            'attendanceType' => 1,

                            'user_id' => '$user_info.id_number',
                            'user_name' => '$user_info.name',
                            'user_email' => '$user_info.email',
                            'user_photo' => '$user_info.PHOTOURL',
                            'user_rank' => '$user_info.rank',
                            'user_loaNo' => '$user_info.loa_number'
                        ]
                    ]
                ]);

            if ($pending || $confirmed) {
                $pending = iterator_to_array($pending);
                $confirmed = iterator_to_array($confirmed);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Status confirmation fetched successfully.',
                    'data' => ['pending' => $pending, 'confirmed' => $confirmed],
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No status confirmation found.',
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch status confirmation.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

}
