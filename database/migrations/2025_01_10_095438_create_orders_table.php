<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->integer('order_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');  
            $table->string('code',20);
            $table->foreignId('redeemed_coupon_id')->constrained('redeemed_coupons', 'redeemed_coupon_id');  
            $table->string('payment_method',20);
            $table->double('total_coupon_discount');
            $table->double('total_product_discount');
            $table->string('note');
            $table->integer('point');
            $table->double('total');
            $table->string('user_address',255);
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
