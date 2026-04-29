<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pdf_overlays')) {
            Schema::create('pdf_overlays', function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_id');
                $table->string('template_name');
                $table->string('original_file_path');
                $table->string('modified_file_path');
                $table->text('overlay_data');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_overlays');
    }
};
