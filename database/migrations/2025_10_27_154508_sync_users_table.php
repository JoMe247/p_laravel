<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username', 50)->unique();
                $table->string('email', 100)->unique();
                $table->string('password_hash', 255);
                $table->string('current_session_token', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->tinyInteger('email_verified')->default(0);
                $table->string('verification_token', 255)->nullable();
                $table->string('reset_token', 255)->nullable();
                $table->dateTime('reset_token_expires')->nullable();
                $table->enum('role', ['admin', 'user'])->default('user');
            });
        }
    }

    public function down(): void
    {
        // No la eliminamos para no perder datos
    }
};
