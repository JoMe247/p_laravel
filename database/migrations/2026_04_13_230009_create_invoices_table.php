<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('agency', 50);
                $table->string('customer_id', 50);
                $table->string('created_by_name', 150)->nullable();
                $table->string('policy_number', 60)->nullable();
                $table->string('invoice_number', 20)->nullable();
                $table->string('creation_date', 30)->nullable();
                $table->string('next_py_date', 30)->nullable();
                $table->string('payment_date', 30)->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->longText('inv_prices')->nullable();
                $table->string('fee', 50)->nullable();
                $table->string('fee_split', 10)->nullable();
                $table->string('fee_payment1_method', 30)->nullable();
                $table->string('fee_payment1_value', 50)->nullable();
                $table->string('fee_payment2_method', 30)->nullable();
                $table->string('fee_payment2_value', 50)->nullable();
                $table->string('premium', 50)->nullable();
                $table->string('premium_split', 10)->nullable();
                $table->string('premium_payment1_method', 30)->nullable();
                $table->string('premium_payment1_value', 50)->nullable();
                $table->string('premium_payment2_method', 30)->nullable();
                $table->string('premium_payment2_value', 50)->nullable();
                $table->string('created_at', 30)->nullable();
                $table->string('updated_at', 30)->nullable();

                $table->index(['agency', 'customer_id'], 'idx_invoices_agency_customer');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
