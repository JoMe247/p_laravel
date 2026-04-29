<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sub_user_tokens')) {
            Schema::create('sub_user_tokens', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('sub_user_id');
                $table->string('token')->unique();
                $table->dateTime('expires_at');

                $table->index('sub_user_id', 'fk_sub_user_tokens_sub_user_id');
                $table->foreign('sub_user_id', 'fk_sub_user_tokens_sub_user_id')
                    ->references('id')->on('sub_users')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_user_tokens');
    }
};
