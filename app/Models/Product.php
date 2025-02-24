<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $fillable = [
        'category_id',
        'unit_id',
        'name',
        'description',
        'discount_percent',
        'price',
        'quantity',
        'image',
        'status',
    ];
    public $timestamps = true;
    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'category_id');
    }
    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'unit_id');
    }
}
