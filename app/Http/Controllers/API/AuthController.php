<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Validate input
        $validatedData = $request->validate([
            'username' => 'required|max:55',
            'email' => 'required|email:rfc,dns|unique:users',
            'phone' => 'required|numeric|unique:users|digits:10',
            'password' => 'required|string|min:8|confirmed',
            // 'password_confirm' => 'required|string|same:password',
            'point' => 'nullable|integer',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|integer',
            'role' => 'nullable|string|in:customer,admin',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']);

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
        ], 201);
    }

    //Login
    //Logout 
}
