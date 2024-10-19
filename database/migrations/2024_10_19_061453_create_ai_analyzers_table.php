<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_analyzers', function (Blueprint $table) {
            $table->id();
            $table->id('patient_id');
            $table->float('stress');
            $table->float('anxiety');
            $table->float('depression');
            $table->timestamps();
            $table->foreignUuid('patient_id')->references('id')->on('patients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyzers');
    }
};
