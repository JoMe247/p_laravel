<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('calendar_events')) {
            Schema::create('calendar_events', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('start_date');
                $table->dateTime('end_date')->nullable();
                $table->integer('notification_value')->nullable();
                $table->enum('notification_unit', ['minutes', 'hours'])->default('minutes');
                $table->string('color', 20)->nullable()->default('#3B82F6');
                $table->boolean('is_public')->nullable()->default(false);
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
