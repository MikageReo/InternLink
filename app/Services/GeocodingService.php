<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class GeocodingService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = Config::get('services.google_maps.api_key');
        $this->baseUrl = Config::get('services.google_maps.geocoding_url');
    }

    /**
     * Geocode an address to get latitude and longitude
     *
     * @param string $address Full address string
     * @return array|null Returns ['latitude' => float, 'longitude' => float] or null if failed
     */
    public function geocodeAddress(string $address): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Maps API key not configured');
            return null;
        }

        if (empty(trim($address))) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'address' => $address,
                'key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                Log::error('Google Maps API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'address' => $address
                ]);
                return null;
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                Log::warning('Google Maps API returned no results', [
                    'status' => $data['status'],
                    'address' => $address
                ]);
                return null;
            }

            $location = $data['results'][0]['geometry']['location'];

            return [
                'latitude' => (float) $location['lat'],
                'longitude' => (float) $location['lng'],
                'formatted_address' => $data['results'][0]['formatted_address'] ?? $address
            ];
        } catch (\Exception $e) {
            Log::error('Geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Geocode an address from structured components
     *
     * @param array $addressComponents Array with keys: street, city, postcode, state, country
     * @return array|null
     */
    public function geocodeStructuredAddress(array $addressComponents): ?array
    {
        $addressParts = array_filter([
            $addressComponents['street'] ?? '',
            $addressComponents['city'] ?? '',
            $addressComponents['postcode'] ?? '',
            $addressComponents['state'] ?? '',
            $addressComponents['country'] ?? ''
        ]);

        if (empty($addressParts)) {
            return null;
        }

        $fullAddress = implode(', ', $addressParts);
        return $this->geocodeAddress($fullAddress);
    }

    /**
     * Geocode a student's address
     *
     * @param object $student Student model instance
     * @return array|null
     */
    public function geocodeStudentAddress($student): ?array
    {
        return $this->geocodeStructuredAddress([
            'street' => $student->address,
            'city' => $student->city,
            'postcode' => $student->postcode,
            'state' => $student->state,
            'country' => $student->country
        ]);
    }

    /**
     * Geocode a lecturer's address
     *
     * @param object $lecturer Lecturer model instance
     * @return array|null
     */
    public function geocodeLecturerAddress($lecturer): ?array
    {
        return $this->geocodeStructuredAddress([
            'street' => $lecturer->address,
            'city' => $lecturer->city,
            'postcode' => $lecturer->postcode,
            'state' => $lecturer->state,
            'country' => $lecturer->country
        ]);
    }

    /**
     * Geocode a company's address from placement application
     *
     * @param object $application PlacementApplication model instance
     * @return array|null
     */
    public function geocodeCompanyAddress($application): ?array
    {
        return $this->geocodeStructuredAddress([
            'street' => $application->companyAddressLine,
            'city' => $application->companyCity,
            'postcode' => $application->companyPostcode,
            'state' => $application->companyState,
            'country' => $application->companyCountry
        ]);
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in kilometers
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Find nearest lecturers to a given location
     *
     * @param float $latitude Target latitude
     * @param float $longitude Target longitude
     * @param int $limit Number of lecturers to return
     * @param array $lecturerIds Optional array of lecturer IDs to filter by
     * @return \Illuminate\Support\Collection Collection of lecturers with distance
     */
    public function findNearestLecturers(float $latitude, float $longitude, int $limit = 5, array $lecturerIds = []): \Illuminate\Support\Collection
    {
        $query = \App\Models\Lecturer::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('status', 'active');

        if (!empty($lecturerIds)) {
            $query->whereIn('lecturerID', $lecturerIds);
        }

        $lecturers = $query->get();

        return $lecturers->map(function ($lecturer) use ($latitude, $longitude) {
            $lecturer->distance = $this->calculateDistance(
                $latitude,
                $longitude,
                (float) $lecturer->latitude,
                (float) $lecturer->longitude
            );
            return $lecturer;
        })
            ->sortBy('distance')
            ->take($limit)
            ->values();
    }

    /**
     * Find nearest supervisors to a student's location
     * Only returns supervisors with available quota and same department as student
     *
     * @param float $latitude Target latitude (student's location)
     * @param float $longitude Target longitude (student's location)
     * @param string|null $studentDepartment Optional department filter (no cross-department)
     * @param int $limit Number of supervisors to return
     * @param bool $includeFullQuota Include supervisors at quota limit (for override scenarios)
     * @return \Illuminate\Support\Collection Collection of supervisors with distance and availability
     */
    public function findNearestSupervisors(
        float $latitude,
        float $longitude,
        ?string $studentDepartment = null,
        int $limit = 10,
        bool $includeFullQuota = false
    ): \Illuminate\Support\Collection {
        $query = \App\Models\Lecturer::where('isSupervisorFaculty', true)
            ->where('status', \App\Models\Lecturer::STATUS_ACTIVE)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Filter by department (no cross-department assignments)
        if ($studentDepartment) {
            $query->where('department', $studentDepartment);
        }

        // Filter by available quota (unless override is allowed)
        if (!$includeFullQuota) {
            $query->whereRaw('(supervisor_quota - current_assignments) > 0');
        }

        $supervisors = $query->get();

        return $supervisors->map(function ($supervisor) use ($latitude, $longitude) {
            $supervisor->distance = $this->calculateDistance(
                $latitude,
                $longitude,
                (float) $supervisor->latitude,
                (float) $supervisor->longitude
            );
            $supervisor->available_quota = $supervisor->supervisor_quota - $supervisor->current_assignments;
            return $supervisor;
        })
            ->sortBy('distance')
            ->take($limit)
            ->values();
    }

    /**
     * Find nearest supervisors for a student
     * Uses student's placement company address if available, otherwise student's address
     *
     * @param \App\Models\Student $student Student model instance
     * @param int $limit Number of supervisors to return
     * @param bool $includeFullQuota Include supervisors at quota limit
     * @return \Illuminate\Support\Collection
     */
    public function findNearestSupervisorsForStudent(
        \App\Models\Student $student,
        int $limit = 10,
        bool $includeFullQuota = false
    ): \Illuminate\Support\Collection {
        // Prioritize company location for placement supervision
        $placement = $student->acceptedPlacementApplication;

        if ($placement && $placement->has_geocoding) {
            return $this->findNearestSupervisors(
                (float) $placement->companyLatitude,
                (float) $placement->companyLongitude,
                $student->program, // Using program as department equivalent
                $limit,
                $includeFullQuota
            );
        }

        // Fallback to student's address
        if ($student->has_geocoding) {
            return $this->findNearestSupervisors(
                (float) $student->latitude,
                (float) $student->longitude,
                $student->program,
                $limit,
                $includeFullQuota
            );
        }

        // If no geocoding available, return empty collection
        return collect();
    }

    /**
     * Check if API key is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
