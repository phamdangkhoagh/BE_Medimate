<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function getAllOrders(Request $request){
        try {
            // get authenticated user
            $user = $request->user();

            if(!$user){
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // With role customer only show order belong to customer 
            // With role admin show all

           $orders = Order::when($user->role === 'customer', function ($query) use ($user){
                return $query->where('user_id', $user->user_id);
           })->when($user->role === 'admin', function($query){
                return $query;
           })->with('orderDetails')->get();

           return response()->json([
            'message' => 'Orders retrieved successfully',
            'orders' => $orders
        ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error!',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            DB::rollBack(); // Rollback transaction
            return response()->json([
                'error' => 'Database Error!',
                'message' => $e->getMessage() // Return actual database error
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error!',
                'message' => $e->getMessage()
            ], 500);
        }
        
    }
    public function createOrder(OrderRequest $request)
    {
        try {
            // Get authenticated user
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Extract validated data
            $validatedData = $request->validated();

            // Start a transaction to ensure atomicity
            DB::beginTransaction();

            //create a new order
            $order = Order::create([
                'user_id' => $user->user_id,
                'code' => $validatedData['code'],
                'redeemed_coupon_id' => $validatedData['redeemed_coupon_id'],
                'payment_method' => $validatedData['payment_method'],
                'total_coupon_discount' => $validatedData['total_coupon_discount'],
                'total_product_discount' => $validatedData['total_product_discount'],
                'note' => $validatedData['note'],
                'point' => $validatedData['point'],
                'total' => $validatedData['total'],
                'user_address' => $validatedData['user_address'],
                'status' => $validatedData['status']
            ]);

            // Insert Order Details
            $orderDetails = [];
            foreach ($validatedData['items'] as $item) {
                $orderDetails[] = [
                    'order_id' => $order->order_id,
                    'product_id' => $item['product_id'],
                    'product_price' => $item['product_price'],
                    'discount_price' => $item['discount_price'],
                    'quantity' => $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Batch insert order details
            OrderDetail::insert($orderDetails);


            // Commit transaction after successful inserts
            DB::commit();

            // Reload order with its order details relationship
            $order->load('orderDetails');

            return response()->json([
                'message' => 'Order created successfully!',
                'order' => $order
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error!',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            DB::rollBack(); // Rollback transaction
            return response()->json([
                'error' => 'Database Error!',
                'message' => $e->getMessage() // Return actual database error
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error!',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request, $orderId)
    {
        try {

            // Get authenticated user
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            //Validate input
            $validatedData = $request->validate([
                'payment_method' => 'nullable|in:credit_card,COD,banking',
                'total_coupon_discount' => 'nullable|numeric|min:0',
                'total_product_discount' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:1000',
                'total' => 'nullable|integer',
                'point' => 'nullable|integer|min:0',
                'user_address' => 'nullable|string|max:500',
                'status' => 'required|in:pending,processing,delivered,refunded,canceled',
            ]);

            // Find the order
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Check user role
            if ($user->role === 'customer') {

                // Customers can only update their own orders
                if ($order->user_id !== $user->user_id) {
                    return response()->json([
                        'error' => 'Forbidden',
                        'message' => 'You can only update your own orders'
                    ], 403);
                }

                // Customers can only update status to 'canceled'
                if (!isset($validatedData['status']) || $validatedData['status'] !== 'canceled') {
                    return response()->json([
                        'error' => 'Forbidden',
                        'message' => 'Customers can only cancel their orders.'
                    ], 403);
                }

                // Allow customer to update only the status to 'canceled'
                $order->update(['status' => 'canceled']);

                return response()->json([
                    'message' => 'Order successfully canceled.',
                    'order' => $order
                ]);
            }

            // Admin can update any field for any customer
            if ($user->role === 'admin') {

                DB::beginTransaction();


                // Update the order with provided data
                $order->update([
                    'payment_method' => $validatedData['payment_method'] ?? $order->payment_method,
                    'total_coupon_discount' => $validatedData['total_coupon_discount'] ?? $order->total_coupon_discount,
                    'total_product_discount' => $validatedData['total_product_discount'] ?? $order->total_product_discount,
                    'note' => $validatedData['note'] ?? $order->note,
                    'point' => $validatedData['point'] ?? $order->point,
                    'total' => $validatedData['total'] ?? $order->total,
                    'user_address' => $validatedData['user_address'] ?? $order->user_address,
                    'status' => $validatedData['status'] ?? $order->status
                ]);

                //Update or insert order details if items are provided
                if (!empty($validatedData['items'])) {
                    // Delete existing order details
                    OrderDetail::where('order_id', $order->order_id)->delete();

                    // Insert new order details
                    $orderDetails = [];
                    foreach ($validatedData['items'] as $item) {
                        $orderDetails[] = [
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'product_price' => $item['product_price'],
                            'discount_price' => $item['discount_price'],
                            'quantity' => $item['quantity'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    OrderDetail::insert($orderDetails);
                }

                // Commit transaction
                DB::commit();

                // Reload order with updated order details
                $order->load('orderDetails');

                return response()->json([
                    'message' => 'Order updated successfully!',
                    'order' => $order
                ]);
            }

            //If role is neither "customer" nor "admin"
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You are not authorized to update orders',
            ], 403);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Validation Error!',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Database Error!',
                'message' => 'Something went wrong while updating the order!'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Server Error!',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
