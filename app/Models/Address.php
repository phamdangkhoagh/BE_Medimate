<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';
    protected $primaryKey = 'address_id';
    protected $fillable = [
        'user_id',
        'user_name',
        'phone',
        'ward',
        'district',
        'province',
        'type',
        'is_default',
        'specific_address',
        'status'
    ];
    public $timestamps = true;
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }
}
