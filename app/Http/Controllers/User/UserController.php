<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {        
        return Inertia::render('users/data', [
            'users'=> User::all(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('users/create', [
            'users'=> User::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            '_id' => 'required|string',
            'ATTRIBUTE' => 'required|string',
            'HUB' => 'required|string',
            'STATUS' => 'required|string',
            'ID NO' => 'required|string',
            'LOA NO' => 'required|string',
            'LICENSE NO' => 'required|string',
            'TYPE' => 'required|string',
            'RANK' => 'required|string',
            'LICENSE EXPIRY' => 'required|string',
            'NAME' => 'required|string',
            'EMAIL' => 'required|email|regex:/^[\w.+-]+@airasia\.com$/',
        ]);
    
        User::create($validated);


        return redirect()->route('users')->with('message', 'User berhasil disimpan');
    }

    public function delete($id)
    {
        $user = User::where("ID NO", $id)->first();
        if (!$user) {
            return redirect()->route('users')->with('error', 'User tidak ditemukan');
        }
        $user->delete();

        return redirect()->route('users')->with('message', 'User berhasil dihapus');
    }

    public function edit($id)
    {
        $user = User::where("ID NO", $id)->first(); 

        return Inertia::render('users/edit', [
            'user' => $user,
        ]);
    }   
}
