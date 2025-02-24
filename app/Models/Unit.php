<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';
    protected $primaryKey = 'unit_id';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $fillable = [
        'unit_name',
        'description',
        'status',
    ];
    public $timestamps = true;
    public function products()
    {
        return $this->hasMany('App\Models\Product', 'unit_id', 'unit_id');
    }
    
}
