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
        Schema::table('lecturers', function (Blueprint $table) {
            // Drop preferred_coursework column
            $table->dropColumn('preferred_coursework');

            // Add program column with enum values
            $table->enum('program', [
                'BCS',
                'BCN',
                'BCM',
                'BCY',
                'DRC'
            ])->nullable()->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            // Drop program column
            $table->dropColumn('program');

            // Restore preferred_coursework column
            $table->string('preferred_coursework')->nullable()->after('department');
        });
    }
};
