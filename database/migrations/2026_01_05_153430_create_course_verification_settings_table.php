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
        Schema::create('course_verification_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('minimum_credit_hour')->default(118);
            $table->integer('maximum_credit_hour')->default(130);
            $table->timestamps();
        });

        // Insert default value
        DB::table('course_verification_settings')->insert([
            'minimum_credit_hour' => 118,
            'maximum_credit_hour' => 130,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_verification_settings');
    }
};
