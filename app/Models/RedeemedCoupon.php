<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemedCoupon extends Model
{
    protected $table = 'redeemed_coupons';
    protected $primaryKey = 'redeemed_coupon_id';
    protected $fillable = [
        'coupon_id',
        'user_id',
        'code',
        'expired_date',
        'status',
    ];
    public $timestamps = true;
    public function coupon()
    {
        return $this->belongsTo('App\Models\Coupon', 'coupon_id', 'coupon_id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
    
}
