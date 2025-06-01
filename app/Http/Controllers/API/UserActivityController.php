<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserActivityController extends Controller// yo bro it's so easy just do it now!!! you good just go on
{
    public function getPoint(Request $request){
        // hehe i'll get point for user when the order is success
        
        return response()->json([
            'message' => 'get point from order of user!!!'
        ]);
    }
}
