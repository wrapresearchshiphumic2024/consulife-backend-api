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
        Schema::table('days', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('times', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('days', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active');
        });

        Schema::table('times', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active');
        });
    }
};
