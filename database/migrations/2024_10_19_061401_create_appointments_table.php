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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->id('patient_id');
            $table->id('psychologist_id');
            $table->date('date');
            $table->time('time');
            $table->enum('status', ['waiting', 'ongoing', 'completed', 'canceled'])->default('waiting');
            $table->timestamps();
            $table->foreignUuid('patient_id')->references('id')->on('patients');
            $table->foreignUuid('psychologist_id')->references('id')->on('psychologists');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
