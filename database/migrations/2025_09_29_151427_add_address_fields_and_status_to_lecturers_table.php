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
        Schema::table('lecturers', function (Blueprint $table) {
            // Check if the table has an 'address' column to position after
            // If not, we'll add after 'state' column which should exist
            $afterColumn = Schema::hasColumn('lecturers', 'address') ? 'address' : 'state';

            // Add address fields (only if they don't already exist)
            if (!Schema::hasColumn('lecturers', 'city')) {
                $table->string('city')->nullable()->after($afterColumn);
            }
            if (!Schema::hasColumn('lecturers', 'postcode')) {
                $table->string('postcode')->nullable()->after('city');
            }
            if (!Schema::hasColumn('lecturers', 'country')) {
                $table->string('country')->nullable()->after('postcode');
            }

            // Add profile photo (only if it doesn't already exist)
            if (!Schema::hasColumn('lecturers', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('country');
            }

            // Add status (only if it doesn't already exist)
            if (!Schema::hasColumn('lecturers', 'status')) {
                $table->enum('status', [
                    'active',
                    'sabbatical leave',
                    'maternity leave',
                    'pilgrimage leave',
                    'transferred',
                    'resigned',
                    'in-active',
                    'passed away'
                ])->default('active')->after('profile_photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            // Remove the added columns
            $columnsToRemove = [];

            if (Schema::hasColumn('lecturers', 'city')) {
                $columnsToRemove[] = 'city';
            }
            if (Schema::hasColumn('lecturers', 'postcode')) {
                $columnsToRemove[] = 'postcode';
            }
            if (Schema::hasColumn('lecturers', 'country')) {
                $columnsToRemove[] = 'country';
            }
            if (Schema::hasColumn('lecturers', 'profile_photo')) {
                $columnsToRemove[] = 'profile_photo';
            }
            if (Schema::hasColumn('lecturers', 'status')) {
                $columnsToRemove[] = 'status';
            }

            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
