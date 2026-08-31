<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_insights', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('subject');
            $table->string('insight_type');
            $table->enum('severity', ['low', 'moderate', 'high', 'critical'])->default('low');
            $table->timestamp('scan_performed_on')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->string('scan_source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('severity');
            $table->index('status');
            $table->index('insight_type');
            $table->index('scan_performed_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_insights');
    }
};