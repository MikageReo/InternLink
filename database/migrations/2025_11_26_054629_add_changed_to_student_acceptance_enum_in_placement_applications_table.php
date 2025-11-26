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
        // Modify the enum to include 'Changed'
        DB::statement("ALTER TABLE placement_applications MODIFY COLUMN studentAcceptance ENUM('Pending', 'Accepted', 'Declined', 'Changed') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        // First, update any 'Changed' values to 'Accepted' or NULL
        DB::statement("UPDATE placement_applications SET studentAcceptance = NULL WHERE studentAcceptance = 'Changed'");

        // Then modify the enum back
        DB::statement("ALTER TABLE placement_applications MODIFY COLUMN studentAcceptance ENUM('Pending', 'Accepted', 'Declined') NULL");
    }
};
