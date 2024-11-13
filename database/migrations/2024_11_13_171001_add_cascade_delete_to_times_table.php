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
        Schema::table('times', function (Blueprint $table) {
            Schema::table('times', function (Blueprint $table) {
                $table->dropForeign(['day_id']);

                $table->foreign('day_id')
                    ->references('id')
                    ->on('days')
                    ->onDelete('cascade');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('times', function (Blueprint $table) {
            $table->dropForeign(['day_id']);

            $table->foreign('day_id')
                ->references('id')
                ->on('days');
        });
    }
};
