<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('url')) {
            Schema::create('url', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->integer('type');
                $table->string('created_by', 60);
                $table->string('signed_by', 60);
                $table->string('short_url', 80)->unique('url_short_url_unique');
                $table->string('original_url', 260)->default('');
                $table->integer('clicks')->default(0);
                $table->string('signed', 3)->default('No');
                $table->date('date');
                $table->time('time');
                $table->integer('rand');
                $table->string('hash', 32)->default('');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('url');
    }
};
