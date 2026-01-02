<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the column to allow the new enum values
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('Active', 'Deferred', 'Barred', 'Terminate', 'In-Active', 'Pass Away', 'Graduated') DEFAULT 'Active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('active', 'inactive', 'graduated', 'suspended') DEFAULT 'active'");
    }
};
