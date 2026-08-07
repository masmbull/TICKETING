<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('default_priority')->default('medium')->after('description');
            $table->foreignId('sla_policy_id')->nullable()->after('default_priority')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['sla_policy_id']);
            $table->dropColumn(['default_priority', 'sla_policy_id']);
        });
    }
};