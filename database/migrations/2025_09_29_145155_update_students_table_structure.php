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
        Schema::table('students', function (Blueprint $table) {
            // First, add new address columns
            $table->string('city')->nullable()->after('address');
            $table->string('postcode')->nullable()->after('city');
            $table->string('state')->nullable()->after('postcode'); // renamed to avoid conflict with existing 'state' if any
            $table->string('country')->nullable()->after('state');

            // Update status enum with new values
            $table->dropColumn('status');
        });

        // Add the new status column with updated enum values
        Schema::table('students', function (Blueprint $table) {
            $table->enum('status', [
                'active',
                'deferred',
                'barred',
                'terminate',
                'in-active',
                'hold',
                'passed away',
                'completed'
            ])->default('active')->after('country');
        });

        // Remove the resume column
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('resume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Restore the resume column
            $table->string('resume')->nullable()->after('profilePhoto');

            // Remove the new address columns
            $table->dropColumn(['city', 'postcode', 'state', 'country']);

            // Drop the new status column
            $table->dropColumn('status');
        });

        // Restore the original status enum
        Schema::table('students', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'graduated', 'suspended'])->default('active')->after('resume');
        });
    }
};
