<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 3.1 ticket workflow:
     * - Drop the legacy subject column (the ticket number is the identifier).
     * - Add Problem Analysis and Resolution fields required before a ticket
     *   can be Completed.
     * - Track completion time and the user who completed the ticket.
     * - Default new tickets to "Waiting Confirmation" instead of "open".
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('subject');

            $table->text('problem_analysis')->nullable()->after('description');
            $table->text('resolution')->nullable()->after('problem_analysis');
            $table->timestamp('completed_at')->nullable()->after('resolved_at');
            $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();

            $table->string('status')->default('Waiting Confirmation')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('ticket_number');

            $table->dropForeign(['completed_by']);
            $table->dropColumn(['problem_analysis', 'resolution', 'completed_at', 'completed_by']);

            $table->string('status')->default('open')->change();
        });
    }
};
