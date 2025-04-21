<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use function Psy\debug;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('users/data', [
            'users' => User::where('type', '<>', 'SUPERADMIN')->get(

            ),
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
            'attribute' => 'required|string',
            'hub' => 'required|string',
            'status' => 'required|string',
            'id_number' => 'required|string',
            'loa_number' => 'required|string',
            'license_number' => 'required|string',
            'type' => 'required|string',
            'rank' => 'required|string',
            'license_expiry' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email|regex:/^[\w.+-]+@airasia\.com$/',
        ]);

        User::create($validated);


        return redirect('users')->with('success', 'User has been successfully saved');
    }

    public function delete($id)
    {
        $user = User::where("id_number", (int) $id)->first();
        if (!$user) {
            return redirect('users')->with('error', 'User not found');
        }
        $user->delete();

        return redirect('users')->with('success', 'User successfully deleted');
    }

    public function edit($id)
    {
        
        $key = env('ENCRYPTION_KEY', '1234567890123456');
        $iv = env('ENCRYPTION_IV', 'abcdef9876543210');
        
        $ciphertext_raw = base64_decode(strtr($id, '-_', '+/'));
        $decrypted = openssl_decrypt($ciphertext_raw, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        if (!$decrypted || !is_numeric($decrypted)) {
            abort(404);
        }

        
        $user = User::where('id_number', '114432156')->first();
        
        return Inertia::render('users/edit', [
            'user' => $user,
        ]);
    }
}
