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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('phone',10)->unique()->nullable();
            $table->string('username', 50)->nullable();
            $table->string('email',100)->unique();
            // $table->timestamp('email_verified_at')->nullable();
            $table->string('password',255);
            $table->integer('point')->default(0);
            $table->date('birthday')->nullable();
            $table->boolean('gender')->nullable();
            $table->enum('role', ['customer', 'admin'])->default('customer');
            $table->string('image', 255)->nullable();
            $table->integer('status')->default(1);
            // $table->rememberToken();
            $table->timestamps();
        });

        // Schema::create('password_reset_tokens', function (Blueprint $table) {
        //     $table->string('email')->primary();
        //     $table->string('token');
        //     $table->timestamp('created_at')->nullable();
        // });

        // Schema::create('sessions', function (Blueprint $table) {
        //     $table->string('id')->primary();
        //     $table->foreignId('user_id')->nullable()->index();
        //     $table->string('ip_address', 45)->nullable();
        //     $table->text('user_agent')->nullable();
        //     $table->longText('payload');
        //     $table->integer('last_activity')->index();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
