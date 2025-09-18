<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTable extends Migration
{
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->string('sid', 64)->unique();
            $table->string('from', 64);
            $table->string('to', 64);
            $table->text('body')->nullable();
            $table->string('direction', 32)->nullable(); // inbound / outbound-api / etc
            $table->string('status', 32)->nullable();
            $table->integer('num_media')->default(0);
            $table->json('media_urls')->nullable();
            $table->timestamp('date_sent')->nullable();
            $table->timestamp('date_created')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms');
    }
}
