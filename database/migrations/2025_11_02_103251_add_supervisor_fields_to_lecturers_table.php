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
            $table->boolean('is_supervisor')->default(false)->after('isAdmin');
            $table->integer('supervisor_quota')->default(0)->after('is_supervisor');
            $table->integer('current_assignments')->default(0)->after('supervisor_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn(['is_supervisor', 'supervisor_quota', 'current_assignments']);
        });
    }
};
