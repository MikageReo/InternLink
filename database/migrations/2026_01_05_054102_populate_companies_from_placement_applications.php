<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\PlacementApplication;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get unique companies from placement applications
        $companies = DB::table('placement_applications')
            ->select(
                'companyName',
                DB::raw('MAX(companyEmail) as companyEmail'),
                DB::raw('MAX(companyNumber) as companyNumber'),
                DB::raw('MAX(companyAddressLine) as companyAddressLine'),
                DB::raw('MAX(companyCity) as companyCity'),
                DB::raw('MAX(companyPostcode) as companyPostcode'),
                DB::raw('MAX(companyState) as companyState'),
                DB::raw('MAX(companyCountry) as companyCountry'),
                DB::raw('MAX(companyLatitude) as companyLatitude'),
                DB::raw('MAX(companyLongitude) as companyLongitude'),
                DB::raw('MAX(industrySupervisorName) as industrySupervisorName'),
                DB::raw('MAX(industrySupervisorContact) as industrySupervisorContact'),
                DB::raw('MAX(industrySupervisorEmail) as industrySupervisorEmail')
            )
            ->whereNotNull('companyName')
            ->groupBy('companyName')
            ->get();

        // Insert companies into companies table
        foreach ($companies as $company) {
            DB::table('companies')->insertOrIgnore([
                'companyName' => $company->companyName,
                'companyEmail' => $company->companyEmail,
                'companyNumber' => $company->companyNumber,
                'companyAddressLine' => $company->companyAddressLine,
                'companyCity' => $company->companyCity,
                'companyPostcode' => $company->companyPostcode,
                'companyState' => $company->companyState,
                'companyCountry' => $company->companyCountry,
                'companyLatitude' => $company->companyLatitude,
                'companyLongitude' => $company->companyLongitude,
                'industrySupervisorName' => $company->industrySupervisorName,
                'industrySupervisorContact' => $company->industrySupervisorContact,
                'industrySupervisorEmail' => $company->industrySupervisorEmail,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally clear companies table
        // DB::table('companies')->truncate();
    }
};
