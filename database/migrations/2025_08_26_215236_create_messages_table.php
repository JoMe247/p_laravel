<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();           // SID Twilio
            $table->string('from')->index();
            $table->string('to')->index();
            $table->text('body')->nullable();
            $table->enum('direction', ['inbound','outbound-api','outbound-call','outbound-reply'])->index();
            $table->string('status')->nullable()->index();
            $table->timestamp('date_sent')->nullable()->index();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('raw')->nullable();           // JSON crudo por si necesitas más campos
            $table->timestamps();

            $table->index(['to','from','date_sent']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('messages');
    }
};
