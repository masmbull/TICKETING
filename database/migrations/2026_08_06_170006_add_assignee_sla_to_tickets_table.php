<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('assignee_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('assignee_id')->constrained()->nullOnDelete();
            $table->timestamp('first_response_at')->nullable()->after('updated_at');
            $table->timestamp('resolved_at')->nullable()->after('first_response_at');
            $table->timestamp('closed_at')->nullable()->after('resolved_at');
            $table->string('sla_priority')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assignee_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'assignee_id', 'department_id', 'first_response_at',
                'resolved_at', 'closed_at', 'sla_priority'
            ]);
        });
    }
};