<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_shifts')) {
            Schema::create('schedule_shifts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('agency', 30);
                $table->enum('assign_type', ['any', 'user', 'sub'])->default('any')->nullable();
                $table->unsignedBigInteger('assign_id')->nullable();
                $table->string('color', 30)->nullable();
                $table->boolean('is_time_off')->default(false);
                $table->string('time_off_type', 50)->nullable();
                $table->string('time_text', 40)->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('created_by_sub_user_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('agency', 'idx_agency');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_shifts');
    }
};
