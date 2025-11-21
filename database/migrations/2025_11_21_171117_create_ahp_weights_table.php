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
        Schema::create('ahp_weights', function (Blueprint $table) {
            $table->id();
            $table->json('criteria_comparisons'); // 4x4 pairwise comparison matrix
            $table->json('calculated_weights'); // Calculated weights for each criterion
            $table->decimal('consistency_ratio', 5, 4); // CR value (e.g., 0.0523)
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // User who created this weight set
            $table->timestamps();

            // Index for faster lookups
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahp_weights');
    }
};
