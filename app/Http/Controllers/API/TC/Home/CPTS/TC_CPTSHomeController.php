<?php

namespace App\Http\Controllers\API\TC\Home\CPTS;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\isEmpty;



class TC_CPTSHomeController extends Controller
{
    public function get_pilot_only()
    {
        try {
            $pilots = DB::table('users')
                ->where('INSTRUCTOR', [''])
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

    public function get_pilot_detail()
    {
        $instructorFIS = DB::table('users')
            ->whereIn('INSTRUCTOR', ['FIS'])
            ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub')
            ->count();

        $instructorFIA = DB::table('users')
            ->whereIn('INSTRUCTOR', ['FIA'])
            ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub')
            ->count();

        $instructorGI = DB::table('users')
            ->whereIn('INSTRUCTOR', ['GI'])
            ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub')
            ->count();

        $instructorCCP = DB::table('users')
            ->whereIn('INSTRUCTOR', ['CCP'])
            ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub')
            ->count();

        $pilotFO = DB::table('users')
            ->where('rank', 'FO')
            ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub')
            ->count();

        $pilotCAPT = DB::table('users')
            ->where('rank', 'CAPT')
            ->select('id_number', 'name', 'email', 'photo_url', 'rank', 'loa_number', 'status', 'hub')
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Instructors retrieved successfully.',
            'data' => [
                'FIS' => $instructorFIS,
                'FIA' => $instructorFIA,
                'GI' => $instructorGI,
                'CCP' => $instructorCCP,
                'FO' => $pilotFO,
                'CAPT' => $pilotCAPT
            ],
        ], 200);
    }

    public function get_attendance_list_cpts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trainingType' => 'required|string',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $trainingType = $request->input('trainingType');
        $from = $request->input('from'); // format: YYYY-MM-DD
        $to = $request->input('to');


        $query = DB::table('attendance')
            ->where('is_delete', false)
            ->where('status', 'done');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        }

        if($trainingType === 'ALL') {
            $attendanceList = $query->get();
        } else {
            $attendanceList = $query
                ->where('subject', $trainingType)
                ->get();
        }

        // dd($attendanceList);

        // Ambil ID-nya
        $attendanceIds = $attendanceList->pluck(value: 'id');
        // dd($attendanceIds);

        // Hitung hadir dan tidak hadir
        $absentCount = DB::table('absent')
            ->whereIn('idattendance', $attendanceIds)
            ->count();

        $presentCount = DB::table('attendance-detail')
            ->whereIn('idattendance', $attendanceIds)
            ->count();

        return response()->json([
            'status' => 'success',
            'absent' => $absentCount,
            'present' => $presentCount,
            'attendance' => $attendanceList,
        ]);
    }
}