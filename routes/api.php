<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Your Project API",
 *     version="1.0.0",
 *     description="API documentation with Swagger"
 * )
 */

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('v0/register', [AuthController::class, 'register']); 
Route::post('v0/login', [AuthController::class, 'login']); 

Route::get('v0/auth/google', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('v0/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('v0/logout', [AuthController::class, 'logout']);

    //Cart 
    Route::post('v0/carts/items', [CartController::class,'addItemToCart']);
    Route::put('v0/cart-items/{cartItem}', [CartController::class,'updateItemCart']);
    Route::delete('v0/cart-items/{cartItem}', [CartController::class,'deleteItemCart']);
    
    //Order
    Route::get('v0/orders',[OrderController::class,'getAllOrders']);
    Route::post('v0/orders',[OrderController::class,'createOrder']);
    Route::put('v0/orders/{orderId}',[OrderController::class,'updateOrder']);

    //Address
    Route::get('v0/address',[AddressController::class,'getAllAddress']);
    Route::post('v0/address',[AddressController::class,'createAddress']);
    Route::put('v0/address/{addressId}',[AddressController::class,'updateAddress']);
    Route::delete('v0/address/{address}',[AddressController::class,'deleteAddress']);

});

// Route::post('/login', function (Request $request) {
//     return response()->json([
//         'message' => 'User logged in successfully!',
//     ], 200, ['Content-Type' => 'application/json']);
// });