<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('files_customers')) {
            Schema::create('files_customers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('customer_id');
                $table->string('file_name');
                $table->string('file_path', 500);
                $table->bigInteger('file_size');
                $table->string('file_type', 50)->nullable();
                $table->unsignedBigInteger('uploaded_by_id');
                $table->enum('uploaded_by_type', ['user', 'sub_user']);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();

                $table->index('customer_id');
                $table->index('uploaded_by_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('files_customers');
    }
};
