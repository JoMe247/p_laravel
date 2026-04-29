<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'doc_config';

    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('sms_monthly_counters')) {
            Schema::connection($this->connection)->create('sms_monthly_counters', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('agency_code', 20);
                $table->string('twilio_number', 30);
                $table->unsignedTinyInteger('mes');
                $table->unsignedSmallInteger('anio');
                $table->unsignedInteger('cantidad')->default(0);
                $table->timestamps();

                $table->unique(['agency_code', 'mes', 'anio'], 'uq_agency_month_year');
                $table->index('twilio_number', 'idx_twilio_number');
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('sms_monthly_counters');
    }
};
