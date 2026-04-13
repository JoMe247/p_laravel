<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agency')) {
            Schema::create('agency', function (Blueprint $table) {
                $table->increments('id_a');
                $table->string('agency_code', 20);
                $table->string('account_type', 2)->default('P1');
                $table->integer('message_counter')->default(0);
                $table->integer('doc_counter')->default(0);
                $table->string('office_phone', 13)->nullable();
                $table->string('agency_address', 260)->nullable();
                $table->string('agency_name', 100);
                $table->string('agency_email', 100)->unique();
                $table->string('agency_logo')->nullable();
                $table->string('invoice_footer_image')->nullable();
                $table->boolean('invoice_footer_enabled')->default(false);
                $table->string('estimate_footer_image')->nullable();
                $table->boolean('estimate_footer_enabled')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agency');
    }
};
