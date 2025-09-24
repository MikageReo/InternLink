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
        Schema::create('course_verifications', function (Blueprint $table) {
            $table->id('courseVerificationID');
            $table->integer('currentCredit');
            $table->string('submittedFile');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->date('applicationDate');
            $table->string('lecturerID');
            $table->string('studentID');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('lecturerID')->references('lecturerID')->on('lecturers');
            $table->foreign('studentID')->references('studentID')->on('students');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_verifications');
    }
};
