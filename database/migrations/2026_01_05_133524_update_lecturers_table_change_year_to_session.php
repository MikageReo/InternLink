<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            // Add session field (will replace year)
            $table->string('session', 10)->nullable()->after('semester');
        });

        // Migrate existing year data to session format (convert YYYY to YY/YY+1 format)
        DB::statement("
            UPDATE lecturers
            SET session = CONCAT(
                SUBSTRING(CAST(year AS CHAR), 3, 2),
                '/',
                LPAD(CAST(SUBSTRING(CAST(year AS CHAR), 3, 2) AS UNSIGNED) + 1, 2, '0')
            )
            WHERE year IS NOT NULL
        ");

        // Drop the old year column
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            // Add back year column
            $table->integer('year')->nullable()->after('semester');
        });

        // Migrate session data back to year (extract first 2 digits and convert to YYYY)
        DB::statement("
            UPDATE lecturers
            SET year = CAST(CONCAT('20', SUBSTRING(session, 1, 2)) AS UNSIGNED)
            WHERE session IS NOT NULL AND session REGEXP '^[0-9]{2}/[0-9]{2}$'
        ");

        Schema::table('lecturers', function (Blueprint $table) {
            // Remove session field
            $table->dropColumn('session');
        });
    }
};
