<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->string('agency', 20);
                $table->string('subject');
                $table->date('start_date');
                $table->date('due_date');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['Open', 'In Progress', 'Closed'])->nullable()->default('Open');
                $table->unsignedInteger('assigned_user_id')->nullable();
                $table->enum('assigned_user_type', ['user', 'sub_user']);
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->enum('created_by_type', ['user', 'sub_user']);
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
