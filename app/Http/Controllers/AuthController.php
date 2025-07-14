<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     *
     *
     * @response 201 {
     * "status_code": 201,
     * "message": "Akun berhasil dibuat",
     * "data": {
     * "id": 1,
     * "nama": "John Doe",
     * "email": "john@example.com",
     * "role_id": 2,
     * "status_akun": "Belum Terverifikasi"
     * }
     * }
     *
     * @response 400 {
     * "status_code": 400,
     * "message": "The email has already been taken.",
     * "data": null
     * }
     *
     * @response 500 {
     * "status_code": 500,
     * "message": "Internal server error"
     * }
     */
    public function register(RegisterRequest $request)
    {
        try {

            $user = new User;
            $user->nama = $request->nama;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role_id = 2;
            $user->status_akun = 'Belum Terverifikasi';
            $user->save();

            Customer::create([
                'user_id' => $user->id,
                'nama' => $user->nama,
            ]);

            return response()->json([
                'status_code' => 201,
                'message' => 'Akun berhasil dibuat',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'status_akun' => $user->status_akun,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    /**
     * Login
     * 
     * @response 200 {
     * "message": "Login berhasil",
     * "status_code": 200,
     * "data": {
     * "id": 1,
     * "nama": "John Doe",
     * "email": "john@example.com",
     * "role_id": 1,
     * "token": "eyJ0eXAiOiJKV1Qi..."
     * }
     * }
     *
     * @response 401 {
     * "message": "Email atau password salah",
     * "status_code": 401,
     * "data": null
     * }
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah',
                'status_code' => 401,
                'data' => null,
            ], 401);
        }

        try {
            $user = Auth::guard('api')->user();

            $formatedUser = [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role->nama,
                'status_akun' => $user->status_akun,
                'token' => $token,
            ];

            return response()->json([
                'message' => 'Login berhasil',
                'status_code' => 200,
                'data' => $formatedUser,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    /**
     * Get current authenticated user.
     *
     * @authenticated
     *
     * @response 200 {
     * "message": "User ditemukan",
     * "status_code": 200,
     * "data": {
     * "id": 1,
     * "nama": "John Doe",
     * "email": "john@example.com",
     * "role_id": 1
     * }
     * }
     */
    public function me()
    {
        try {
            $user = Auth::guard('api')->user();

            $formatedUser = [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role->nama,
            ];

            return response()->json([
                'message' => 'User ditemukan',
                'status_code' => 200,
                'data' => $formatedUser,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    /**
     * Logout user
     *
     * @authenticated
     *
     * @response 200 {
     * "message": "Logout berhasil",
     * "status_code": 200,
     * "data": null
     * }
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Logout berhasil',
            'status_code' => 200,
            'data' => null,
        ]);
    }
}