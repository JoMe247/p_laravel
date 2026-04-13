<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'doc_config';

    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('limits')) {
            Schema::connection($this->connection)->create('limits', function (Blueprint $table) {
                $table->increments('id_lim');
                $table->string('account_type', 2);
                $table->integer('msg_limit');
                $table->integer('doc_limit');
                $table->integer('user_limit');
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('limits');
    }
};
