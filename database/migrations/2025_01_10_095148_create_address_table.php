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
        Schema::create('address', function (Blueprint $table) {
            $table->integer('address_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->string('user_name');
            $table->string('phone', 10);
            $table->string('ward', 255);
            $table->string('district', 255);
            $table->string('province', 255);
            $table->string('type', 50);
            $table->integer('is_default', 1);
            $table->string('specific_address', 255);
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
