<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;


class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('type', '<>', 'SUPERADMIN')->count();

        $countInstructor = User:: where('type', '=', 'Instructor')->count();
        $countExaminee = User:: where('type', '=', 'Examinee')->count();
        $countAdministrator = User:: where('type', '=', 'Administrator')->count();
        $countCPTS = User:: where('type', '=', 'CPTS')->count();

        $countValid = User:: where([['status', '=', 'VALID'], ['type', '<>', 'SUPERADMIN']])->count();
        $countInvalid = User:: where([['status', '=', 'INVALID'], ['type', '<>', 'SUPERADMIN']])->count();
        
        $userExpired = User::where('type', '<>', 'SUPERADMIN')
                    ->where('license_expiry', '<', now())
                    ->pluck('id_number');

        $totalExpired = $userExpired->count();

        // dd($userExpired);
        // dd($totalExpired);
        
        return Inertia::render('dashboard', [
            'countInstructor' => $countInstructor,
            'countExaminee' => $countExaminee,
            'countAdministrator' => $countAdministrator,
            'countCPTS' => $countCPTS,
            'countValid' => $countValid,
            'countInvalid' => $countInvalid,
            'userExpired' => $userExpired,
            'totalExpired' => $totalExpired,
            
        ]);
    }
}
