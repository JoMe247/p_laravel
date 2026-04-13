<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->increments('ID');
                $table->string('Name', 120)->nullable();
                $table->string('Phone', 12)->nullable();
                $table->string('Phone2', 12)->nullable();
                $table->string('Email1', 120)->nullable();
                $table->string('Email2', 120)->nullable();
                $table->string('Address', 240)->nullable();
                $table->string('City', 30)->nullable();
                $table->string('State', 30)->nullable();
                $table->string('ZIP_Code', 10)->nullable();
                $table->string('Drivers_License', 60)->nullable();
                $table->string('DL_State', 30)->nullable();
                $table->string('DOB', 12)->nullable();
                $table->string('Source', 30)->nullable();
                $table->string('Office', 40)->nullable();
                $table->string('Marital', 30)->nullable();
                $table->string('Gender', 30)->nullable();
                $table->string('CID', 60)->nullable();
                $table->date('Added')->nullable();
                $table->string('Agent_of_Record', 30)->nullable();
                $table->string('Picture')->nullable();
                $table->string('Alert', 300)->nullable();
                $table->string('Agency', 30)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
