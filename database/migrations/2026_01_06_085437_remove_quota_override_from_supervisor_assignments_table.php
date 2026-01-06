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
        Schema::table('supervisor_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('supervisor_assignments', 'quota_override')) {
                $table->dropColumn('quota_override');
            }
            if (Schema::hasColumn('supervisor_assignments', 'override_reason')) {
                $table->dropColumn('override_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisor_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('supervisor_assignments', 'quota_override')) {
                $table->boolean('quota_override')->default(false)->after('distance_km');
            }
            if (!Schema::hasColumn('supervisor_assignments', 'override_reason')) {
                $table->text('override_reason')->nullable()->after('quota_override');
            }
        });
    }
};
