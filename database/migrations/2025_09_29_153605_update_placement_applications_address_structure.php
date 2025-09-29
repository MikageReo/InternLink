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
        Schema::table('placement_applications', function (Blueprint $table) {
            // Add structured company address fields after companyAddress
            $table->string('companyCity')->nullable()->after('companyAddress');
            $table->string('companyPostcode')->nullable()->after('companyCity');
            $table->string('companyState')->nullable()->after('companyPostcode');
            $table->string('companyCountry')->nullable()->after('companyState');

            // Add longitude and latitude for geocoding
            $table->decimal('companyLatitude', 10, 8)->nullable()->after('companyCountry');
            $table->decimal('companyLongitude', 11, 8)->nullable()->after('companyLatitude');
        });

        // Rename the existing companyAddress to be more specific
        Schema::table('placement_applications', function (Blueprint $table) {
            $table->renameColumn('companyAddress', 'companyAddressLine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placement_applications', function (Blueprint $table) {
            // Rename back to original
            $table->renameColumn('companyAddressLine', 'companyAddress');

            // Remove the new columns
            $table->dropColumn([
                'companyCity',
                'companyPostcode',
                'companyState',
                'companyCountry',
                'companyLatitude',
                'companyLongitude'
            ]);
        });
    }
};
