<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('priority'); // low, medium, high, critical
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'sub_category_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_mappings');
    }
};
