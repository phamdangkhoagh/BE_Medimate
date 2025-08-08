<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Coupon;
use App\Traits\GenerateCouponCode;
use Carbon\Carbon;

class PromotionController extends Controller
{
    use GenerateCouponCode;
    function getUserPromotion(Request $request, $userId)
    {
        try {
            // get authentication
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Belong to reddemed_coupons filter coupon from it
            $coupon = DB::table('redeemed_coupons as rede')
                ->join('users as u', 'rede.user_id', '=', 'u.user_id')
                ->where('rede.user_id', $userId)
                ->select('rede.*', 'u.username', 'u.email', 'u.point')
                ->get();

            return response()->json([
                'message' => 'Successful when take coupon for user!',
                'coupons' => $coupon
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed when take coupon for user!',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    function redeemCouponWithPoints(Request $request, $userId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $code = $this->generateUniqueCouponCode();
        
        $validateData = $request->validate([
            'coupon_id' => 'required|exists:coupon,coupon_id'
        ]);

        $coupon = Coupon::find($validateData['coupon_id']);

        // check if user has enough points
        if ($user->point < $coupon->points) {
            return response()->json([
                'message' => 'Not enough points to redeem this coupon.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Deduct user points
            $user->point -= $coupon->points;
            $user->save();

            DB::table('redeemed_coupons')->insert([
                'user_id' => $userId,
                'coupon_id' => $coupon->coupon_id,
                'code' => $code,
                'expired_date' => Carbon::now()->addDays(7)->endOfday(),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Coupon redeemed successfully!',
                'coupon' => $coupon->only(['coupon_id', 'description']),
                'remaining_points' => $user->point
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Redemption failed. Try Again!',
                'detail' => $e->getMessage()
            ], 500);
        }
    }
}
