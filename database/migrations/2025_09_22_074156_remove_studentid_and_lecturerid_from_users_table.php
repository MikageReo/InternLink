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
            if (Schema::hasColumn('users', 'studentID')) {
                $table->dropForeign(['studentID']);
                $table->dropColumn('studentID');
            }

            if (Schema::hasColumn('users', 'lecturerID')) {
                $table->dropForeign(['lecturerID']);
                $table->dropColumn('lecturerID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('studentID')->nullable();
            $table->string('lecturerID')->nullable();

            // Restore FK (if rollback)
            $table->foreign('studentID')
                ->references('studentID')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('lecturerID')
                ->references('lecturerID')
                ->on('lecturers')
                ->onDelete('cascade');
        });
    }
};
