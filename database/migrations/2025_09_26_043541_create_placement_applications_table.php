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
        Schema::create('placement_applications', function (Blueprint $table) {
            $table->id('applicationID');
            $table->string('companyName');
            $table->text('companyAddress');
            $table->string('companyEmail');
            $table->string('companyNumber');
            $table->decimal('allowance', 10, 2)->nullable();
            $table->string('position');
            $table->text('jobscope');
            $table->enum('methodOfWork', ['WFO', 'WOS', 'WOC', 'WFH', 'WFO & WFH']);
            $table->date('startDate');
            $table->date('endDate');
            $table->date('applicationDate');
            $table->text('remarks')->nullable();
            $table->string('studentID');
            $table->string('committeeID')->nullable();
            $table->string('coordinatorID')->nullable();
            $table->enum('committeeStatus', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('coordinatorStatus', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('studentAcceptance', ['Pending', 'Accepted', 'Declined'])->nullable();
            $table->integer('applyCount')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('committeeID')->references('lecturerID')->on('lecturers')->onDelete('set null');
            $table->foreign('coordinatorID')->references('lecturerID')->on('lecturers')->onDelete('set null');

            // Indexes for better performance
            $table->index(['studentID', 'applicationDate']);
            $table->index(['committeeStatus', 'coordinatorStatus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placement_applications');
    }
};
