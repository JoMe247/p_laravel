<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('type');
                $table->integer('template_id')->nullable();
                $table->string('policy_number', 40);
                $table->integer('id_customer');
                $table->string('insured_name', 250);
                $table->string('phone', 14);
                $table->string('email', 250);
                $table->string('user', 250);
                $table->date('date');
                $table->time('time');
                $table->string('path', 300);
                $table->longText('docsign_overlay')->nullable();
                $table->boolean('signed')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
