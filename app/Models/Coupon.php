<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupon';
    protected $primaryKey = 'coupon_id';
    protected $fillable = [
        'description',
        'points',
        'discount',
        'usage_days',
        'image',
        'status',
    ];
    public $timestamps = true;
}
