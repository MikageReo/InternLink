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
        Schema::create('supervisor_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('studentID');
            $table->string('supervisorID'); // References lecturers.lecturerID
            $table->string('assignedBy'); // References lecturers.lecturerID (coordinator)
            $table->enum('status', ['assigned', 'completed', 'reassigned', 'cancelled'])->default('assigned');
            $table->text('assignment_notes')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable(); // Store calculated distance
            $table->boolean('quota_override')->default(false); // Flag for quota override
            $table->text('override_reason')->nullable(); // Reason for quota override
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('supervisorID')->references('lecturerID')->on('lecturers')->onDelete('cascade');
            $table->foreign('assignedBy')->references('lecturerID')->on('lecturers')->onDelete('cascade');

            // Index for student lookups
            $table->index(['studentID', 'status']);

            // Index for faster queries
            $table->index('supervisorID');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_assignments');
    }
};
