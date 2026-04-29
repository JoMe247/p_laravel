<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('policies')) {
            Schema::create('policies', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('customer_id');
                $table->string('pol_carrier', 150)->nullable();
                $table->string('pol_number', 150)->nullable();
                $table->string('pol_url')->nullable();
                $table->date('pol_expiration')->nullable();
                $table->string('last_payment', 50)->nullable();
                $table->date('pol_eff_date')->nullable();
                $table->date('pol_added_date')->nullable();
                $table->string('pol_due_day', 50)->nullable();
                $table->string('pol_status', 50)->nullable();
                $table->string('pol_agent_record', 150)->nullable();
                $table->json('vehicules')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
