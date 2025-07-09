<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserActivityController extends Controller
{
    public function getPoint(Request $request){
        
        
        return response()->json([
            'message' => 'get point from order of user!!!'
        ]);
    }
}
