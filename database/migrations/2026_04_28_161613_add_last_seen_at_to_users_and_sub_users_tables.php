<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_seen_at')->nullable();
            });
        }

        if (!Schema::hasColumn('sub_users', 'last_seen_at')) {
            Schema::table('sub_users', function (Blueprint $table) {
                $table->timestamp('last_seen_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_seen_at');
            });
        }

        if (Schema::hasColumn('sub_users', 'last_seen_at')) {
            Schema::table('sub_users', function (Blueprint $table) {
                $table->dropColumn('last_seen_at');
            });
        }
    }
};