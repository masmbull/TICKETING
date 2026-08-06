<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('sub_category_id')->nullable()->constrained()->nullOnDelete();
        $table->timestamps();
        
        $table->index(['user_id', 'status']);
        $table->index('ticket_number');
        $table->index(['category_id', 'sub_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};