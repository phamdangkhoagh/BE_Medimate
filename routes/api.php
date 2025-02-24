<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::post('/api/register', [AuthController::class, 'register']); 
// });

Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', function (Request $request) {
    return response()->json([
        'message' => 'User logged in successfully!',
    ], 200, ['Content-Type' => 'application/json']);
});