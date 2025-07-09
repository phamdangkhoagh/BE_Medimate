<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\RedeemedCoupon;
use App\Traits\GenerateOrderCode;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use GenerateOrderCode;

    public function getAllOrders(Request $request)
    {
        try {
            // get authenticated user
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // With role customer only show order belong to customer 
            // With role admin show all

            $orders = Order::when($user->role === 'customer', function ($query) use ($user) {
                return $query->where('user_id', $user->user_id);
            })->when($user->role === 'admin', function ($query) {
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

            $code = $this->generateUniqueOrderCode();

            $order = Order::create([
                'user_id' => $user->user_id,
                'code' => $code,
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

            $validatedData = $request->validate([
                'redeemed_coupon_id' => 'nullable|exists:redeemed_coupons,redeemed_coupon_id',
                'payment_method' => 'nullable|in:credit_card,COD,banking',
                'total_coupon_discount' => 'nullable|numeric|min:0',
                'total_product_discount' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:1000',
                'total' => 'nullable|integer',
                'point' => 'nullable|integer|min:0',
                'user_address' => 'nullable|string|max:500',
                'items' => 'required|array', // Array of products
                'items.*.product_id' => 'required|exists:products,product_id',
                'items.*.product_price' => 'required|numeric|min:0',
                'items.*.discount_percent' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Check coupon
            $coupon = $this->checkCouponIfExist($validatedData);

            DB::beginTransaction();
            
            // Update the order with provided data
            $order->update([
                'redeemed_coupon_id' => $validatedData['redeemed_coupon_id'],
                'payment_method' => $validatedData['payment_method'],
                'total_coupon_discount' => $this->calTotalCouponDiscount($validatedData) ?? 0,
                'total_product_discount' => $this->calTotalProductDiscount($validatedData) ?? 0,
                'note' => $validatedData['note'] ?? $order->note,
                'point' => $this->calPoint($validatedData) ?? $order->point,
                'total' => $this->calAllCost($validatedData) ?? $order->total,
                'user_address' => $validatedData['user_address'] ?? $order->user_address,
            ]);

            //Update or insert order details if items are provided
            if (!empty($validatedData['items'])) {
                OrderDetail::where('order_id', $order->order_id)->delete();

                $orderDetails = [];
                foreach ($validatedData['items'] as $item) {
                    $orderDetails[] = [
                        'order_id' => $order->order_id,
                        'product_id' => $item['product_id'],
                        'product_price' => $item['product_price'],
                        'discount_price' => (($item['discount_percent']*$item['product_price']*$item['quantity'])/100),
                        'quantity' => $item['quantity'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                OrderDetail::insert($orderDetails);
            }

            DB::commit();

            // Reload order with updated order details
            $order->load('orderDetails');

            return response()->json([
                'message' => 'Order updated successfully!',
                'order' => $order
            ]);
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

    public function calTotalProductDiscount($validatedData)
    {
        $total = 0;
        foreach ($validatedData['items'] as $item) {
            $price = $item['product_price'];
            $dicountPercent = $item['discount_percent'] ?? 0;
            $discountAmount = ($price * $dicountPercent) / 100;
            $total += $discountAmount * $item['quantity'];
        }
        return $total;
    }

    public function checkCouponIfExist($validatedData)
    {
        if (!empty($validatedData['redeemed_coupon_id'])) {
            $redeemedCoupon = RedeemedCoupon::with('coupon')
                ->where('redeemed_coupon_id', $validatedData['redeemed_coupon_id'])
                ->where('user_id', auth()->id())
                ->first();

            if (!$redeemedCoupon) {
                return response()->json(['error' => 'Invalid or unauthorized coupon.'], 400);
            }

            $coupon = $redeemedCoupon->coupon;

            if ($coupon->status == 0) {
                return response()->json(['error' => 'Coupon is not active'], 400);
            }

            // Check expiration
            $createdAt = $coupon->created_at;
            $usageDays = $coupon->usage_days;

            if ($createdAt->addDays($usageDays)->isPast()) {
                return response()->json(['error' => 'Coupon has expired.'], 400);
            }

            // Valid coupon
            return $coupon->discount;
        }

        return null; // No coupon applied
    }

    public function calTotalCouponDiscount($validatedData)
    {
        if ($validatedData['redeemed_coupon_id']) {
            $total = 0;
            foreach ($validatedData['items'] as $item) {
                $price = $item['product_price'];
                $dicountPercent = $this->checkCouponIfExist($validatedData) ?? 0;
                $discountAmount = ($price * $dicountPercent) / 100;
                $total += $discountAmount * $item['quantity'];
            }
            return $total;
        }

        return 0;
    }

    public function calAllCost($validatedData)
    {
        $total = 0;
        foreach($validatedData['items'] as $item){
            $totalPrice = $item['product_price']*$item['quantity'];
            $total += $totalPrice - (($this->calTotalCouponDiscount($validatedData))+ ($this->calTotalProductDiscount($validatedData)));
        }

        return $total;
    }
    
    public function calPoint ($validatedData)
    {
        $total = $this->calAllCost($validatedData);
        $point = ($total / 1000);
        return $point;
    }
}
