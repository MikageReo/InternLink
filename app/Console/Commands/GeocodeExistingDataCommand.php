<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeocodingService;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\PlacementApplication;
use Illuminate\Support\Facades\DB;

class GeocodeExistingDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocode:existing-data {--type=all : Type of data to geocode (students, lecturers, placements, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Geocode existing students, lecturers, and placement applications';

    protected GeocodingService $geocodingService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->geocodingService = new GeocodingService();

        if (!$this->geocodingService->isConfigured()) {
            $this->error('Google Maps API key is not configured!');
            $this->info('Please add GOOGLE_MAPS_API_KEY to your .env file');
            return self::FAILURE;
        }

        $type = $this->option('type');

        if ($type === 'all' || $type === 'students') {
            $this->geocodeStudents();
        }

        if ($type === 'all' || $type === 'lecturers') {
            $this->geocodeLecturers();
        }

        if ($type === 'all' || $type === 'placements') {
            $this->geocodePlacementApplications();
        }

        $this->newLine();
        $this->info('✅ Geocoding process completed!');

        return self::SUCCESS;
    }

    protected function geocodeStudents()
    {
        $this->info('📍 Geocoding students...');

        $students = Student::whereNull('latitude')
            ->orWhereNull('longitude')
            ->get();

        if ($students->isEmpty()) {
            $this->info('No students need geocoding.');
            return;
        }

        $bar = $this->output->createProgressBar($students->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;

        foreach ($students as $student) {
            if (empty($student->address) && empty($student->city)) {
                $failureCount++;
                $bar->advance();
                continue;
            }

            $result = $this->geocodingService->geocodeStudentAddress($student);

            if ($result) {
                $student->update([
                    'latitude' => $result['latitude'],
                    'longitude' => $result['longitude'],
                    'has_geocoding' => true,
                ]);
                $successCount++;
            } else {
                $failureCount++;
            }

            $bar->advance();

            // Rate limiting - be nice to Google API
            usleep(100000); // 0.1 second delay between requests
        }

        $bar->finish();
        $this->newLine();
        $this->info("Students geocoded: {$successCount} success, {$failureCount} failed");
    }

    protected function geocodeLecturers()
    {
        $this->info('📍 Geocoding lecturers...');

        $lecturers = Lecturer::whereNull('latitude')
            ->orWhereNull('longitude')
            ->get();

        if ($lecturers->isEmpty()) {
            $this->info('No lecturers need geocoding.');
            return;
        }

        $bar = $this->output->createProgressBar($lecturers->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;

        foreach ($lecturers as $lecturer) {
            if (empty($lecturer->address) && empty($lecturer->city)) {
                $failureCount++;
                $bar->advance();
                continue;
            }

            $result = $this->geocodingService->geocodeLecturerAddress($lecturer);

            if ($result) {
                $lecturer->update([
                    'latitude' => $result['latitude'],
                    'longitude' => $result['longitude'],
                    'has_geocoding' => true,
                ]);
                $successCount++;
            } else {
                $failureCount++;
            }

            $bar->advance();

            // Rate limiting - be nice to Google API
            usleep(100000); // 0.1 second delay between requests
        }

        $bar->finish();
        $this->newLine();
        $this->info("Lecturers geocoded: {$successCount} success, {$failureCount} failed");
    }

    protected function geocodePlacementApplications()
    {
        $this->info('📍 Geocoding placement applications...');

        $applications = PlacementApplication::whereNull('companyLatitude')
            ->orWhereNull('companyLongitude')
            ->get();

        if ($applications->isEmpty()) {
            $this->info('No placement applications need geocoding.');
            return;
        }

        $bar = $this->output->createProgressBar($applications->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;

        foreach ($applications as $application) {
            if (empty($application->companyAddressLine) && empty($application->companyCity)) {
                $failureCount++;
                $bar->advance();
                continue;
            }

            $result = $this->geocodingService->geocodeCompanyAddress($application);

            if ($result) {
                $application->update([
                    'companyLatitude' => $result['latitude'],
                    'companyLongitude' => $result['longitude'],
                    'has_geocoding' => true,
                ]);
                $successCount++;
            } else {
                $failureCount++;
            }

            $bar->advance();

            // Rate limiting - be nice to Google API
            usleep(100000); // 0.1 second delay between requests
        }

        $bar->finish();
        $this->newLine();
        $this->info("Placement applications geocoded: {$successCount} success, {$failureCount} failed");
    }
}
