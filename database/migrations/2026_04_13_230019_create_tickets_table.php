<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('agency', 20);
                $table->enum('created_by_type', ['user', 'sub_user']);
                $table->unsignedBigInteger('created_by_id');
                $table->string('subject');
                $table->enum('assigned_type', ['user', 'sub_user'])->nullable();
                $table->unsignedBigInteger('assigned_id')->nullable();
                $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Low');
                $table->enum('status', ['Open', 'In Progress', 'Answered', 'On Hold', 'Closed'])->default('Open');
                $table->text('description')->nullable();
                $table->date('date');
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
