<?php

namespace App\Http\Controllers\API\TC\Home\Examinee;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\isEmpty;

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

                        $data = DB::connection('mongodb')
                            ->getMongoDB()
                            ->selectCollection('attendance')
                            ->aggregate(pipeline: [
                                [
                                    '$match' => [
                                        'subject' => $request->input('subject'),
                                        'status' => 'pending',
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
                                        'instructor_name' => '$user_info.name',
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
                                'message' => 'You have already registered for this class.',
                                'data' => $data[0],
                            ], 200);
                        }
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
                            'instructor_name' => '$user_info.name',
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

    public function create_attendance_detail(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'idAttendance' => 'required|string',
                'idTraining' => 'required|string',
                'signature' => 'required',
                'idTrainingType' => 'required|string',
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
            $img_name = null;
            if ($request->hasFile('signature')) {
                $img_name = $request->idTraining . '_' . $request->idAttendance . '_' . $request->signature->getClientOriginalExtension();
                Storage::disk('signatures')->put($img_name, file_get_contents($request->signature));
            }

            $insertData = DB::table('attendance-detail')->insert([
                'id' => $request->idTraining . '_' . $request->idTrainingType . '_' . now()->timestamp,
                'idattendance' => $request->idAttendance,
                'idtraining' => $request->idTraining,
                'signature' => $img_name,
                'status' => 'done',
            ]);

            if ($insertData) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Attendance detail created successfully.',
                    'data' => [
                        'id' => $request->idTraining . '_' . $request->idTrainingType . '_',
                        'idattendance' => $request->idAttendance,
                        'idtraining' => $request->idTraining,
                        'signature' => $img_name,
                    ]
                ], status: 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create attendance detail.',
                ], status: 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating attendance detail.',
                'error' => $e->getMessage(),
            ], status: 500);
        }


    }

    public function get_attendance(Request $request)
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
                'message' => 'ID Attendance needed',
                'error' => $validated->errors(),
            ], 422);
        }

        try {
            $attendance = DB::table('attendance-detail')
                ->where('idattendance', $request->input('idattendance'))
                ->get();

            $totalAttendance = $attendance->count();

            if ($attendance) {
                $attendance = iterator_to_array($attendance);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Attendance fetched successfully.',
                    'data' => [
                        'attendance' => $attendance,
                        'totalAttendance' => $totalAttendance,
                    ],
                ], 200);
            }

            if ($attendance->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No attendance found for the given ID.',
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching attendance.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function get_trainee_details(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'idtraining' => 'required|array|min:1',
                '_id' => 'required|array|min:1',
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
            $trainingList = $request->input('idtraining');

            $traineeDetails = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idtraining' => ['$in' => $trainingList],
                            '_id' => ['$in' => $request->input('_id')],
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'users',
                            'localField' => 'idtraining',
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
                            'idtraining' => 1,
                            'signature' => 1,
                            'status' => 1,
                            'feedback' => 1,
                            'rActive' => 1,
                            'rKnowledge' => 1,
                            'rCommunication' => 1,
                            'grade' => 1,
                            'score' => 1,
                            'user_id' => '$user_info.id_number',
                            'user_name' => '$user_info.name',
                            'user_email' => '$user_info.email',
                            'user_photo' => '$user_info.PHOTOURL',
                        ]
                    ]
                ]);

            $traineeDetails = iterator_to_array($traineeDetails);
            if (!empty($traineeDetails)) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data trainee fetched successfully.',
                    'data' => $traineeDetails,
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'No data trainee found.',
            ], 404);



        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching trainee details.',
                'error' => $e->getMessage(),
            ], status: 500);
        }

    }

    public function trainee_details(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'idtraining' => 'required|string',
                '_id' => 'required|string',
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
            $traineeDetails = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idtraining' => $request->input('idtraining'),
                            '_id' => $request->input('_id'),
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'users',
                            'localField' => 'idtraining',
                            'foreignField' => 'id_number',
                            'as' => 'trainee_info'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$trainee_info',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 1,
                            'idattendance' => 1,
                            'signature' => 1,
                            'status' => 1,
                            'feedback' => 1,
                            'rActive' => 1,
                            'rKnowledge' => 1,
                            'rCommunication' => 1,
                            'grade' => 1,
                            'score' => 1,

                            'id_number' => 1,
                            'name' => 1,
                            'email' => 1,
                            'photo_url' => 1,
                            'rank' => 1,
                            'loa_number' => 1,
                            'trainee_id' => '$trainee_info.id_number',
                            'trainee_name' => '$trainee_info.name',
                            'trainee_email' => '$trainee_info.email',
                            'trainee_photo' => '$trainee_info.PHOTOURL',
                            'trainee_rank' => '$trainee_info.rank',
                        ]
                    ]
                ]);

            $traineeDetails = iterator_to_array($traineeDetails);

            if (!empty($traineeDetails)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No trainee details found for the given ID.',
                    'data' => $traineeDetails[0],
                ], 200);
            }


            return response()->json([
                'status' => 'success',
                'message' => 'No trainee details found for the given ID.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching trainee details.',
                'error' => $e->getMessage(),
            ], status: 500);
        }
    }

    public function save_trainee_score(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                '_id' => 'required|string',
                'status' => 'required|string',
                'rActive' => 'required',
                'rKnowledge' => 'required',
                'rCommunication' => 'required',
                'feedback' => 'required|string',
                'grade' => 'integer|between:0.0,100.0',
                'score' => 'required|string',
                'formatNo' => 'required|string',
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
            $updateData = DB::table('attendance-detail')
                ->where('_id', $request->input('_id'))
                ->update([
                    'status' => $request->input('status'),
                    'rActive' => $request->input('rActive'),
                    'rKnowledge' => $request->input('rKnowledge'),
                    'rCommunication' => $request->input('rCommunication'),
                    'feedback' => $request->input('feedback'),
                    'grade' => $request->input('grade'),
                    'score' => $request->input('score'),
                    'formatNo' => $request->input('formatNo'),
                ]);

            if ($updateData) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Trainee score saved successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to save trainee score.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving trainee score.',
                'error' => $e->getMessage(),
            ], status: 500);
        }

    }

    public function confirm_attendance(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                '_id' => 'required|string',
                'attendanceType' => 'required|string',
                'remarks' => 'nullable|string',
                'signature' => 'required|image',
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
            $img_name = null;
            if ($request->hasFile('signature')) {
                $img_name = $request->_id . '_' . $request->signature->getClientOriginalExtension();
                Storage::disk('signatures')->put($img_name, file_get_contents($request->signature));
            }

            $updateData = DB::table('attendance')
                ->where('_id', $request->input('_id'))
                ->update([
                    'status' => 'confirmation',
                    'attendanceType' => $request->input('attendanceType'),
                    'remarks' => $request->input('remarks'),
                    'signatureInstructor' => $img_name,
                    'keyAttendance' => null,

                ]);

            if ($updateData) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Attendance confirmed successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to confirm attendance.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while confirming attendance.',
                'error' => $e->getMessage(),
            ], status: 500);
        }
    }

    public function get_need_feedback(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'idtraining' => 'required|string',
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            $data = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idtraining' => $request->input('idtraining'),
                            'status' => 'donescoring',
                            'feedbackForInstructor' => null,
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'attendance',
                            'localField' => 'idattendance',
                            'foreignField' => '_id',
                            'as' => 'attendance_detail'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$attendance_detail',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 1,
                            'idtraining' => 1,
                            'signature' => 1,
                            'status' => 1,
                            'rActive' => 1,
                            'rKnowledge' => 1,
                            'rCommunication' => 1,
                            'grade' => 1,
                            'score' => 1,
                            'feedback' => 1,
                            'feedbackForInstructor' => 1,

                            'attendance_id' => '$attendance_detail.id_number',
                            'attendance_subject' => '$attendance_detail.subject',
                            'attendance_date' => '$attendance_detail.date',
                            'attendance_instructor' => '$attendance_detail.instructor',
                            'attendance_trainingType' => '$attendance_detail.trainingType',
                            'attendance_venue' => '$attendance_detail.venue',
                            'attendance_room' => '$attendance_detail.room',
                            'attendance_department' => '$attendance_detail.department',

                        ]
                    ]
                ]);

            $data = iterator_to_array($data);
            if (!empty($data)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Feedback status fetched successfully.',
                    'data' => $data,
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No feedback status found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching feedback status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function examinee_feedback(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                '_id' => 'required|string',
                'idtraining' => 'required|string',
                'rMastery' => 'required|integer',
                'rTimeManagement' => 'required|integer',
                'rTeachingMethod' => 'required|integer',
                'feedbackForInstructor' => 'required|string',
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], status: 422);
        }

        try {
            $updateData = DB::table('attendance-detail')
                ->where([['_id', $request->input('_id')], ['idtraining', $request->input('idtraining')]])
                ->update([
                    'rMastery' => $request->input('rMastery'),
                    'rTimeManagement' => $request->input('rTimeManagement'),
                    'rTeachingMethod' => $request->input('rTeachingMethod'),
                    'feedbackForInstructor' => $request->input('feedbackForInstructor'),
                ]);

            if ($updateData) {
                return response()->json([
                    'status' => 'Success',
                    'message' => 'Feedback submitted successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Failed to submit feedback.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => 'An error occurred while submitting feedback.',
                'error' => $e->getMessage(),
            ], status: 500);
        }
    }

    public function check_feedback(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                '_id' => 'required|string',
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
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            '_id' => $request->input('_id'),
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'attendance',
                            'localField' => 'idattendance',
                            'foreignField' => '_id',
                            'as' => 'detail'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$detail',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$instructor_info',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'users',
                            'localField' => 'detail.instructor',
                            'foreignField' => 'id_number',
                            'as' => 'instructor_info'
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 1,
                            'idtraining' => 1,
                            'rMastery' => 1,
                            'rTimeManagement' => 1,
                            'rTeachingMethod' => 1,
                            'feedbackForInstructor' => 1,
                            'detail_subject' => '$detail.subject',
                            'detail_department' => '$detail.department',
                            'detail_trainingType' => '$detail.trainingType',
                            'detail_date' => '$detail.date',
                            'detail_venue' => '$detail.venue',
                            'detail_room' => '$detail.room',
                            'instructor_id' => '$instructor_info.id_number',
                            'instructor_name' => '$instructor_info.name',
                        ]
                    ]
                ]);

            $data = iterator_to_array($data);
            if (!empty($data)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Feedback status fetched successfully.',
                    'data' => $data[0],
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching feedback status.',
                'error' => $e->getMessage(),
            ], status: 500);
        }

    }

    public function get_trainee_profile(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'id_number' => 'required|string',
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            $data = DB::table('users')->where('id_number', $request->input('id_number'))->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.',
                ], 204);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Trainee fetch successfully.',
                'data' => $data[0],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 204);
        }

    }
}
