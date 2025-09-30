<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends ApiController
{

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) return $this->error('Invalid credentials', 401);

        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->success([], 'Logged out');
    }
}
