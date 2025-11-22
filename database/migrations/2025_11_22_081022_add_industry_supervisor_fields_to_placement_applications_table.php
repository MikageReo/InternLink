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
            // Add industry supervisor information fields
            $table->string('industrySupervisorName')->nullable()->after('companyNumber');
            $table->string('industrySupervisorContact')->nullable()->after('industrySupervisorName');
            $table->string('industrySupervisorEmail')->nullable()->after('industrySupervisorContact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placement_applications', function (Blueprint $table) {
            // Remove industry supervisor information fields
            $table->dropColumn([
                'industrySupervisorName',
                'industrySupervisorContact',
                'industrySupervisorEmail'
            ]);
        });
    }
};
