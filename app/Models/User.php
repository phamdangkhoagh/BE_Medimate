<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'phone',
        'username',
        'email',
        'password',
        'point',
        'birthday',
        'gender',
        'role',
        'image',
        'status'
    ];

    public $timestamps = true;

    protected $hidden = [
        'password',
    ];

    public function addresses()
    {
        return $this->hasMany('App\Models\Address', 'user_id', 'user_id');
    }
    public function redeemedCoupons()
    {
        return $this->hasMany('App\Models\RedeemedCoupon', 'user_id', 'user_id');
    }
    public function notifications()
    {
        return $this->hasMany('App\Models\Notification', 'user_id', 'user_id');
    }
    
}
