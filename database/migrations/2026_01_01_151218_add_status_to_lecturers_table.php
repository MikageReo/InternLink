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
        // Update the existing status column enum values
        DB::statement("ALTER TABLE lecturers MODIFY COLUMN status ENUM('Active', 'Sabatical Leave', 'Maternity Leave', 'Pligrimage Leave', 'Transfered', 'Resigned', 'In-Active', 'Medical Leave', 'Pass Away') DEFAULT 'Active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
