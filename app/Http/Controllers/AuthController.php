<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phoneNumber' => 'required|string',
        ]);

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phoneNumber' => $data['phoneNumber']
        ]);

        $user->assignRole('user');

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => ' user registered successfully ',
            'user' => $user->only(['id', 'username', 'email']),
            'token' => $token,
            'role' => $user->getRoleNames()->first(),
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => ' user loggedin successfully ',
            'user_id' => $user->id,
            'token' => $token,
            'role' => $user->getRoleNames()->first(),

        ], 200);
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'User logged out successfully'
            ], 200);
        }

        return response()->json([
            'error' => 'Logout failed or user not authenticated'
        ], 401);
    }
}
