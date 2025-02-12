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
        Schema::create('redeemed_coupons', function (Blueprint $table) {
            $table->id('redeemed_coupon_id');
            $table->foreignId('coupon_id')->constrained('coupon','coupon_id');
            $table->foreignId('user_id')->constrained('users','user_id');
            $table->string('code',20);
            $table->dateTime('expired_date');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeemed_coupons');
    }
};
