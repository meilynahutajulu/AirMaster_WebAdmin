<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Carbon\Carbon;


use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('users/data', [
            'users' => User::where('type', '<>', 'SUPERADMIN')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('users/create', [
            'users' => User::all(),
        ]);
    }

    public function store(Request $request)
    {
        
        $validated = $request->validate([
            '_id' => 'required|string',
            'photo_url' => 'nullable|string',
            'attribute' => 'required|string',
            'hub' => 'required|string',
            'status' => 'required|string',
            'id_number' => 'required|string',
            'loa_number' => 'required|string',
            'license_number' => 'required|string',
            'type' => 'required|string',
            'rank' => 'required|string',
            'license_expiry' => 'required|date',
            'name' => 'required|string',
            'email' => 'required|email|regex:/^[\w.+-]+@airasia\.com$/',
            
        ]);
        
        // $validated['license_expiry'] = Carbon::parse($validated['license_expiry']);
        

        User::create([
            'id_number' => $validated['id_number'],
            'photo_url' => $validated['photo_url'],
            'attribute' => $validated['attribute'],
            'hub' => $validated['hub'],
            'status' => $validated['status'],
            'loa_number' => $validated['loa_number'],
            'license_number' => $validated['license_number'],
            'type' => $validated['type'],
            'rank' => $validated['rank'],
            'license_expiry' => Carbon::parse($request->license_expiry),
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        


        return redirect('users')->with('success', 'User has been successfully saved');
    }

    public function delete($id)
    {
        $user = User::where("id_number", $id)->first();
        // dd($user);
        
        if (!$user) {
            return redirect('users')->with('error', 'User not found');
        }


        $user->delete();

        return redirect('users')->with('success', 'User successfully deleted');
    }

    public function edit($id)
    {
        $user = User::where('id_number', $id)->first();


        return Inertia::render('users/edit', [
            'user' => $user,
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $user = User::where('id_number', $id)->first();
        
        $validated = $request->validate([
            'attribute' => 'required|string',
            'hub' => 'required|string',
            'status' => 'required|string',
            'id_number' => 'required|unique:users,id_number'. $user->id_number,
            'loa_number' => 'required|string',
            'license_number' => 'required|string',
            'type' => 'required|string',
            'rank' => 'required|string',
            'license_expiry' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email|regex:/^[\w.+-]+@airasia\.com$/',
        ]);
        
        // dd($validated);
        // exit;

        if (!$user) {
            return redirect('users')->with('error', 'User not found');
        }

        $user->update($validated);

        return redirect('users')->with('success', 'User has been successfully updated');
    }

    public function expired()
    {
        return Inertia::render('users/data', [
            'users' => User::where('type', '<>', 'SUPERADMIN')
                    -> where('license_expiry', '<', now())->get(),
        ]);
    }
}
