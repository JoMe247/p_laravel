<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('signing')) {
            Schema::create('signing', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 260);
                $table->string('client', 260);
                $table->string('agent', 260);
                $table->string('type', 50);
                $table->string('path', 260);
                $table->date('date_1');
                $table->time('time_1');
                $table->date('date_2')->nullable();
                $table->time('time_2')->nullable();
                $table->string('city_client', 50);
                $table->string('country_client', 80);
                $table->string('ip_client', 80);
                $table->string('device_client', 120);
                $table->string('browser_client', 150);
                $table->string('os_client', 50);
                $table->string('dName_client', 260);
                $table->string('coordinates_client', 100);
                $table->string('city_agent', 50);
                $table->string('country_agent', 80);
                $table->string('ip_agent', 80);
                $table->string('device_agent', 120);
                $table->string('browser_agent', 150);
                $table->string('os_agent', 50);
                $table->string('dName_agent', 260);
                $table->string('coordinates_agent', 80);
                $table->string('last_seen', 30);
                $table->string('status', 30);
                $table->string('hash_id', 40)->unique('uk_signing_hash_id');
                $table->integer('opened');
                $table->string('client_region', 120);
                $table->string('agent_region', 120);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('signing');
    }
};
