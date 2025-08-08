<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GenerateCouponCode
{
    public function generateUniqueCouponCode(): string
    {
        $prefix = "PRM";
        $timestamp = now() ->format('ymdHis');
        $random = strtoupper(Str::random(6));

        return $prefix . $timestamp . $random;
    }
}