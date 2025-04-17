<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('users/data', [
            'users' => User::all(),
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


        return redirect('users')->with('success', 'User has been successfully saved');
    }

    public function delete($id)
    {
        $user = User::where("id", $id)->first();
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

        $user = DB::table('users')->where('id', '=', $decrypted)->first();

        return Inertia::render('users/edit', [
            'user' => $user,
        ]);
    }
}
