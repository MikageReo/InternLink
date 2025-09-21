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
        Schema::table('users', function (Blueprint $table) {
            $table->string('studentID')->nullable()->after('role');
            $table->string('lecturerID')->nullable()->after('studentID');

            // Add foreign key constraints
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('lecturerID')->references('lecturerID')->on('lecturers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['studentID']);
            $table->dropForeign(['lecturerID']);
            $table->dropColumn(['studentID', 'lecturerID']);
        });
    }
};
