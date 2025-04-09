<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CartDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Your Project API",
 *     version="1.0.0",
 *     description="API documentation with Swagger"
 * )
 */
/**
 * @OA\Post(
 *     path="v0/carts/items",
 *     summary="Add item to cart",
 *     tags={"carts"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"product_id", "quantity"},
 *             @OA\Property(property="product_id", type="integer", example=1),
 *             @OA\Property(property="quantity", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Item added to cart successfully"
 *     )
 * )
 */
class CartController extends Controller
{
    public function addItemToCart(Request $request)
    {
        // Validate input
        $validateData = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Get the authenticated user
        $user = $request->user();

        //Check if the product is already in the user's cart
        $cartItem = CartDetail::where('user_id', $user->user_id)
            ->where('product_id', $validateData['product_id'])
            ->first();

        if ($cartItem) {
            // Update the quantity if item already exists
            $cartItem->update([
                'quantity' => $cartItem->quantity + $validateData['quantity']
            ]);
        } else {
            // Create a new cart item
            $cartItem = CartDetail::create([
                'user_id' => $user->user_id,
                'product_id' => $validateData['product_id'],
                'quantity' => $validateData['quantity']
            ]);
        }

        return response()->json([
            'message' => 'Add item to cart successfully!',
            'carts' => $cartItem
        ], 200);
    }

    public function updateItemCart(Request $request, CartDetail $cartItem)
    {
        try {
            // Get the authenticated user
            $user = $request->user();

            // Ensure this cart item belong to the authenticated user
            if ($cartItem->user_id !== $user->user_id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Validate the new quantity
            $validateData = $request->validate([
                'quantity' => 'required|integer|min:0'
            ]);

            // Calculate the new total quantity by adding the requested quantity
            $newQuantity = $cartItem->quantity + $validateData['quantity'];

            // If quantity is 0, remove the item from the cart
            if ($validateData['quantity'] == 0) {
                $cartItem->delete();
                return response()->json([
                    'message' => 'Cart item removed successfully',
                    'cartItem' => $cartItem
                ], 200);
            }

            // Otherwise, update the quantity
            $cartItem->update([
                'quantity' => $newQuantity
            ]);

            // Return a success response
            return response()->json([
                'message' => 'Cart item updated successfully',
                'cart_item' => $cartItem
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }
    }

    public function deleteItemCart(Request $request, CartDetail $cartItem)
    {
        try {

            // Get authenticated user
            $user = $request->user();

            // Verify that the cart item belongs to the authenticated user
            if ($cartItem->user_id !== $user->user_id) {
                return response()->json(['message' => 'Unauthenticated'], 403);
            }

            // Delete the cart item 
            $cartItem->delete();

            // Return a success response
            return response()->json([
                'message' => 'Cart item deleted successfully',
                'cart_item' => $cartItem
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }
    }
}
