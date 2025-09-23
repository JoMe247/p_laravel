<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedColumnToSmsTable extends Migration
{
    public function up()
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->string('deleted', 3)->nullable()->default(null)->after('status');
        });
    }

    public function down()
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
    }
}
