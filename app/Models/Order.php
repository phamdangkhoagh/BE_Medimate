<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $fillable = [
        'user_id',
        'code',
        'redeemed_coupon_id',
        'payment_method',
        'total_coupon_discount',
        'total_product_discount',
        'note',
        'point',
        'total',
        'user_address',
        'status',
    ];
    public $timestamps = true;
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
    public function orderDetails()
    {
        return $this->hasMany('App\Models\OrderDetail', 'order_id', 'order_id');
    }
    public function redeemedCoupon()
    {
        return $this->belongsTo('App\Models\Coupon', 'redeemed_coupon_id', 'coupon_id');
    }
    
}
