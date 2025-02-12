<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';
    protected $primaryKey = 'unit_id';
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
