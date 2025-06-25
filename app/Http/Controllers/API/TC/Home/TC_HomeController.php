<?php

namespace App\Http\Controllers\API\TC\Home;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use App\Models\TrainingType;

class TC_HomeController extends Controller
{
    public function get_training_cards()
    {
        // $data = TrainingType::where('is_delete', false)->get();
        $data = DB::table('trainingType')
            ->where('is_delete', false)->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Training Types retrieved successfully.',
            'data'=>$data,
        ], 200);
    }



    // Examinee

    public function get_need_feedback(){
        $data = DB::table('attendance')
            ->where('status', '==', 'done');

    }

    public function get_att_trainees()
    {
        $data = DB::table('users')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Instructors retrieved successfully.',
            'data' => $data,
        ], 200);
    }
}
