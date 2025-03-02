<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Validate input
        $validatedData = $request->validate([
            'username' => 'required|max:55',
            'email' => 'required|email:rfc,dns|unique:users',
            'phone' => 'nullable|numeric|unique:users|digits:10',
            'password' => 'required|string|min:8|confirmed',
            'point' => 'nullable|integer',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|integer',
            'role' => 'nullable|string|in:customer,admin',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);
        
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath  = $request->file('image')->store('profiles', 'public');
            $validatedData['image'] = $imagePath;
        }

        //Insert into database
        $user = User::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'password' => $validatedData['password'],
            'role' => $validatedData['role'] ?? 'customer',
            'image' => $validatedData['image'] ?? null,
        ]);

        $accessToken = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
            'access_token' => $accessToken
        ], 200);
    }

    //Login with email and password
    public function login(Request $request)
    {
        //Validation input
        $validatedData = $request->validate([
            'email' => 'required|email:rfc,dns',
            'password' => 'required|string',
        ]);

        //if email and password is correct then login
        if (!auth()->attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        $accessToken =  $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully!',
            'user' => $user,
            'access_token' => $accessToken
        ], 200);
    }
    
    //Login with phone
    //Logout 
    public function logout (Request $request){
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'User logged out successfully!',
        ], 200);
    }
}
