<?php

namespace App\Http\Controllers\API\TC\Home\Administrator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use MongoDB\Client;

class TC_AdministratorHomeController extends Controller
{
    public function get_total_participant(Request $request)
    {
        $validated = Validator::make($request->all(), [
            '_id' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }


        try {
            $dataparticipant = DB::table('attendance-detail')
                ->select('idtraining')
                ->where('idattendance', $request->_id)
                ->get();
            return response()->json([
                'status' => 'success',
                'data' => $dataparticipant
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching the total participants.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_pilot_list()
    {
        $data = DB::table('users')
            ->select('id_number', 'name', 'email')
            ->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Instructors retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    public function recurrent_date_training(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $data = DB::table('trainingType')
                ->where('_id', $request->id)
                ->select('recurrent')
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Training type not found.',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Recurrent date retrieved successfully.',
                'data' => $data->recurrent,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching the recurrent date.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirm_class_attendance(Request $request)
    {
        $validated = Validator::make($request->all(), [
            '_id' => 'required|string',
            'idPilotAdministrator' => 'required|string',
            'valid_to' => 'required|date',
            'signature' => 'required|image',
            'absentTrainees' => '',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $img_name = null;
            if ($request->hasFile('signature')) {
                $img_name = $request->_id . '_pilot_administrator.' . $request->signature->getClientOriginalExtension();
                Storage::disk('signatures')->put($img_name, file_get_contents($request->signature));
            }

            $updateSuccess = DB::table('attendance')
                ->where('_id', $request->_id)
                ->update([
                    'idPilotAdministrator' => $request->idPilotAdministrator,
                    'valid_to' => $request->valid_to,
                    'signaturePilotAdministrator' => $img_name,
                    'status' => 'done',
                    'expiry' => 'VALID',
                ]);

            if (!$updateSuccess) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update attendance record.',
                ], 500);
            }

            if (request()->absentTrainees === null || request()->absentTrainees === '') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Attendance confirmed successfully, no absent trainees.',
                ], 200);
            } else {
                $absentTrainees = is_array($request->absentTrainees)
                    ? $request->absentTrainees
                    : explode(',', $request->absentTrainees);

                $insertErrors = [];
                foreach ($absentTrainees as $absentTrainee) {
                    $success = DB::table('absent')->insert([
                        'idattendance' => $request->_id,
                        'id' => trim($absentTrainee) . '-' . $request->_id,
                        'idtraining' => trim($absentTrainee),
                    ]);

                    if (!$success) {
                        $insertErrors[] = trim($absentTrainee);
                    }
                }
            }

            if (count($insertErrors) > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attendance updated, but failed to save some absent trainees.',
                    'errors' => $insertErrors,
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance confirmed successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while confirming attendance.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function get_absent_participant(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idattendance' => 'required|string'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $dataAbsent = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('absent')
                ->aggregate(
                    [
                        [
                            '$match' => [
                                'idattendance' => $request->input('idattendance'),
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
                                'idtraining' => 1,
                                'idattendance' => 1,

                                'trainee_id' => '$trainee_info.id_number',
                                'trainee_name' => '$trainee_info.name',
                                'trainee_email' => '$trainee_info.email',
                                'trainee_photo' => '$trainee_info.photo_url',
                                'trainee_rank' => '$trainee_info.rank',
                                'trainee_loaNo' => '$trainee_info.loa_number',
                                'trainee_status' => '$trainee_info.status',
                                'trainee_hub' => '$trainee_info.hub',
                                'trainee_licenseNo' => '$trainee_info.license_number',

                            ]
                        ]

                    ]
                );

            $dataAbsent = iterator_to_array($dataAbsent);
            if (!empty($dataAbsent)) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'Absent trainees retrieved successfully.',
                    'data' => $dataAbsent,
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'No absent trainee found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching trainee details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_total_absent_trainee()
    {
        $validated = Validator::make(request()->all(), [
            'idattendance' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $absentCount = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('absent')
                ->countDocuments(['idattendance' => request()->input('idattendance')]);

            return response()->json([
                'status' => 'success',
                'message' => 'Total absent trainees retrieved successfully.',
                'data' => $absentCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching total absent trainees.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_attendance_detail_done(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idattendance' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $data = DB::table('attendance-detail')
                ->where('idattendance', $request->idattendance)
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance details retrieved successfully.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching attendance details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_instructor_data(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idPilotAdministrator' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $data = DB::table('users')
                ->where('id_number', $request->idPilotAdministrator)
                ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub', 'license_number')
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor not found.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Instructor data retrieved successfully.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching instructor data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function get_administrator_data(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idPilotAdministrator' => 'required|string',
            '_id' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $data = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance')
                ->aggregate([
                    [
                        '$match' => [
                            'idPilotAdministrator' => $request->input('idPilotAdministrator'),
                            '_id' => $request->input('_id')
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'users',
                            'localField' => 'idPilotAdministrator',
                            'foreignField' => 'id_number',
                            'as' => 'administrator_info'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$administrator_info',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$addFields' => [
                            'administrator_id' => '$administrator_info.id_number',
                            'administrator_name' => '$administrator_info.name',
                            'administrator_email' => '$administrator_info.email',
                        ]
                    ],
                    [
                        '$project' => [
                            'administrator_info' => 0,

                        ]
                    ]
                ]);
            $data = iterator_to_array($data);
            if (!empty($data)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Administrator data retrieved successfully.',
                    'data' => $data[0],
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No administrator data found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching administrator data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_participant_detail(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idattendance' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {

            $data = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idattendance' => $request->input('idattendance'),
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
                            'idtraining' => 1,
                            'idattendance' => 1,
                            'signature' => 1,
                            'trainee_id' => '$trainee_info.id_number',
                            'trainee_name' => '$trainee_info.name',
                            'trainee_email' => '$trainee_info.email',
                            'trainee_photo' => '$trainee_info.photo_url',
                            'trainee_rank' => '$trainee_info.rank',
                            'trainee_loaNo' => '$trainee_info.loa_number',
                            'trainee_status' => '$trainee_info.status',
                            'trainee_hub' => '$trainee_info.hub',
                            'trainee_licenseNo' => '$trainee_info.license_number',
                        ]
                    ]
                ]);

            $data = iterator_to_array($data);
            if (!empty($data)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Participant details retrieved successfully.',
                    'data' => $data,
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'No participant details found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching participant details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSignature(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'filename' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ], 400);
        }

        try {
            $disk = Storage::disk('signatures');

            if ($disk->exists($request->filename)) {
                $img = $disk->get($request->filename);
                return response($img, 200)->header('Content-Type', 'image/jpeg');
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Device image not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve file',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_participant_history(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idtraining' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $history = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idtraining' => $request->input('idtraining'),
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'attendance',
                            'localField' => 'idattendance',
                            'foreignField' => '_id',
                            'as' => 'history_info'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$history_info',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$match' => [
                            'history_info.status' => 'done',
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 1,
                            'idtraining' => 1,
                            'idattendance' => 1,
                            'signature' => 1,
                            'history_id' => '$history_info._id',
                            'history_subject' => '$history_info.subject',
                            'history_valid_to' => '$history_info.valid_to',
                        ]
                    ]
                ]);
            $history = iterator_to_array($history);
            if (!empty($history)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Participant history retrieved successfully.',
                    'data' => $history,
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No participant history found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching participant history.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_participant_training_history(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'idtraining' => 'required|string',
            'subject' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $history = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idtraining' => $request->input('idtraining'),
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'attendance',
                            'localField' => 'idattendance',
                            'foreignField' => '_id',
                            'as' => 'history_info'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$history_info',
                            'preserveNullAndEmptyArrays' => true
                        ]
                    ],
                    [
                        '$match' => [
                            'history_info.subject' => $request->input('subject'),
                            'history_info.status' => 'done',
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 1,
                            'idtraining' => 1,
                            'idattendance' => 1,
                            'signature' => 1,
                            'history_id' => '$history_info._id',
                            'history_instructor' => '$history_info.instructor',
                            'history_date' => '$history_info.date',
                            'history_subject' => '$history_info.subject',
                            'history_valid_to' => '$history_info.valid_to',
                            'history_department' => '$history_info.department',
                            'history_venue' => '$history_info.venue',
                            'history_room' => '$history_info.room',
                            'history_training_type' => '$history_info.trainingType',

                        ]
                    ]
                ]);
            $history = iterator_to_array($history);
            if (!empty($history)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Participant training history retrieved successfully.',
                    'data' => $history,
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No participant training history found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching participant training history.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_instructor_training(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'instructor' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $data = DB::table('users')
                ->select('name')
                ->where('_id', $request->instructor)
                ->get();


            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor not found.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Instructor found.',
                'data' => $data[0],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching instructor data.',
                'error' => $e->getMessage(),
            ], status: 500);
        }
    }

    public function get_trainee_training(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idtraining' => 'required|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $data = DB::table('users')
                ->select('name')
                ->where('_id', $request->idtraining)
                ->get();


            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trainee not found.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Trainee found.',
                'data' => $data[0],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching trainee data.',
                'error' => $e->getMessage(),
            ], status: 500);
        }
    }

    public function get_history_training_trainee(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'idtrainee' => 'required|string',
            'subject' => 'required|string',
            'longlist' => 'required|integer',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors()->first(),
            ], 400);
        }

        try {
            $data = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idtraining' => $request->input('idtrainee'),
                            'status' => 'donescoring',
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'attendance',
                            'localField' => 'idattendance',
                            'foreignField' => '_id',
                            'as' => 'attendance_history'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$attendance_history',
                            'preserveNullAndEmptyArrays' => false
                        ]
                    ],
                    [
                        '$match' => [
                            'attendance_history.subject' => $request->input('subject'),
                            'attendance_history.status' => 'done',
                            'attendance_history.is_delete' => false,
                        ]
                    ],
                    [
                        '$sort' => [
                            'attendance_history.valid_to' => 1
                        ]
                    ],
                    [
                        '$facet' => [
                            'metadata' => [
                                ['$count' => 'total']
                            ],
                            'data' => [
                                ['$skip' => 0],
                            ]
                        ]
                    ]
                ]);

