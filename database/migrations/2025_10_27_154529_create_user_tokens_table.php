<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_tokens')) {
            Schema::create('user_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id'); // coincide con users.id
                $table->string('token', 255)->unique();
                $table->dateTime('expires_at');
                $table->timestamps();

                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tokens');
    }
};
