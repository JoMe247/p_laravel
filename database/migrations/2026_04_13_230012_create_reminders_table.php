<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reminders')) {
            Schema::create('reminders', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('agency', 30);
                $table->unsignedBigInteger('customer_id');
                $table->dateTime('remind_at');
                $table->enum('remind_to_type', ['user', 'sub']);
                $table->unsignedBigInteger('remind_to_id');
                $table->text('description');
                $table->boolean('send_email')->default(false);
                $table->dateTime('notified_at')->nullable();
                $table->enum('created_by_type', ['user', 'sub'])->nullable();
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

                $table->index('agency', 'idx_agency');
                $table->index('remind_at', 'idx_remind_at');
                $table->index(['remind_to_type', 'remind_to_id'], 'idx_remind_to');
                $table->index('customer_id', 'idx_customer');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
