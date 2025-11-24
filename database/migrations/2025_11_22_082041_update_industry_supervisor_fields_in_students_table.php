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
        Schema::table('students', function (Blueprint $table) {
            // Drop the existing industrySupervisorName column
            // Industry supervisor info will be retrieved from placement_applications table instead
            $table->dropColumn('industrySupervisorName');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Restore the industrySupervisorName column if rolling back
            $table->string('industrySupervisorName')->nullable()->after('academicAdvisorID');
        });
    }
};