            $raw = iterator_to_array($data, false)[0] ?? [];

            $total = $raw['metadata'][0]['total'] ?? 0;
            $longlist = (int) $request->input('longlist');
            $skip = max($total - $longlist, 0);

            // Ambil ulang data tapi sekarang `skip` langsung fix
            $data = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('attendance-detail')
                ->aggregate([
                    [
                        '$match' => [
                            'idtraining' => $request->input('idtrainee'),
                            'status' => 'donescoring',
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'attendance',
                            'localField' => 'idattendance',
                            'foreignField' => '_id',
                            'as' => 'attendance_history'
                        ]
                    ],
                    [
                        '$unwind' => [
                            'path' => '$attendance_history',
                            'preserveNullAndEmptyArrays' => false
                        ]
                    ],
                    [
                        '$match' => [
                            'attendance_history.subject' => $request->input('subject'),
                            'attendance_history.status' => 'done',
                            'attendance_history.is_delete' => false,
                        ]
                    ],
                    ['$sort' => ['attendance_history.valid_to' => 1]],
                    ['$skip' => $skip],
                    ['$limit' => $longlist],
                    [
                        '$project' => [
                            '_id' => 1,
                            'idtraining' => 1,
                            'idattendance' => 1,
                            'attendance_history_id' => '$attendance_history._id',
                            'attendance_history_subject' => '$attendance_history.subject',
                            'attendance_history_valid_to' => '$attendance_history.valid_to',
                            'attendance_history_date' => '$attendance_history.date',
                        ]
                    ],
                ]);

            $data = iterator_to_array($data);
            if (!empty($data)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Training history retrieved successfully.',
                    'data' => $data
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'No training history found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching training history.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_training_remarks(Request $request)
    {
        try {
            $data = DB::table('trainingRemark')->get();

            $sorted = $data->sortBy('id')->values(); 

            if ($sorted->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No remarks found for this training.',
                    'data' => [],
                ], 200); 
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Training remarks retrieved successfully.',
                'data' => $sorted,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching training remarks.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_all_pilot () {
        try {
            $pilots = DB::table('users')
                ->whereIn('INSTRUCTOR', ['FIS', 'FIA', 'GI', 'CCP'])
                ->orWhere('rank', 'CAPT')
                ->orWhere('rank', 'FO')
                ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub', 'license_number')
                ->get();

            if ($pilots->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No pilots found.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pilots retrieved successfully.',
                'data' => $pilots,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching pilots.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}