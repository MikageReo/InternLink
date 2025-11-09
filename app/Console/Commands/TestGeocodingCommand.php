<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeocodingService;

class TestGeocodingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:geocoding {address?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google Maps Geocoding API functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $geocodingService = new GeocodingService();

        if (!$geocodingService->isConfigured()) {
            $this->error('Google Maps API key is not configured!');
            $this->info('Please add GOOGLE_MAPS_API_KEY to your .env file');
            return self::FAILURE;
        }

        $testAddress = $this->argument('address') ?? 'Universiti Malaysia Pahang Al-Sultan Abdullah, Kuantan, Pahang, Malaysia';

        $this->info("Testing geocoding with address: {$testAddress}");
        $this->newLine();

        $result = $geocodingService->geocodeAddress($testAddress);

        if ($result) {
            $this->info('✅ Geocoding successful!');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Latitude', $result['latitude']],
                    ['Longitude', $result['longitude']],
                    ['Formatted Address', $result['formatted_address']],
                ]
            );

            // Test distance calculation
            $this->newLine();
            $this->info('Testing distance calculation...');

            // Distance from UMPSA to Kuala Lumpur (approximately)
            $kl_lat = 3.1390;
            $kl_lng = 101.6869;

            $distance = $geocodingService->calculateDistance(
                $result['latitude'],
                $result['longitude'],
                $kl_lat,
                $kl_lng
            );

            $this->info("Distance to Kuala Lumpur: " . round($distance, 2) . " km");

            // Test finding nearest lecturers (if any exist with coordinates)
            $this->newLine();
            $this->info('Testing nearest lecturer search...');

            $nearestLecturers = $geocodingService->findNearestLecturers(
                $result['latitude'],
                $result['longitude'],
                3
            );

            if ($nearestLecturers->count() > 0) {
                $this->info("Found {$nearestLecturers->count()} lecturers with coordinates:");
                $lecturerData = $nearestLecturers->map(function ($lecturer) {
                    return [
                        'ID' => $lecturer->lecturerID,
                        'Name' => $lecturer->user->name ?? 'N/A',
                        'Distance (km)' => round($lecturer->distance, 2),
                        'Status' => $lecturer->status,
                    ];
                })->toArray();

                $this->table(['ID', 'Name', 'Distance (km)', 'Status'], $lecturerData);
            } else {
                $this->warn('No lecturers found with geocoding coordinates in the database.');
            }

        } else {
            $this->error('❌ Geocoding failed!');
            $this->info('This could be due to:');
            $this->info('1. Invalid or missing Google Maps API key');
            $this->info('2. API quota exceeded');
            $this->info('3. Invalid address format');
            $this->info('4. Network connectivity issues');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
