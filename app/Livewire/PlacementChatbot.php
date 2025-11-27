<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PlacementApplication;
use App\Models\Student;
use App\Models\SupervisorAssignment;
use App\Models\CourseVerification;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlacementChatbot extends Component
{
    public $isOpen = false;
    public $messages = [];
    public $currentMessage = '';
    public $isTyping = false;
    public $quickActions = [];
    public $storedSummaries = []; // Stores ranked summaries for number-based selection
    public $viewingDetails = false; // Tracks if currently viewing details
    public $faqMenuShown = false; // Tracks if FAQ menu is currently displayed
    public $storedFAQs = []; // Stores FAQ list for number-based selection

    protected function getGeocodingService(): GeocodingService
    {
        return app(GeocodingService::class);
    }

    public function mount()
    {
        // Initialize with welcome message
        $this->messages[] = [
            'type' => 'bot',
            'content' => "Hello! I'm Lia , your InternLink Assistant. I can help you with:\n\n" .
                "📊 Evaluate placements before applying\n" .
                "📋 Check your application status\n" .
                "❓ Answer FAQs (simply select a number)\n\n" .
                "What would you like to know?",
            'timestamp' => now()
        ];

        // Set up quick actions
        $this->quickActions = [
            'Evaluate Placement',
            'Check Status',
            'FAQs'
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        if (empty(trim($this->currentMessage))) {
            return;
        }

        $userMessage = trim($this->currentMessage);

        // Add user message
        $this->messages[] = [
            'type' => 'user',
            'content' => $userMessage,
            'timestamp' => now()
        ];

        $this->currentMessage = '';
        $this->isTyping = true;

        // Process message and get response
        $response = $this->processMessage($userMessage);

        $this->isTyping = false;

        // Add bot response
        $this->messages[] = [
            'type' => 'bot',
            'content' => $response,
            'timestamp' => now()
        ];

        // Scroll to bottom after response
        $this->dispatch('scroll-to-bottom');
    }

    public function quickAction($action)
    {
        $this->currentMessage = $action;
        $this->sendMessage();
    }

    private function processMessage($message): string
    {
        $messageLower = strtolower($message);
        $student = Auth::user()->student;

        if (!$student) {
            return "I couldn't find your student profile. Please contact the administrator.";
        }

        // Check if user is selecting a number (for application details or FAQs)
        // This should be checked BEFORE evaluate placement
        if (preg_match('/#?\d+/', $message)) {
            // Check if FAQ menu is shown and user selected a number
            if ($this->faqMenuShown && !empty($this->storedFAQs)) {
                $selectedNumber = $this->extractNumberSelection($message);
                if ($selectedNumber !== null) {
                    return $this->showFAQAnswer($selectedNumber);
                }
            }

            // Check if summaries are stored and user selected a number
            if (!empty($this->storedSummaries)) {
                $selectedNumber = $this->extractNumberSelection($message);
                if ($selectedNumber !== null) {
                    return $this->showApplicationDetails($student, $selectedNumber);
                }
            }
        }

        // Check for navigation commands
        if (str_contains($messageLower, 'back') || str_contains($messageLower, 'menu') || str_contains($messageLower, 'exit') || str_contains($messageLower, 'return')) {
            // If viewing FAQ menu/answer, return to FAQ menu
            if ($this->faqMenuShown && !empty($this->storedFAQs)) {
                if (str_contains($messageLower, 'back') || str_contains($messageLower, 'menu')) {
                    return $this->showFAQMenu();
                } elseif (str_contains($messageLower, 'exit')) {
                    $this->faqMenuShown = false;
                    $this->storedFAQs = [];
                    return "Returning to main menu. What would you like to do?\n\n" .
                        "• Evaluate a placement\n" .
                        "• Check your application status\n" .
                        "• Answer FAQs";
                }
            }

            // If viewing placement details, return to summary
            if ($this->viewingDetails && !empty($this->storedSummaries)) {
                $this->viewingDetails = false;
                return $this->showSummaryList($student);
            }

            // Otherwise, return to main menu
            $this->viewingDetails = false;
            $this->storedSummaries = [];
            $this->faqMenuShown = false;
            $this->storedFAQs = [];
            return "Returning to main menu. What would you like to do?\n\n" .
                "• Evaluate a placement\n" .
                "• Check your application status\n" .
                "• Answer FAQs";
        }

        // Evaluate placement
        if (str_contains($messageLower, 'evaluate') || str_contains($messageLower, 'is this good') || str_contains($messageLower, 'should i apply')) {
            return $this->evaluatePlacement($student, $message);
        }

        // Check status
        if (str_contains($messageLower, 'status') || str_contains($messageLower, 'check') || str_contains($messageLower, 'application')) {
            return $this->checkStatus($student);
        }

        // FAQs - Check this last to avoid conflicts with other commands
        // Only trigger if explicitly asking for FAQs or if message starts with FAQ keywords
        if (
            str_contains($messageLower, 'faq') ||
            str_contains($messageLower, 'questions') ||
            (str_contains($messageLower, 'what') && (str_contains($messageLower, 'requirement') || str_contains($messageLower, 'document') || str_contains($messageLower, 'need'))) ||
            (str_contains($messageLower, 'how') && str_contains($messageLower, 'apply'))
        ) {
            $this->faqMenuShown = true;
            return $this->answerFAQ($messageLower);
        }

        // Default response
        return "I'm not sure I understand. Try asking me to:\n\n" .
            "• Evaluate a placement\n" .
            "• Check your application status\n" .
            "• Answer FAQs";
    }

    private function evaluatePlacement($student, $message): string
    {
        // Check if user wants to view another application's details
        if (str_contains(strtolower($message), 'another') || str_contains(strtolower($message), 'more') || str_contains(strtolower($message), 'next')) {
            if (empty($this->storedSummaries)) {
                return "I don't have your application list. Please say 'Evaluate Placement' first to see your applications.";
            }
            return $this->showSummaryList($student);
        }

        // Try to extract number selection (e.g., "#1", "show #1", "details for #1")
        $selectedNumber = $this->extractNumberSelection($message);

        if ($selectedNumber !== null && !empty($this->storedSummaries)) {
            // User selected a number from the summary list
            return $this->showApplicationDetails($student, $selectedNumber);
        }

        // Try to extract company name from message
        $companyName = $this->extractCompanyName($message);

        if (!$companyName) {
            // Get ALL student's applications
            $applications = $student->placementApplications()
                ->orderBy('applicationDate', 'desc')
                ->get();

            if ($applications->isEmpty()) {
                return "I couldn't find any placements to evaluate. Please apply for a placement first.\n\n" .
                    "Example: 'Evaluate placement at Maybank'";
            }

            // If only one application, show full details
            if ($applications->count() === 1) {
                $this->viewingDetails = true;
                $evaluation = $this->performEvaluation($student, $applications->first());
                $evaluation .= "\n\n" . $this->getNavigationOptions();
                return $evaluation;
            }

            // Multiple applications - show summary list
            return $this->showSummaryList($student);
        } else {
            // Specific company requested - show full details
            $application = $student->placementApplications()
                ->where('companyName', 'like', '%' . $companyName . '%')
                ->orderBy('applicationDate', 'desc')
                ->first();

            if (!$application) {
                return "I couldn't find a placement application for '$companyName'. Please check the company name and try again.";
            }

            $this->viewingDetails = true;
            $evaluation = $this->performEvaluation($student, $application);
            $evaluation .= "\n\n" . $this->getNavigationOptions();
            return $evaluation;
        }
    }

    private function performEvaluation($student, $application): string
    {
        $score = 0;
        $factors = [];
        $maxScore = 100;

        // 1. Jobscope Match with Student Program (40 points - 40%)
        $jobscopeScore = $this->calculateJobscopeMatch($student, $application);
        $score += $jobscopeScore;
        $factors[] = $this->getJobscopeFactorText($jobscopeScore, $student, $application);

        // 2. Distance between Student and Company (20 points - 20%)
        $distanceScore = $this->calculateDistanceScore($student, $application);
        $score += $distanceScore;
        $factors[] = $this->getDistanceFactorText($distanceScore, $student, $application);

        // 3. Company History (20 points - 20%)
        $companyHistoryScore = $this->calculateCompanyHistoryScore($application);
        $score += $companyHistoryScore;
        $factors[] = $this->getCompanyHistoryFactorText($companyHistoryScore, $application);

        // 4. Allowance (20 points - 20%)
        $allowanceScore = $this->calculateAllowanceScore($application);
        $score += $allowanceScore;
        $factors[] = $this->getAllowanceFactorText($allowanceScore, $application);

        // Generate recommendation
        $rating = $this->getRating($score);
        $recommendation = $this->getRecommendation($score, $factors);

        return "📊 **Placement Evaluation for {$application->companyName}**\n\n" .
            "**Overall Score: {$score}/{$maxScore}** ({$rating})\n\n" .
            "**Evaluation Factors:**\n" . implode("\n", $factors) . "\n\n" .
            "**Recommendation:**\n{$recommendation}";
    }

    /**
     * Calculate jobscope match score (40 points max)
     */
    private function calculateJobscopeMatch($student, $application): float
    {
        if (!$student->program || !$application->jobscope) {
            return 0;
        }

        $program = strtolower($student->program);
        $jobscope = strtolower($application->jobscope);

        // Extract keywords from program (common program keywords)
        $programKeywords = $this->extractProgramKeywords($program);

        // Count matches in jobscope
        $matchCount = 0;
        $totalKeywords = count($programKeywords);

        if ($totalKeywords === 0) {
            return 0;
        }

        foreach ($programKeywords as $keyword) {
            if (str_contains($jobscope, $keyword)) {
                $matchCount++;
            }
        }

        // Calculate percentage match
        $matchPercentage = ($matchCount / $totalKeywords) * 100;

        // Score based on match percentage (40 points max)
        if ($matchPercentage >= 70) {
            return 40; // Excellent match
        } elseif ($matchPercentage >= 50) {
            return 30; // Good match
        } elseif ($matchPercentage >= 30) {
            return 20; // Fair match
        } elseif ($matchPercentage >= 15) {
            return 10; // Poor match
        }

        return 0; // No match
    }

    /**
     * Extract keywords from student program
     * Based on UMP's Bachelor of Computer Science programs
     */
    private function extractProgramKeywords($program): array
    {
        $keywords = [];
        $programLower = strtolower($program);

        // UMP Computer Science Program Keywords Mapping
        $programMappings = [
            // Bachelor of Computer Science (Software Engineer)
            'software engineer' => [
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

            // Bachelor of Computer Science (Computer Systems & Networking)
            'computer systems' => [
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
            ],
            'networking' => [
                'network',
                'networking',
                'lan',
                'wan',
                'vpn',
                'routing',
                'switching',
                'router',
                'switch',
                'firewall',
                'tcp',
                'udp',
                'protocol',
                'ip address',
                'subnet',
                'dhcp',
                'dns',
                'network administration',
                'server management',
                'packet',
                'infrastructure',
                'wifi',
                'network security',
                'cisco',
                'network configuration',
                'topology'
            ],

            // Bachelor of Computer Science (Multimedia Software)
            'multimedia software' => [
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
                'web design'
            ],
            'multimedia' => [
                'multimedia',
                'design',
                'graphic',
                'animation',
                'video',
                'media',
                'creative',
                'ui',
                'ux',
                'user interface',
                'user experience',
                'graphic design',
                'video editing',
                'animation',
                '3d',
                '2d',
                'illustration',
                'digital content',
                'marketing design',
                'photo editing'
            ],

            // Bachelor of Computer Science (Cyber Security)
            'cyber security' => [
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
                'zero trust'
            ],
            'cybersecurity' => [
                'cybersecurity',
                'information security',
                'protection',
                'malware',
                'security',
                'cyber',
                'penetration testing',
                'firewall',
                'encryption',
                'network security',
                'malware',
                'cyber attack',
                'risk',
                'vulnerability',
                'threat',
                'incident',
                'security operation',
                'soc',
                'forensics',
                'intrusion detection',
                'identity management'
            ],

            // Diploma in Computer Science
            'diploma computer science' => [
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
            ],

        ];

        // Check for program match (order matters - more specific first)
        // 1. Software Engineer
        if (str_contains($programLower, 'software engineer')) {
            $keywords = $programMappings['software engineer'];
        }
        // 2. Computer Systems & Networking
        elseif (str_contains($programLower, 'computer systems') || str_contains($programLower, 'systems & networking')) {
            $keywords = array_merge(
                $programMappings['computer systems'] ?? [],
                $programMappings['networking'] ?? []
            );
        }
        // 3. Multimedia Software
        elseif (str_contains($programLower, 'multimedia software') || str_contains($programLower, 'multimedia')) {
            $keywords = array_merge(
                $programMappings['multimedia software'] ?? [],
                $programMappings['multimedia'] ?? []
            );
        }
        // 4. Cyber Security
        elseif (str_contains($programLower, 'cyber security') || str_contains($programLower, 'cybersecurity')) {
            $keywords = array_merge(
                $programMappings['cyber security'] ?? [],
                $programMappings['cybersecurity'] ?? []
            );
        }
        // 5. Diploma in Computer Science
        elseif (str_contains($programLower, 'diploma') && str_contains($programLower, 'computer science')) {
            $keywords = $programMappings['diploma computer science'];
        }

        // Fallback: Check for general computer science keywords
        elseif (str_contains($programLower, 'computer science')) {
            // Add general computer science keywords
            $keywords = [
                'computer',
                'software',
                'programming',
                'development',
                'system',
                'application',
                'technology',
                'it',
                'coding',
                'algorithm'
            ];
        }

        // Remove duplicates and return
        return array_unique($keywords);
    }

    /**
     * Get jobscope factor text
     */
    private function getJobscopeFactorText($score, $student, $application): string
    {
        if ($score >= 35) {
            return "✅ Jobscope matches your program ({$student->program}) excellently (+{$score} points)";
        } elseif ($score >= 25) {
            return "✅ Jobscope matches your program ({$student->program}) well (+{$score} points)";
        } elseif ($score >= 15) {
            return "⚠️ Jobscope partially matches your program ({$student->program}) (+{$score} points)";
        } elseif ($score > 0) {
            return "⚠️ Jobscope has minimal match with your program ({$student->program}) (+{$score} points)";
        } else {
            return "❌ Jobscope does not match your program ({$student->program}) (0 points)";
        }
    }

    /**
     * Calculate distance score (20 points max)
     */
    private function calculateDistanceScore($student, $application): float
    {
        if (!$student->latitude || !$student->longitude || !$application->companyLatitude || !$application->companyLongitude) {
            return 0;
        }

        $distance = $this->getGeocodingService()->calculateDistance(
            (float) $student->latitude,
            (float) $student->longitude,
            (float) $application->companyLatitude,
            (float) $application->companyLongitude
        );

        // Score based on distance (closer = higher score)
        if ($distance <= 10) {
            return 20; // Very close (0-10 km)
        } elseif ($distance <= 25) {
            return 15; // Close (10-25 km)
        } elseif ($distance <= 50) {
            return 10; // Moderate (25-50 km)
        } elseif ($distance <= 100) {
            return 5; // Far (50-100 km)
        } elseif ($distance <= 500) {
            return 3; // Long-distance (100-500 km)
        }
        return 1; // Very far (>500 km) but still willing
    }

    /**
     * Get distance factor text
     */
    private function getDistanceFactorText($score, $student, $application): string
    {
        if (!$student->latitude || !$student->longitude || !$application->companyLatitude || !$application->companyLongitude) {
            return "⚠️ Distance cannot be calculated (location data missing) (0 points)";
        }

        $distance = $this->getGeocodingService()->calculateDistance(
            (float) $student->latitude,
            (float) $student->longitude,
            (float) $application->companyLatitude,
            (float) $application->companyLongitude
        );

        $distanceRounded = round($distance, 1);

        if ($score >= 15) {
            return "✅ Company is very close to your address ({$distanceRounded} km) (+{$score} points)";
        } elseif ($score >= 10) {
            return "✅ Company is moderately close to your address ({$distanceRounded} km) (+{$score} points)";
        } elseif ($score >= 5) {
            return "⚠️ Company is far from your address ({$distanceRounded} km) (+{$score} points)";
        } elseif ($score >= 1) {
            return "⚠️ Company is quite far from your address ({$distanceRounded} km) (+{$score} points)";
        } else {
            return "❌ Company is extremely far from your address ({$distanceRounded} km) (0 points)";
        }
    }

    /**
     * Calculate company history score (20 points max)
     */
    private function calculateCompanyHistoryScore($application): float
    {
        $companyAcceptanceCount = PlacementApplication::where('companyName', $application->companyName)
            ->where('studentAcceptance', 'Accepted')
            ->count();

        if ($companyAcceptanceCount >= 10) {
            return 20;
        } elseif ($companyAcceptanceCount >= 5) {
            return 15;
        } elseif ($companyAcceptanceCount >= 2) {
            return 10;
        } elseif ($companyAcceptanceCount > 0) {
            return 5;
        }

        return 0;
    }

    /**
     * Get company history factor text
     */
    private function getCompanyHistoryFactorText($score, $application): string
    {
        $companyAcceptanceCount = PlacementApplication::where('companyName', $application->companyName)
            ->where('studentAcceptance', 'Accepted')
            ->count();

        if ($score >= 15) {
            return "✅ Company has accepted {$companyAcceptanceCount} UMP students (+{$score} points)";
        } elseif ($score >= 10) {
            return "✅ Company has accepted {$companyAcceptanceCount} UMP students (+{$score} points)";
        } elseif ($score >= 5) {
            return "⚠️ Company has accepted {$companyAcceptanceCount} UMP student(s) (+{$score} points)";
        } else {
            return "❌ Company has no UMP student history (0 points)";
        }
    }

    /**
     * Calculate allowance score (20 points max)
     */
    private function calculateAllowanceScore($application): float
    {
        if (!$application->allowance) {
            return 0;
        }

        // Get average allowance for this company
        $avgAllowance = PlacementApplication::where('companyName', $application->companyName)
            ->where('studentAcceptance', 'Accepted')
            ->whereNotNull('allowance')
            ->avg('allowance');

        if (!$avgAllowance) {
            // No historical data, score based on absolute value
            if ($application->allowance >= 1000) {
                return 20;
            } elseif ($application->allowance >= 700) {
                return 15;
            } elseif ($application->allowance >= 400) {
                return 10;
            } else {
                return 5;
            }
        }

        // Score based on comparison with average
        $ratio = $application->allowance / $avgAllowance;

        if ($ratio >= 1.2) {
            return 20; // 20%+ above average
        } elseif ($ratio >= 1.1) {
            return 18; // 10-20% above average
        } elseif ($ratio >= 1.0) {
            return 15; // At or slightly above average
        } elseif ($ratio >= 0.9) {
            return 12; // Slightly below average
        } elseif ($ratio >= 0.8) {
            return 8; // Below average
        } else {
            return 5; // Significantly below average
        }
    }

    /**
     * Get allowance factor text
     */
    private function getAllowanceFactorText($score, $application): string
    {
        if (!$application->allowance) {
            return "⚠️ No allowance information (0 points)";
        }

        $avgAllowance = PlacementApplication::where('companyName', $application->companyName)
            ->where('studentAcceptance', 'Accepted')
            ->whereNotNull('allowance')
            ->avg('allowance');

        $allowanceFormatted = "RM " . number_format($application->allowance, 2);

        if ($avgAllowance) {
            $ratio = $application->allowance / $avgAllowance;
            if ($ratio >= 1.2) {
                return "✅ Allowance is significantly above average ({$allowanceFormatted}) (+{$score} points)";
            } elseif ($ratio >= 1.0) {
                return "✅ Allowance is at or above average ({$allowanceFormatted}) (+{$score} points)";
            } elseif ($ratio >= 0.9) {
                return "⚠️ Allowance is slightly below average ({$allowanceFormatted}) (+{$score} points)";
            } else {
                return "⚠️ Allowance is below average ({$allowanceFormatted}) (+{$score} points)";
            }
        } else {
            // No historical data
            if ($score >= 15) {
                return "✅ Allowance is competitive ({$allowanceFormatted}) (+{$score} points)";
            } elseif ($score >= 10) {
                return "✅ Allowance is moderate ({$allowanceFormatted}) (+{$score} points)";
            } else {
                return "⚠️ Allowance is low ({$allowanceFormatted}) (+{$score} points)";
            }
        }
    }

    private function getRating($score): string
    {
        if ($score >= 80) return "Excellent - Highly Suitable⭐⭐⭐⭐⭐";
        if ($score >= 60) return "Good - Suitable ⭐⭐⭐⭐";
        if ($score >= 40) return "Fair - Moderately Suitable ⭐⭐⭐";
        if ($score >= 20) return "Poor - Less Suitable ⭐⭐";
        return "Very Poor - Not Suitable ⭐";
    }

    private function getRecommendation($score, $factors): string
    {
        if ($score >= 80) {
            return "✅ **Highly Recommended** - This placement demonstrates excellent alignment with your preferences and qualifications.";
        } elseif ($score >= 60) {
            return "✅ **Recommended** - This placement is suitable and meets most of your criteria.";
        } elseif ($score >= 40) {
            return "⚠️ **Moderately Recommended** - This placement meets some criteria but has certain limitations to review.";
        } elseif ($score >= 20) {
            return "⚠️ **Consider Carefully** - This placement has notable issues, review carefully before proceeding.";
        } else {
            return "❌ **Not Recommended** - This placement does not sufficiently match your requirements. Explore other options.";
        }
    }

    /**
     * Perform quick evaluation for summary list (just score and rating, no detailed factors)
     */
    private function performQuickEvaluation($student, $application): array
    {
        $score = 0;

        // 1. Jobscope Match (40 points - 40%)
        $score += $this->calculateJobscopeMatch($student, $application);

        // 2. Distance (20 points - 20%)
        $score += $this->calculateDistanceScore($student, $application);

        // 3. Company History (20 points - 20%)
        $score += $this->calculateCompanyHistoryScore($application);

        // 4. Allowance (20 points - 20%)
        $score += $this->calculateAllowanceScore($application);

        $rating = $this->getRating($score);
        $recommendation = $this->getQuickRecommendation($score);

        return [
            'score' => $score,
            'rating' => $rating,
            'recommendation' => $recommendation
        ];
    }

    /**
     * Get quick recommendation text for summary list
     */
    private function getQuickRecommendation($score): string
    {
        if ($score >= 80) {
            return "Strongly Recommended!";
        } elseif ($score >= 60) {
            return "Recommended";
        } elseif ($score >= 40) {
            return "Consider with Caution";
        } else {
            return "Not Recommended";
        }
    }

    private function checkStatus($student): string
    {
        $statusInfo = [];

        // Course Verification Status
        $courseVerification = $student->courseVerifications()
            ->orderBy('applicationDate', 'desc')
            ->first();

        if ($courseVerification) {
            $statusInfo[] = "📋 **Course Verification:** {$courseVerification->status}";
        } else {
            $statusInfo[] = "📋 **Course Verification:** Not submitted";
        }

        // Placement Applications
        $applications = $student->placementApplications()
            ->orderBy('applicationDate', 'desc')
            ->get();

        if ($applications->count() > 0) {
            $statusInfo[] = "\n💼 **Placement Applications:** {$applications->count()} total";
            foreach ($applications->take(3) as $app) {
                $status = $app->overall_status;
                $studentAcceptance = $app->studentAcceptance ? " (Acceptance: {$app->studentAcceptance})" : "";
                $statusInfo[] = "   • {$app->companyName}: {$status}{$studentAcceptance}";
            }
        } else {
            $statusInfo[] = "\n💼 **Placement Applications:** None submitted";
        }

        // Supervisor Assignment
        $supervisorAssignment = $student->supervisorAssignment;
        if ($supervisorAssignment) {
            $supervisor = $supervisorAssignment->supervisor;
            $statusInfo[] = "\n👨‍🏫 **Supervisor:** {$supervisor->user->name} (Assigned)";
        } else {
            $statusInfo[] = "\n👨‍🏫 **Supervisor:** Not assigned yet";
        }

        return implode("\n", $statusInfo);
    }

    private function getCompanyInsights($message): string
    {
        $companyName = $this->extractCompanyName($message);

        if (!$companyName) {
            return "Please provide a company name. Example: 'Tell me about Maybank' or 'Company insights for Grab'";
        }

        $companyStats = PlacementApplication::where('companyName', 'like', '%' . $companyName . '%')
            ->where('studentAcceptance', 'Accepted')
            ->select(
                'companyName',
                DB::raw('COUNT(*) as total_students'),
                DB::raw('AVG(allowance) as avg_allowance'),
                DB::raw('MIN(applicationDate) as first_accepted'),
                DB::raw('MAX(applicationDate) as last_accepted')
            )
            ->groupBy('companyName')
            ->first();

        if (!$companyStats) {
            return "I couldn't find information about '$companyName'. This company may not have accepted any UMP students yet.";
        }

        $positions = PlacementApplication::where('companyName', $companyStats->companyName)
            ->where('studentAcceptance', 'Accepted')
            ->select('position', DB::raw('COUNT(*) as count'))
            ->groupBy('position')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $insights = "💼 **Company Insights: {$companyStats->companyName}**\n\n";
        $insights .= "📊 **Statistics:**\n";
        $insights .= "• Total UMP Students Accepted: {$companyStats->total_students}\n";

        if ($companyStats->avg_allowance) {
            $insights .= "• Average Allowance: RM " . number_format($companyStats->avg_allowance, 2) . "\n";
        }

        if ($companyStats->first_accepted) {
            $insights .= "• First Partnership: " . \Carbon\Carbon::parse($companyStats->first_accepted)->format('Y') . "\n";
        }

        if ($companyStats->last_accepted) {
            $insights .= "• Last Accepted: " . \Carbon\Carbon::parse($companyStats->last_accepted)->format('M Y') . "\n";
        }

        if ($positions->count() > 0) {
            $insights .= "\n📋 **Common Positions:**\n";
            foreach ($positions as $position) {
                $insights .= "• {$position->position} ({$position->count} students)\n";
            }
        }

        return $insights;
    }

    private function compareCompanies($message): string
    {
        // Extract company names from message
        $companies = $this->extractCompanyNames($message);

        if (count($companies) < 2) {
            return "Please provide at least 2 company names to compare.\n\n" .
                "Example: 'Compare Maybank and Grab' or 'Compare Maybank vs Grab'";
        }

        $comparison = "📊 **Company Comparison**\n\n";

        foreach ($companies as $companyName) {
            $stats = PlacementApplication::where('companyName', 'like', '%' . $companyName . '%')
                ->where('studentAcceptance', 'Accepted')
                ->select(
                    DB::raw('COUNT(*) as total_students'),
                    DB::raw('AVG(allowance) as avg_allowance')
                )
                ->first();

            if ($stats) {
                $comparison .= "**{$companyName}:**\n";
                $comparison .= "• Students Accepted: {$stats->total_students}\n";
                if ($stats->avg_allowance) {
                    $comparison .= "• Avg Allowance: RM " . number_format($stats->avg_allowance, 2) . "\n";
                }
            } else {
                $comparison .= "**{$companyName}:** No data available\n";
            }
            $comparison .= "\n";
        }

        return $comparison;
    }

    private function answerFAQ($message): string
    {
        $messageLower = strtolower($message);

        $faqs = [
            [
                'key' => 'requirement',
                'title' => 'Placement Requirements',
                'answer' => "**Placement Requirements:**\n\n" .
                    "1. ✅ Approved course verification\n" .
                    "2. ✅ Complete placement application form\n" .
                    "3. ✅ Submit required documents\n" .
                    "4. ✅ Wait for committee and coordinator approval"
            ],
            [
                'key' => 'document',
                'title' => 'Required Documents',
                'answer' => "**Required Documents:**\n\n" .
                    "📄 **For Placement Application:**\n" .
                    "• Offer letter from company\n" .
                    "• Acceptance form\n" .
                    "• Any other documents specified by the company\n\n" .
                    "📄 **For Course Verification:**\n" .
                    "• Course registration document\n" .
                    "• Transcript or academic record\n" .
                    "• Any other supporting documents"
            ],
            [
                'key' => 'apply',
                'title' => 'How to Apply',
                'answer' => "**How to Apply for Placement:**\n\n" .
                    "1. 📋 Go to 'Internship Placement' page in the navigation menu\n" .
                    "2. ➕ Click 'New Application' button\n" .
                    "3. ✍️ Fill in all company details (name, address, contact, etc.)\n" .
                    "4. 📎 Upload required documents (offer letter, acceptance form)\n" .
                    "5. ✅ Submit your application\n\n" .
                    "**Note:** Your application will be reviewed by the committee and coordinator."
            ],
            [
                'key' => 'supervisor',
                'title' => 'Supervisor Assignment',
                'answer' => "**Supervisor Assignment:**\n\n" .
                    "👨‍🏫 **When are supervisors assigned?**\n" .
                    "Supervisors are automatically assigned after you accept a placement offer.\n\n" .
                    "⚙️ **How are supervisors selected?**\n" .
                    "The system automatically considers:\n" .
                    "• Distance between supervisor and company location\n" .
                    "• Supervisor's current workload (quota)\n" .
                    "• Supervisor's preferences and availability\n\n" .
                    "📧 **Notification:**\n" .
                    "You will be notified via email once a supervisor is assigned to you."
            ],
            [
                'key' => 'time',
                'title' => 'Processing Time',
                'answer' => "**Processing Time:**\n\n" .
                    "📋 **Course Verification:**\n" .
                    "• Usually takes 1-2 weeks\n" .
                    "• Depends on lecturer availability\n\n" .
                    "💼 **Placement Application:**\n" .
                    "• Committee review: Usually 1-2 weeks\n" .
                    "• Coordinator review: Usually 1-2 weeks\n" .
                    "• Total: Usually 2-4 weeks\n\n" .
                    "👨‍🏫 **Supervisor Assignment:**\n" .
                    "• Usually assigned within 1 week after accepting placement\n" .
                    "• May vary depending on supervisor availability"
            ],
        ];

        // Store FAQs for number-based selection
        $this->storedFAQs = $faqs;

        // Check for specific FAQ keywords (order matters - more specific first)
        if (str_contains($messageLower, 'requirement') || str_contains($messageLower, 'what do i need')) {
            return $faqs[0]['answer'] . "\n\n" . $this->getFAQNavigationOptions();
        }

        if (str_contains($messageLower, 'document') || str_contains($messageLower, 'what document') || str_contains($messageLower, 'file')) {
            return $faqs[1]['answer'] . "\n\n" . $this->getFAQNavigationOptions();
        }

        if (str_contains($messageLower, 'how to apply') || str_contains($messageLower, 'how do i apply') || str_contains($messageLower, 'how can i apply')) {
            return $faqs[2]['answer'] . "\n\n" . $this->getFAQNavigationOptions();
        }

        if (str_contains($messageLower, 'supervisor') || str_contains($messageLower, 'supervisor assignment') || str_contains($messageLower, 'when will i get')) {
            return $faqs[3]['answer'] . "\n\n" . $this->getFAQNavigationOptions();
        }

        if (str_contains($messageLower, 'processing time') || str_contains($messageLower, 'how long') || str_contains($messageLower, 'how much time')) {
            return $faqs[4]['answer'] . "\n\n" . $this->getFAQNavigationOptions();
        }

        // If no specific FAQ matched, show FAQ menu with numbers
        return $this->showFAQMenu();
    }

    /**
     * Show FAQ menu with numbered options
     */
    private function showFAQMenu(): string
    {
        $menu = "**Frequently Asked Questions:**\n\n";
        $menu .= "Please select a number to view the answer:\n\n";

        $icons = ['📋', '📄', '💼', '👨‍🏫', '⏱️'];
        $titles = [
            'Placement Requirements',
            'Required Documents',
            'How to Apply',
            'Supervisor Assignment',
            'Processing Time'
        ];

        foreach ($this->storedFAQs as $index => $faq) {
            $menu .= "**" . ($index + 1) . ". {$icons[$index]} {$faq['title']}**\n";
        }

        $menu .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $menu .= "💬 **To view an answer, type the number (e.g., 1, 2, 3, 4, or 5)**\n";
        $menu .= "Or type **'back'** to return to main menu";

        return $menu;
    }

    /**
     * Show FAQ answer based on number selection
     */
    private function showFAQAnswer($number): string
    {
        if (empty($this->storedFAQs)) {
            return "I don't have the FAQ list. Please say 'FAQs' first to see the options.";
        }

        // Number is 1-indexed, array is 0-indexed
        $index = $number - 1;

        if (!isset($this->storedFAQs[$index])) {
            $maxNumber = count($this->storedFAQs);
            return "Invalid selection. Please choose a number between 1 and {$maxNumber}.";
        }

        $faq = $this->storedFAQs[$index];
        return $faq['answer'] . "\n\n" . $this->getFAQNavigationOptions();
    }

    /**
     * Get navigation options after showing FAQ answer
     */
    private function getFAQNavigationOptions(): string
    {
        $options = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $options .= "💬 **What would you like to do next?**\n\n";

        if (!empty($this->storedFAQs)) {
            $maxNumber = count($this->storedFAQs);
            $options .= "• Type **1** to **{$maxNumber}** to view another FAQ\n";
        }

        $options .= "• Type **'back'** or **'menu'** to return to FAQ menu\n";
        $options .= "• Type **'exit'** to return to main menu";

        return $options;
    }

    /**
     * Show summary list of all applications
     */
    private function showSummaryList($student): string
    {
        $applications = $student->placementApplications()
            ->orderBy('applicationDate', 'desc')
            ->get();

        $response = "📊 **Your Placement Applications Summary** ({$applications->count()} applications)\n\n";

        $summaries = [];
        foreach ($applications as $index => $application) {
            $summary = $this->performQuickEvaluation($student, $application);
            $summaries[] = [
                'index' => $index + 1,
                'company' => $application->companyName,
                'date' => $application->applicationDate->format('M d, Y'),
                'score' => $summary['score'],
                'rating' => $summary['rating'],
                'recommendation' => $summary['recommendation'],
                'application' => $application
            ];
        }

        // Sort by score (highest first)
        usort($summaries, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Store summaries for later reference
        $this->storedSummaries = $summaries;
        $this->viewingDetails = false;

        $response .= "**Ranked by Score:**\n\n";
        foreach ($summaries as $rank => $summary) {
            $response .= "**#" . ($rank + 1) . " - {$summary['company']}**\n";
            $response .= "📅 Applied: {$summary['date']}\n";
            $response .= "⭐ Score: {$summary['score']}/100 ({$summary['rating']})\n";
            $response .= "💡 {$summary['recommendation']}\n\n";
        }

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $response .= "💬 **Want more details?** Type:\n";
        $response .= "• **1** (to see details of #1)\n";
        $response .= "• **2** (to see details of #2)\n";
        $response .= "• Type **'back'** to return to main menu\n";

        return $response;
    }

    /**
     * Show details for a specific application by number
     */
    private function showApplicationDetails($student, $number): string
    {
        if (empty($this->storedSummaries)) {
            return "I don't have your application list. Please say 'Evaluate Placement' first.";
        }

        // Number is 1-indexed, array is 0-indexed
        $index = $number - 1;

        if (!isset($this->storedSummaries[$index])) {
            $maxNumber = count($this->storedSummaries);
            return "Invalid selection. Please choose a number between #1 and #{$maxNumber}.";
        }

        $summary = $this->storedSummaries[$index];
        $application = $summary['application'];

        $this->viewingDetails = true;

        $evaluation = $this->performEvaluation($student, $application);
        $evaluation .= "\n\n" . $this->getNavigationOptions();

        return $evaluation;
    }

    /**
     * Get navigation options after showing details
     */
    private function getNavigationOptions(): string
    {
        $options = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $options .= "💬 **What would you like to do next?**\n\n";

        if (!empty($this->storedSummaries)) {
            $maxNumber = count($this->storedSummaries);
            $options .= "• Type **1** to **{$maxNumber}** to view another application's details\n";
            $options .= "• Type **'back'** to return to summary list\n";
        }
        $options .= "• Or ask me something else!";

        return $options;
    }

    /**
     * Extract number selection from message (e.g., "#1", "show #1", "details for #1")
     */
    private function extractNumberSelection($message): ?int
    {
        // Pattern: # followed by a number
        if (preg_match('/#(\d+)/', $message, $matches)) {
            return (int) $matches[1];
        }

        // Pattern: "show 1", "details for 1", "number 1"
        if (preg_match('/(?:show|details|view|number|select|choose)\s+(\d+)/i', $message, $matches)) {
            return (int) $matches[1];
        }

        // Pattern: just a number at the start or end
        if (preg_match('/^\s*(\d+)\s*$/', trim($message), $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function extractCompanyName($message): ?string
    {
        // Common company names in Malaysia
        $companies = ['maybank', 'grab', 'shopee', 'intel', 'samsung', 'microsoft', 'google', 'ibm', 'hp', 'dell'];

        foreach ($companies as $company) {
            if (str_contains($message, $company)) {
                return ucfirst($company);
            }
        }

        // Try to extract from patterns like "at [Company]" or "for [Company]"
        if (preg_match('/(?:at|for|about|company|placement)\s+([A-Za-z\s]+)/i', $message, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function extractCompanyNames($message): array
    {
        $companies = [];

        // Try to find "vs" or "and" patterns
        if (preg_match('/([A-Za-z\s]+)\s+(?:vs|and|&)\s+([A-Za-z\s]+)/i', $message, $matches)) {
            $companies[] = trim($matches[1]);
            $companies[] = trim($matches[2]);
        }

        return $companies;
    }

    public function render()
    {
        return view('livewire.placement-chatbot');
    }
}
