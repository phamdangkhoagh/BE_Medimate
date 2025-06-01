<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GenerateOrderCode
{
    public function generateUniqueOrderCode(): string
    {
        $prefix = "HDH";
        $timestamp = now() ->format('ymdHis');
        $random = strtoupper(Str::random(6));

        return $prefix . $timestamp . $random;
    }
}