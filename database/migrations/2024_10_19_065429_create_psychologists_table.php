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
        Schema::create('psychologists', function (Blueprint $table) {
            $table->id();
            $table->id('user_id');
            $table->string('degree');
            $table->string('major');
            $table->string('university');
            $table->year('graduation_year');
            $table->json('language');
            $table->json('certification');
            $table->json('specialization');
            $table->text('work_experience');
            $table->string('profesional_identification_number');
            $table->json('cv');
            $table->json('practice_license');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->foreignUuid('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychologists');
    }
};
