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
        Schema::table('request_defers', function (Blueprint $table) {
            if (Schema::hasColumn('request_defers', 'startDate')) {
                $table->dropColumn('startDate');
            }
            if (Schema::hasColumn('request_defers', 'endDate')) {
                $table->dropColumn('endDate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_defers', function (Blueprint $table) {
            if (!Schema::hasColumn('request_defers', 'startDate')) {
                $table->date('startDate')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('request_defers', 'endDate')) {
                $table->date('endDate')->nullable()->after('startDate');
            }
        });
    }
};
