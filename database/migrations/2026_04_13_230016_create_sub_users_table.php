<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sub_users')) {
            Schema::create('sub_users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username', 50)->unique();
                $table->string('name', 100)->nullable()->comment('Nombre real del usuario');
                $table->string('email', 100)->unique();
                $table->string('password_hash');
                $table->string('current_session_token')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->tinyInteger('email_verified')->nullable()->default(0);
                $table->string('verification_token')->nullable();
                $table->string('reset_token')->nullable();
                $table->dateTime('reset_token_expires')->nullable();
                $table->enum('role', ['admin', 'user'])->nullable()->default('user');
                $table->string('remember_token', 100)->nullable();
                $table->string('agency', 10)->nullable();
                $table->string('twilio_number', 20)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_users');
    }
};
