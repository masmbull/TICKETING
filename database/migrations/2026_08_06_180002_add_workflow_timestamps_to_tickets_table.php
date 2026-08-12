<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 3.1: Add workflow timestamps for an accurate ticket timeline.
     * - assigned_at:           when the ticket was assigned to an IT Support member
     * - problem_analysis_at:   when the problem analysis was submitted
     * - resolution_at:         when the resolution was submitted
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('completed_by');
            $table->timestamp('problem_analysis_at')->nullable()->after('assigned_at');
            $table->timestamp('resolution_at')->nullable()->after('problem_analysis_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['assigned_at', 'problem_analysis_at', 'resolution_at']);
        });
    }
};
