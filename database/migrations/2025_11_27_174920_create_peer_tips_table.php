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
        Schema::create('peer_tips', function (Blueprint $table) {
            $table->id();
            $table->string('nickname'); // Anonymous nickname
            $table->text('tip_content'); // The tip shared by the user
            $table->string('studentID')->nullable(); // Optional: for tracking but can be anonymous
            $table->timestamps();

            // Index for faster queries
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peer_tips');
    }
};
