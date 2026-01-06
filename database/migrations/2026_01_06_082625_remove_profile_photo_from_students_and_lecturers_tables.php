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
        // Remove profilePhoto from students table
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'profilePhoto')) {
                $table->dropColumn('profilePhoto');
            }
        });

        // Remove profile_photo from lecturers table
        Schema::table('lecturers', function (Blueprint $table) {
            if (Schema::hasColumn('lecturers', 'profile_photo')) {
                $table->dropColumn('profile_photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore profilePhoto to students table
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'profilePhoto')) {
                $table->string('profilePhoto')->nullable()->after('internship_end_date');
            }
        });

        // Restore profile_photo to lecturers table
        Schema::table('lecturers', function (Blueprint $table) {
            if (!Schema::hasColumn('lecturers', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('country');
            }
        });
    }
};
