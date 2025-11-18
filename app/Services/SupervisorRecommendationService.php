<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Lecturer;
use App\Models\SupervisorAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SupervisorRecommendationService
{
    protected GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Get top 3 recommended supervisors for a student based on hybrid scoring
     *
     * @param Student $student
     * @param int $limit
     * @return Collection
     */
    public function getRecommendedSupervisors(Student $student, int $limit = 3): Collection
    {
        $placement = $student->acceptedPlacementApplication;

        if (!$placement) {
            return collect();
        }

        // Get all available supervisors
        $supervisors = Lecturer::where('isSupervisorFaculty', true)
            ->where('status', Lecturer::STATUS_ACTIVE)
            ->whereRaw('(supervisor_quota - current_assignments) > 0')
            ->where('department', $student->program)
            ->get();

        if ($supervisors->isEmpty()) {
            return collect();
        }

        // Calculate score for each supervisor
        $scoredSupervisors = $supervisors->map(function ($lecturer) use ($student, $placement) {
            $score = $this->calculateSupervisorScore($lecturer, $student, $placement);

            return [
                'lecturer' => $lecturer,
                'score' => $score['total'],
                'breakdown' => $score['breakdown'],
                'distance_km' => $score['distance'],
                'available_quota' => $lecturer->supervisor_quota - $lecturer->current_assignments,
            ];
        });

        // Sort by score descending and take top N
        return $scoredSupervisors
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /**
     * Calculate hybrid score for a supervisor-student match
     *
     * Formula: score = (courseMatch * 0.4) + (preferenceMatch * 0.3) + (distanceScore * 0.2) + (workloadScore * 0.1)
     *
     * @param Lecturer $lecturer
     * @param Student $student
     * @param $placement
     * @return array
     */
    protected function calculateSupervisorScore(Lecturer $lecturer, Student $student, $placement): array
    {
        $breakdown = [];

        // 1. Course Match (40% weight)
        $courseMatch = $this->calculateCourseMatch($lecturer, $student, $placement);
        $courseScore = $courseMatch * 0.4;
        $breakdown['course_match'] = [
            'raw' => $courseMatch,
            'weighted' => $courseScore,
            'weight' => '40%'
        ];

        // 2. Travel Preference Match (30% weight)
        $distance = $this->calculateDistance($lecturer, $placement);
        $preferenceMatch = $this->calculatePreferenceMatch($lecturer, $distance);
        $preferenceScore = $preferenceMatch * 0.3;
        $breakdown['preference_match'] = [
            'raw' => $preferenceMatch,
            'weighted' => $preferenceScore,
            'weight' => '30%'
        ];

        // 3. Distance Score (20% weight)
        $distanceScore = $this->calculateDistanceScore($distance) * 0.2;
        $breakdown['distance_score'] = [
            'raw' => $this->calculateDistanceScore($distance),
            'weighted' => $distanceScore,
            'weight' => '20%',
            'distance_km' => $distance
        ];

        // 4. Workload Score (10% weight)
        $workloadScore = $this->calculateWorkloadScore($lecturer) * 0.1;
        $breakdown['workload_score'] = [
            'raw' => $this->calculateWorkloadScore($lecturer),
            'weighted' => $workloadScore,
            'weight' => '10%',
            'current_load' => $lecturer->current_assignments,
            'max_load' => $lecturer->supervisor_quota
        ];

        $totalScore = $courseScore + $preferenceScore + $distanceScore + $workloadScore;

        return [
            'total' => round($totalScore, 4),
            'breakdown' => $breakdown,
            'distance' => $distance
        ];
    }

    /**
     * Calculate course match score
     * Returns 1 if student's program matches lecturer's preferred coursework, else 0
     */
    protected function calculateCourseMatch(Lecturer $lecturer, Student $student, $placement): float
    {
        // Match by program
        if ($lecturer->preferred_coursework && $student->program) {
            if (
                stripos($student->program, $lecturer->preferred_coursework) !== false ||
                stripos($lecturer->preferred_coursework, $student->program) !== false
            ) {
                return 1.0;
            }
        }

        // Fallback: match by jobscope keywords
        if ($lecturer->preferred_coursework && $placement->jobscope) {
            $keywords = explode(' ', strtolower($lecturer->preferred_coursework));
            $jobscope = strtolower($placement->jobscope);

            foreach ($keywords as $keyword) {
                if (strlen($keyword) > 3 && strpos($jobscope, $keyword) !== false) {
                    return 0.7; // Partial match
                }
            }
        }

        return 0.0;
    }

    /**
     * Calculate if student location matches lecturer's travel preference
     * local < 50km, nationwide = any distance
     */
    protected function calculatePreferenceMatch(Lecturer $lecturer, ?float $distance): float
    {
        if ($distance === null) {
            return 0.5; // Unknown distance gets neutral score
        }

        switch ($lecturer->travel_preference) {
            case 'local':
                return $distance <= 50 ? 1.0 : 0.0;

            case 'nationwide':
                return 1.0; // Accepts any distance

            default:
                return 0.5;
        }
    }

    /**
     * Calculate distance score: 1 / (1 + distance_in_km)
     * Closer = higher score
     */
    protected function calculateDistanceScore(?float $distance): float
    {
        if ($distance === null) {
            return 0.5; // Unknown distance gets neutral score
        }

        return 1 / (1 + $distance);
    }

    /**
     * Calculate workload score: 1 - (assigned_students / max_students)
     * Less loaded = higher score
     */
    protected function calculateWorkloadScore(Lecturer $lecturer): float
    {
        if ($lecturer->supervisor_quota == 0) {
            return 0.0;
        }

        return 1 - ($lecturer->current_assignments / $lecturer->supervisor_quota);
    }

    /**
     * Calculate distance between lecturer and placement location
     */
    protected function calculateDistance(Lecturer $lecturer, $placement): ?float
    {
        if (
            !$lecturer->latitude || !$lecturer->longitude ||
            !$placement->companyLatitude || !$placement->companyLongitude
        ) {
            return null;
        }

        return $this->geocodingService->calculateDistance(
            (float) $lecturer->latitude,
            (float) $lecturer->longitude,
            (float) $placement->companyLatitude,
            (float) $placement->companyLongitude
        );
    }

    /**
     * Get all students who need supervisor assignment
     */
    public function getStudentsNeedingSupervisor(): Collection
    {
        return Student::whereHas('placementApplications', function ($q) {
            $q->where('studentAcceptance', 'Accepted');
        })
            ->whereDoesntHave('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            })
            ->with(['user', 'acceptedPlacementApplication'])
            ->get();
    }

    /**
     * Log recommendation for transparency
     */
    public function logRecommendation(Student $student, array $recommendations): void
    {
        Log::info('Supervisor recommendations generated', [
            'student_id' => $student->studentID,
            'student_name' => $student->user->name,
            'student_program' => $student->program,
            'recommendations' => collect($recommendations)->map(function ($rec) {
                return [
                    'lecturer_id' => $rec['lecturer']->lecturerID,
                    'lecturer_name' => $rec['lecturer']->user->name,
                    'score' => $rec['score'],
                    'distance_km' => $rec['distance_km'],
                ];
            })->toArray()
        ]);
    }
}
