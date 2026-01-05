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
        Schema::create('companies', function (Blueprint $table) {
            $table->id('companyID');
            $table->string('companyName');
            $table->string('companyEmail')->nullable();
            $table->string('companyNumber')->nullable();
            $table->text('companyAddressLine')->nullable();
            $table->string('companyCity')->nullable();
            $table->string('companyPostcode')->nullable();
            $table->string('companyState')->nullable();
            $table->string('companyCountry')->nullable();
            $table->decimal('companyLatitude', 10, 8)->nullable();
            $table->decimal('companyLongitude', 11, 8)->nullable();
            $table->string('industrySupervisorName')->nullable();
            $table->string('industrySupervisorContact')->nullable();
            $table->string('industrySupervisorEmail')->nullable();
            $table->enum('status', ['Active', 'Downsize', 'Blacklisted', 'Closed Down', 'Illegal'])->default('Active');
            $table->timestamps();

            // Indexes for better performance
            $table->index('companyName');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
