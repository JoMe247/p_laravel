<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('company')) {
            Schema::create('company', function (Blueprint $table) {
                $table->increments('id');
                $table->string('company_name');
                $table->string('user_name');
                $table->string('phone_number', 10)->nullable();
                $table->string('password');
                $table->text('description')->nullable();
                $table->string('picture')->nullable();
                $table->string('url');
                $table->string('type', 30);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
