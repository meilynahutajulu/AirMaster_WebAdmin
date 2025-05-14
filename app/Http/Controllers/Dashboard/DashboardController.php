<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use App\Models\userLogin as UserLogin;


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

        $loginData = UserLogin::all()
            ->map(function (UserLogin $item) {
                $item->formatted_date = $item->login_date->format('Y-m-d');
                return $item;
            })
            ->groupBy('formatted_date')
            ->map(function ($group) {
                return [
                    'date' => $group->first()->formatted_date,
                    'mobile' => $group->count(),
                ];
            })
            ->values();

            // dd($loginData); 
    
    
        
        return Inertia::render('dashboard', [
            'countInstructor' => $countInstructor,
            'countExaminee' => $countExaminee,
            'countAdministrator' => $countAdministrator,
            'countCPTS' => $countCPTS,
            'countValid' => $countValid,
            'countInvalid' => $countInvalid,
            'userExpired' => $userExpired,
            'totalExpired' => $totalExpired,
            'loginData' => $loginData,          
            
        ]);
    }
}
