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
        Schema::create('request_justifications', function (Blueprint $table) {
            $table->id('justificationID');
            $table->unsignedBigInteger('applicationID');
            $table->text('reason');
            $table->date('requestDate');
            $table->string('committeeID')->nullable();
            $table->string('coordinatorID')->nullable();
            $table->enum('committeeStatus', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('coordinatorStatus', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('remarks')->nullable();
            $table->date('decisionDate')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('applicationID')->references('applicationID')->on('placement_applications')->onDelete('cascade');
            $table->foreign('committeeID')->references('lecturerID')->on('lecturers')->onDelete('set null');
            $table->foreign('coordinatorID')->references('lecturerID')->on('lecturers')->onDelete('set null');

            // Indexes for better performance
            $table->index(['applicationID', 'requestDate']);
            $table->index(['committeeStatus', 'coordinatorStatus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_justifications');
    }
};
