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
        Schema::create('lecturers', function (Blueprint $table) {
            $table->string('lecturerID')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('staffGrade')->nullable();
            $table->string('role')->nullable();
            $table->string('position')->nullable();
            $table->string('state')->nullable();
            $table->string('researchGroup')->nullable();
            $table->string('department')->nullable();
            $table->integer('studentQuota')->default(0);
            $table->boolean('isAcademicAdvisor')->default(false);
            $table->boolean('isSupervisorFaculty')->default(false);
            $table->boolean('isCommittee')->default(false);
            $table->boolean('isCoordinator')->default(false);
            $table->boolean('isAdmin')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
