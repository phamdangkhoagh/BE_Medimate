<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    protected $primaryKey = 'order_detail_id';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $fillable = [
        'order_id',
        'product_id',
        'product_price',
        'discount_price',
        'quantity',
    ];
    public $timestamps = true;
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'product_id');
    }
    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id', 'order_id');
    }
    
}
