<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers_notes')) {
            Schema::create('customers_notes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('customer_id');
                $table->string('agency', 30);
                $table->string('policy')->nullable();
                $table->string('subject')->nullable();
                $table->text('note');
                $table->string('created_by', 120);
                $table->string('creator_type', 20)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('customer_id', 'fk_customernote_customer');
                $table->foreign('customer_id', 'fk_customernote_customer')
                    ->references('ID')->on('customers')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers_notes');
    }
};
