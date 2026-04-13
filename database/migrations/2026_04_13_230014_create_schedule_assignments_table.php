<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_assignments')) {
            Schema::create('schedule_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('agency', 30);
                $table->date('shift_date');
                $table->enum('target_type', ['user', 'sub']);
                $table->unsignedBigInteger('target_id');
                $table->unsignedBigInteger('shift_id');
                $table->unsignedBigInteger('assigned_by_user_id')->nullable();
                $table->unsignedBigInteger('assigned_by_sub_user_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['agency', 'shift_date', 'target_type', 'target_id'], 'uniq_agency_day_target');
                $table->index('shift_id', 'fk_schedule_shift');
                $table->index(['agency', 'shift_date'], 'idx_agency_date');
                $table->index(['target_type', 'target_id'], 'idx_target');
                $table->foreign('shift_id', 'fk_schedule_shift')
                    ->references('id')->on('schedule_shifts')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_assignments');
    }
};
