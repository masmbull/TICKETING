<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('description', 500)->nullable()->after('new_values');
            $table->string('target', 255)->nullable()->after('description');

            $table->index(['target']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['target']);
            $table->dropColumn(['description', 'target']);
        });
    }
};
