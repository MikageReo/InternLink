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
        // First, migrate any existing studentQuota values to supervisor_quota
        // Only update if supervisor_quota is 0 and studentQuota has a value
        DB::statement('UPDATE lecturers SET supervisor_quota = studentQuota WHERE supervisor_quota = 0 AND studentQuota > 0');

        // Then drop the studentQuota column
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('studentQuota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the studentQuota column
        Schema::table('lecturers', function (Blueprint $table) {
            $table->integer('studentQuota')->default(0)->after('department');
        });

        // Copy values back from supervisor_quota
        DB::statement('UPDATE lecturers SET studentQuota = supervisor_quota');
    }
};
