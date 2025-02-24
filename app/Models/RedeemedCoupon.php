<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemedCoupon extends Model
{
    protected $table = 'redeemed_coupons';
    protected $primaryKey = 'redeemed_coupon_id';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
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
