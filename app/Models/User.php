<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
class User extends Model
{
    use HasApiTokens, Notifiable;
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
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
