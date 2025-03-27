<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller{

    // User Registration
    public function register(Request $request)
    {
        // dd($request->all());
        try {
            // Validate request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:3|confirmed',
            ]);
    
            // Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors separately
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            // Handle other unexpected errors
            return response()->json([
                'success' => false,
                'error' => 'Registration failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // User Login
    public function login(Request $request)
    {
        // dd($request->all());
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt authentication
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }
        
        // Generate token
        $user = Auth::user();
        
        $token = $user->createToken('auth_token')->plainTextToken;
        

        return response()->json(['message' => 'Login successful', 'token' => $token, 'user' => $user]);
    }

    // User Logout
    public function logout(Request $request)
    {
        // dd($request->user());
        $request->user()->tokens()->delete(); // Revoke all tokens

        return response()->json(['message' => 'Logged out successfully']);
    }
}
