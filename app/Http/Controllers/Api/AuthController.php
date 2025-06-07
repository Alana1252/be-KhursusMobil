<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auth'     => ['required', 'string'],
            'password' => ['required', 'string'],

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->auth)
                    ->orWhere('no_hp', $request->auth)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username / No HP atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $role = $user->getRoleNames(); // Collection of role names

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'no_hp' => $user->no_hp,
                'role' => $role[0] ?? null,
            ]
        ]);
    }

    // 📝 REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'  => ['required', 'string', 'max:255', 'unique:users'],
            'no_hp'     => ['required', 'string', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'no_hp' => $request->no_hp,
            'name' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('Siswa');
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    // 🚪 LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    // 👀 GET ALL USERS (KASIR SAJA)
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    // 📄 GET USER DETAIL
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load('roles')
        ]);
    }

    // ➕ CREATE USER (Oleh KASIR)
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username'  => ['required', 'string', 'max:255', 'unique:users'],
            'no_hp'     => ['required', 'string', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:8'],
            'role'      => ['required', 'in:Siswa,Instruktur,Kasir,Owner'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'no_hp' => $request->no_hp,
            'no_hp' => $request->no_hp,
            'name' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat',
            'user' => $user
        ]);
    }

    // ✏️ UPDATE USER
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['sometimes', 'required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'no_hp' => ['sometimes', 'required', 'string', 'max:255', 'unique:users,no_hp,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'in:Siswa,Instruktur,Kasir,Owner']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        if ($request->has('username')) {
            $user->username = $request->username;
            $user->name = $request->username;
        }
        if ($request->has('no_hp')) $user->no_hp = $request->no_hp;
        if ($request->filled('password')) $user->password = Hash::make($request->password);

        $user->save();

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'no_hp' => $user->no_hp,
                'role' => $user->getRoleNames()[0] ?? null,
            ]
        ]);
    }

    // ❌ DELETE USER
    public function destroy(User $user)
{
    // Hapus pesanan yang dimiliki user tersebut (jika memang tidak dibutuhkan)
    $user->pesanan()->delete(); // Pastikan ada relasi pesanan() di model User

    $user->delete();

    return response()->json([
        'success' => true,
        'message' => 'User berhasil dihapus'
    ]);
}

}
