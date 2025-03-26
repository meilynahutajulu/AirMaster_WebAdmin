<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('google_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('attribute')->nullable();
            $table->string('loa_no')->nullable();
            $table->string('hub');
            $table->string('status');
            $table->string('photo_url')->nullable();
            $table->timestamp('license_expiry')->nullable();
            $table->string('license_no')->nullable();
            $table->string('rank');
            $table->json('instructor')->nullable();
            $table->json('privileges')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};