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
        Schema::table('course_verifications', function (Blueprint $table) {
            $table->enum('academicAdvisorStatus', ['pending', 'approved', 'rejected'])->nullable()->after('status');
            $table->string('academicAdvisorID')->nullable()->after('academicAdvisorStatus');
            
            // Foreign key constraint for academic advisor
            $table->foreign('academicAdvisorID')->references('lecturerID')->on('lecturers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_verifications', function (Blueprint $table) {
            $table->dropForeign(['academicAdvisorID']);
            $table->dropColumn(['academicAdvisorStatus', 'academicAdvisorID']);
        });
    }
};
