<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Lecturer;
use App\Models\SupervisorAssignment;
use App\Models\AHPWeight;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SupervisorRecommendationService
{
    protected GeocodingService $geocodingService;

    // Default weights (fallback if no AHP weights exist)
    protected const DEFAULT_WEIGHTS = [
        'course_match' => 0.40,
        'preference_match' => 0.30,
        'distance_score' => 0.20,
        'workload_score' => 0.10,
    ];

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Get weights for supervisor scoring
     * Checks for AHP weights first, falls back to default weights
     *
     * @return array
     */
    protected function getWeights(): array
    {
        $ahpWeights = AHPWeight::getLatest();

        if ($ahpWeights && $ahpWeights->is_consistent) {
            return $ahpWeights->getWeights();
        }

        // Fallback to default weights
        return self::DEFAULT_WEIGHTS;
    }

    /**
     * Get top 3 recommended supervisors for a student based on hybrid scoring
     *
     * @param Student $student
     * @param int $limit
     * @param bool $includeFullQuota Include supervisors at quota limit (for override scenarios)
     * @return Collection
     */
    public function getRecommendedSupervisors(Student $student, int $limit = 3, bool $includeFullQuota = false): Collection
    {
        $placement = $student->acceptedPlacementApplication;

        if (!$placement) {
            return collect();
        }

        // Get all available supervisors (any department can supervise any student)
        // Exclude lecturers with administrative positions (Dean, Deputy Dean, etc.)
        // Only include active lecturers
        $query = Lecturer::where('isSupervisorFaculty', true)
            ->where('status', Lecturer::STATUS_ACTIVE);

        // Filter by available quota (unless override is allowed)
        if (!$includeFullQuota) {
            $query->whereRaw('(supervisor_quota - current_assignments) > 0');
        }

        $supervisors = $query->get()
            ->filter(function ($lecturer) {
                return $lecturer->canSupervise(); // This checks for administrative positions
            });

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
     * Formula uses AHP weights if available, otherwise defaults:
     * score = (courseMatch * weight) + (preferenceMatch * weight) + (distanceScore * weight) + (workloadScore * weight)
     *
     * @param Lecturer $lecturer
     * @param Student $student
     * @param $placement
     * @return array
     */
    protected function calculateSupervisorScore(Lecturer $lecturer, Student $student, $placement): array
    {
        // Get weights (AHP or default)
        $weights = $this->getWeights();
        $breakdown = [];

        // 1. Course Match
        $courseMatch = $this->calculateCourseMatch($lecturer, $student, $placement);
        $courseWeight = $weights['course_match'] ?? self::DEFAULT_WEIGHTS['course_match'];
        $courseScore = $courseMatch * $courseWeight;
        $breakdown['course_match'] = [
            'raw' => $courseMatch,
            'weighted' => $courseScore,
            'weight' => number_format($courseWeight * 100, 1) . '%'
        ];

        // 2. Travel Preference Match
        $distance = $this->calculateDistance($lecturer, $placement);
        $preferenceMatch = $this->calculatePreferenceMatch($lecturer, $distance);
        $preferenceWeight = $weights['preference_match'] ?? self::DEFAULT_WEIGHTS['preference_match'];
        $preferenceScore = $preferenceMatch * $preferenceWeight;
        $breakdown['preference_match'] = [
            'raw' => $preferenceMatch,
            'weighted' => $preferenceScore,
            'weight' => number_format($preferenceWeight * 100, 1) . '%'
        ];

        // 3. Distance Score
        $distanceScoreRaw = $this->calculateDistanceScore($distance);
        $distanceWeight = $weights['distance_score'] ?? self::DEFAULT_WEIGHTS['distance_score'];
        $distanceScore = $distanceScoreRaw * $distanceWeight;
        $breakdown['distance_score'] = [
            'raw' => $distanceScoreRaw,
            'weighted' => $distanceScore,
            'weight' => number_format($distanceWeight * 100, 1) . '%',
            'distance_km' => $distance
        ];

        // 4. Workload Score
        $workloadScoreRaw = $this->calculateWorkloadScore($lecturer);
        $workloadWeight = $weights['workload_score'] ?? self::DEFAULT_WEIGHTS['workload_score'];
        $workloadScore = $workloadScoreRaw * $workloadWeight;
        $breakdown['workload_score'] = [
            'raw' => $workloadScoreRaw,
            'weighted' => $workloadScore,
            'weight' => number_format($workloadWeight * 100, 1) . '%',
            'current_load' => $lecturer->current_assignments,
            'max_load' => $lecturer->supervisor_quota
        ];

        $totalScore = $courseScore + $preferenceScore + $distanceScore + $workloadScore;

        return [
            'total' => round($totalScore, 4),
            'breakdown' => $breakdown,
            'distance' => $distance,
            'weights_source' => AHPWeight::getLatest() ? 'ahp' : 'default'
        ];
    }

    /**
     * Calculate course match score
     * Returns 1 if student's program matches lecturer's program, else 0
     */
    protected function calculateCourseMatch(Lecturer $lecturer, Student $student, $placement): float
    {
        // Match by program - exact match
        if ($lecturer->program && $student->program) {
            if ($lecturer->program === $student->program) {
                return 1.0;
            }
        }

        // Fallback: match by jobscope keywords if program doesn't match
        if ($lecturer->program && $placement->jobscope) {

            //Convert full program name to code
            $programCode = $this->getProgramCodeFromFullName($lecturer->program);

            // If we can't get a program code, skip keyword matching
            if (!$programCode) {
                return 0.0;
            }

            // Map program codes to keywords
            $programKeywords = [
                'BCS' => [
                    'software',
                    'engineering',
                    'development',
                    'programming',
                    'coding',
                    'application',
                    'system',
                    'web',
                    'mobile',
                    'developer',
                    'programmer',
                    'software development',
                    'app development',
                    'coding',
                    'algorithm',
                    'framework',
                    'api',
                    'frontend',
                    'backend',
                    'full stack',
                    'devops',
                    'debugging',
                    'testing',
                    'quality assurance',
                    'database',
                    'sql',
                    'oop',
                    'git',
                    'version control',
                    'mvc',
                    'cloud',
                    'integration',
                    'deployment'
                ],

                'BCN' => [
                    'system',
                    'network',
                    'networking',
                    'server',
                    'routing',
                    'switch',
                    'firewall',
                    'protocol',
                    'tcp',
                    'ip',
                    'lan',
                    'wan',
                    'system administration',
                    'network administration',
                    'server management',
                    'hardware',
                    'operating system',
                    'os',
                    'windows server',
                    'linux',
                    'troubleshoot',
                    'configuration',
                    'vm',
                    'cloud',
                    'it support',
                    'deployment',
                    'storage',
                    'backup',
                    'monitoring',
                    'vpn',
                    'switching',
                    'router',
                    'udp',
                    'ip address',
                    'subnet',
                    'dhcp',
                    'dns',
                    'packet',
                    'infrastructure',
                    'wifi',
                    'network security',
                    'cisco',
                    'network configuration',
                    'topology'
                ],

                'BCM' => [
                    'multimedia',
                    'design',
                    'graphic',
                    'animation',
                    'media',
                    'video',
                    'creative',
                    'ui',
                    'ux',
                    'user interface',
                    'user experience',
                    '3d',
                    '2d',
                    'adobe',
                    'photoshop',
                    'illustrator',
                    'premiere',
                    'after effects',
                    'blender',
                    'maya',
                    'digital art',
                    'visual design',
                    'storyboard',
                    'video editing',
                    'motion design',
                    'interactive media',
                    'web design',
                    'graphic design',
                    'illustration',
                    'digital content',
                    'marketing design',
                    'photo editing'
                ],

                'BCY' => [
                    'security',
                    'cyber',
                    'cybersecurity',
                    'network security',
                    'information security',
                    'protection',
                    'penetration testing',
                    'encryption',
                    'firewall',
                    'vulnerability',
                    'malware',
                    'ransomware',
                    'phishing',
                    'threat detection',
                    'security audit',
                    'risk assessment',
                    'compliance',
                    'forensics',
                    'authentication',
                    'authorization',
                    'incident response',
                    'data protection',
                    'privacy',
                    'ids',
                    'ips',
                    'siem',
                    'zero trust',
                    'cyber attack',
                    'risk',
                    'threat',
                    'incident',
                    'security operation',
                    'soc',
                    'intrusion detection',
                    'identity management'
                ],

                'DRC' => [
                    'computer',
                    'information technology',
                    'it',
                    'basic',
                    'fundamental',
                    'application',
                    'mysql',
                    'data entry',
                    'system support',
                    'technical support',
                    'helpdesk',
                    'networking',
                    'troubleshooting',
                    'installation',
                    'maintenance',
                    'pc maintenance',
                    'system analysis',
                    'software',
                    'hardware',
                    'coding',
                    'programming',
                    'html',
                    'css',
                    'javascript',
                    'python',
                    'java',
                    'c++',
                    'database',
                    'sql',
                    'web development',
                    'it support',
                    'troubleshoot',
                    'documentation',
                    'network basics',
                    'operating system',
                    'algorithm',
                    'problem solving',
                    'frontend',
                    'backend',
                    'system support',
                    'technical support',
                    'debugging',
                    'testing',
                    'application development'
                ]
            ];


            $keywords = $programKeywords[$programCode] ?? [];
            $jobscope = strtolower($placement->jobscope);

            // Count how many keywords match in the jobscope
            $matchedKeywords = 0;
            $totalKeywords = count($keywords);

            if ($totalKeywords === 0) {
                return 0.0; // No keywords to match
            }

            foreach ($keywords as $keyword) {
                if (strpos($jobscope, $keyword) !== false) {
                    $matchedKeywords++;
                }
            }

            // Calculate match percentage
            $matchPercentage = ($matchedKeywords / $totalKeywords) * 100;

            // Return score based on percentage of keywords matched (more realistic scoring)
            // Higher percentage = more relevant match, but still lower than perfect match
            if ($matchPercentage >= 50) {
                // High relevance: 50-60% of keywords match
                return 0.45; // 45% score
            } elseif ($matchPercentage >= 30) {
                // Medium relevance: 30-50% of keywords match
                return 0.30; // 30% score
            } elseif ($matchPercentage >= 15) {
                // Low relevance: 15-30% of keywords match
                return 0.15; // 15% score
            } elseif ($matchPercentage >= 5) {
                // Very low relevance: 5-15% of keywords match
                return 0.05; // 5% score
            } elseif ($matchedKeywords > 0) {
                // Minimal match: less than 5% but at least 1 keyword found
                return 0.02; // 2% score
            }

            // No keywords matched
            return 0.0;
        }

        return 0.0;
    }

    //Convert full name to short code
    private function getProgramCodeFromFullName($fullName): ?string
    {
        $programMap = [
            'Bachelor of Computer Science (Software Engineering) with Honours' => 'BCS',
            'Bachelor of Computer Science (Computer Systems & Networking) with Honours' => 'BCN',
            'Bachelor of Computer Science (Multimedia Software) with Honours' => 'BCM',
            'Bachelor of Computer Science (Cyber Security) with Honours' => 'BCY',
            'Diploma in Computer Science' => 'DRC',
        ];

        return $programMap[$fullName] ?? null;
    }
    /**
     * Calculate if student location matches lecturer's travel preference
     * local < 50km, nationwide = any distance
     * Ensures minimum score of 0.3 to never go to 0
     */
    protected function calculatePreferenceMatch(Lecturer $lecturer, ?float $distance): float
    {
        if ($distance === null) {
            return 0.5; // Unknown distance gets neutral score
        }

        $travelPreference = strtolower($lecturer->travel_preference ?? '');

        switch ($travelPreference) {
            case 'local':
                // Local preference: perfect match within 50km, partial match beyond
                if ($distance <= 50) {
                    return 1.0; // Perfect match within preference range
                } else {
                    // Beyond preference but still assignable, give partial credit
                    // Graduated reduction based on distance
                    if ($distance <= 100) {
                        return 0.7; // Still relatively close
                    } elseif ($distance <= 200) {
                        return 0.5; // Moderate distance
                    } else {
                        return 0.3; // Far but still acceptable (minimum)
                    }
                }

            case 'nationwide':
                return 1.0; // Accepts any distance - always perfect match

            default:
                // Unknown or missing preference - assume flexible but not ideal
                return 0.5;
        }
    }

    /**
     * Calculate distance score using tiered system
     * Closer = higher score
     */
    protected function calculateDistanceScore(?float $distance): float
    {
        if ($distance === null) {
            return 0.5; // Unknown distance gets neutral score
        }

        // Tiered scoring based on practical distance ranges
        if ($distance <= 20) {
            return 1.0; // Very close (0-20 km) - ideal distance
        } elseif ($distance <= 50) {
            return 0.75; // Close (20-50 km) - still very good
        } elseif ($distance <= 100) {
            return 0.5; // Moderate (50-100 km) - acceptable
        } elseif ($distance <= 200) {
            return 0.3; // Far (100-200 km) - less ideal but workable
        } else {
            return 0.15; // Very far (200+ km) - minimum score
        }
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
