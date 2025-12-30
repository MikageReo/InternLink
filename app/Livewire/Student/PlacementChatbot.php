<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\PlacementApplication;
use App\Models\Student;
use App\Models\SupervisorAssignment;
use App\Models\CourseVerification;
use App\Models\PeerTip;
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
    public $messageButtons = []; // Stores buttons for each message

    // Input fields for peer tips
    public $tipNickname = '';
    public $tipContent = '';
    public $showingTipForm = false;

    // Game state
    public $currentGame = null; // 'trivia', 'quiz', 'word'
    public $currentGameData = null; // Stores current game question/word
    public $gameAnswer = ''; // User's answer input
    public $showingGameAnswer = false; // Whether to show the real answer

    protected function getGeocodingService(): GeocodingService
    {
        return app(GeocodingService::class);
    }

    public function mount()
    {
        // Initialize with welcome message
        $messageIndex = count($this->messages);
        $this->messages[] = [
            'type' => 'bot',
            'content' => "Hello! I'm Lia, your InternLink Assistant. How can I help you today?",
            'timestamp' => now()
        ];

        // Set up buttons for welcome message
        $this->messageButtons[$messageIndex] = [
            ['label' => '📊 Evaluate Placement', 'action' => 'evaluate_placement'],
            ['label' => '📋 Check Status', 'action' => 'check_status'],
            ['label' => '❓ FAQs', 'action' => 'faqs'],
            ['label' => '🎤 Interview Prep', 'action' => 'interview_prep'],
            ['label' => '💡 Daily Tip', 'action' => 'daily_tip'],
            ['label' => '👥 Peer Support', 'action' => 'peer_support'],
            ['label' => '🎮 Mini Games', 'action' => 'mini_games']
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        // This method is now disabled - users can only use buttons
        return;
    }

    public function quickAction($action)
    {
        // Legacy method - redirect to buttonAction
        $this->buttonAction($action);
    }

    public function buttonAction($action, $data = null)
    {
        // Convert data to proper type if it's a string
        if ($data === 'null' || $data === null || $data === '') {
            $data = null;
        } else {
            $data = is_numeric($data) ? (int)$data : $data;
        }

        // Add user message showing what button was clicked
        $buttonLabel = $this->getButtonLabel($action, $data);
        $this->messages[] = [
            'type' => 'user',
            'content' => $buttonLabel,
            'timestamp' => now()
        ];

        $this->isTyping = true;

        // Process button action and get response
        $response = $this->processButtonAction($action, $data);

        $this->isTyping = false;

        // Add bot response with buttons
        $messageIndex = count($this->messages);
        $this->messages[] = [
            'type' => 'bot',
            'content' => $response['content'],
            'timestamp' => now()
        ];

        // Store buttons for this message
        if (!empty($response['buttons'])) {
            $this->messageButtons[$messageIndex] = $response['buttons'];
        }

        // Scroll to bottom after response
        $this->dispatch('scroll-to-bottom');
    }

    private function getButtonLabel($action, $data = null): string
    {
        $labels = [
            'evaluate_placement' => '📊 Evaluate Placement',
            'check_status' => '📋 Check Status',
            'faqs' => '❓ FAQs',
            'back_to_menu' => '🏠 Back to Menu',
            'view_application' => 'View Application',
            'view_faq' => 'View FAQ',
            'back_to_faq_menu' => 'Back to FAQ Menu',
            'back_to_summary' => 'Back to Summary',
            'view_another' => 'View Another Application',
            // Interview prep sub-actions
            'interview_tips' => '💡 Interview Tips',
            'common_questions' => '❓ Common Questions',
            'practice_questions' => '📝 Practice Questions',
            'interview_do_dont' => '✅ Do\'s & Don\'ts',
            // Mini games
            'game_trivia' => '🎲 Trivia Game',
            'game_quiz' => '🧠 Quick Quiz',
            'game_word' => '🔤 Word Game',
            // Peer support
            'view_tips' => '💬 View Tips',
            'share_tip' => '✍️ Share a Tip',
            'submit_tip' => '✅ Submit Tip',
            'submit_game_answer' => '✅ Submit Answer'
        ];

        if ($data && isset($labels[$action])) {
            return $labels[$action] . ($data ? " #{$data}" : '');
        }

        return $labels[$action] ?? $action;
    }

    private function processButtonAction($action, $data = null): array
    {
        $student = Auth::user()->student;

        if (!$student) {
            return [
                'content' => "I couldn't find your student profile. Please contact the administrator.",
                'buttons' => [['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']]
            ];
        }

        switch ($action) {
            case 'evaluate_placement':
                return $this->handleEvaluatePlacement($student);

            case 'check_status':
                return [
                    'content' => $this->checkStatus($student),
                    'buttons' => [['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']]
                ];

            case 'faqs':
                return $this->handleFAQs();

            case 'interview_prep':
                return $this->handleInterviewPrep();

            case 'daily_tip':
                return $this->handleDailyTip();

            case 'peer_support':
                return $this->handlePeerSupport();

            case 'mini_games':
                return $this->handleMiniGames();

                // Interview prep sub-actions
            case 'interview_tips':
                return $this->handleInterviewTips();

            case 'common_questions':
                return $this->handleCommonQuestions();

            case 'practice_questions':
                return $this->handlePracticeQuestions();

            case 'interview_do_dont':
                return $this->handleInterviewDoDont();

                // Mini games sub-actions
            case 'game_trivia':
                return $this->handleTriviaGame();

            case 'game_quiz':
                return $this->handleQuickQuiz();

            case 'game_word':
                return $this->handleWordGame();

            case 'submit_game_answer':
                return $this->handleSubmitGameAnswer();

                // Peer support sub-actions
            case 'view_tips':
                return $this->handleViewPeerTips();

            case 'share_tip':
                return $this->handleShareTip();

            case 'submit_tip':
                return $this->handleSubmitTip();

                // Application viewing
            case 'view_application':
                return $this->handleViewApplication($student, $data);

                // FAQ viewing
            case 'view_faq':
                return $this->handleViewFAQ($data);

                // Navigation
            case 'back_to_summary':
                return $this->handleBackToSummary($student);

            case 'back_to_faq_menu':
                return $this->handleFAQs();

            case 'back_to_menu':
                return $this->handleBackToMenu();

            default:
                return [
                    'content' => "I'm not sure what you want to do. Please select an option.",
                    'buttons' => $this->getMainMenuButtons()
                ];
        }
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

    private function handleEvaluatePlacement($student): array
    {
        // Get ALL student's applications
        $applications = $student->placementApplications()
            ->orderBy('applicationDate', 'desc')
            ->get();

        if ($applications->isEmpty()) {
            return [
                'content' => "I couldn't find any placements to evaluate. Please apply for a placement first.",
                'buttons' => [['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']]
            ];
        }

        // If only one application, show full details
        if ($applications->count() === 1) {
            $this->viewingDetails = true;
            $evaluation = $this->performEvaluation($student, $applications->first());
            return [
                'content' => $evaluation,
                'buttons' => [
                    ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
                ]
            ];
        }

        // Multiple applications - show summary list with buttons
        return $this->showSummaryListWithButtons($student);
    }

    private function handleViewApplication($student, $number): array
    {
        if (empty($this->storedSummaries)) {
            return [
                'content' => "I don't have your application list. Please click 'Evaluate Placement' first.",
                'buttons' => [['label' => '📊 Evaluate Placement', 'action' => 'evaluate_placement']]
            ];
        }

        if ($number === null || !is_numeric($number)) {
            return [
                'content' => "Please select a valid application number.",
                'buttons' => $this->getApplicationListButtons()
            ];
        }

        $index = (int)$number - 1;
        if (!isset($this->storedSummaries[$index])) {
            $maxNumber = count($this->storedSummaries);
            return [
                'content' => "Invalid selection. Please choose a number between #1 and #{$maxNumber}.",
                'buttons' => $this->getApplicationListButtons()
            ];
        }

        $summary = $this->storedSummaries[$index];
        $application = $summary['application'];
        $this->viewingDetails = true;

        $evaluation = $this->performEvaluation($student, $application);
        return [
            'content' => $evaluation,
            'buttons' => [
                ['label' => '📋 Back to Summary', 'action' => 'back_to_summary'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleFAQs(): array
    {
        $faqs = [
            [
                'key' => 'requirement',
                'title' => 'Placement Requirements',
                'answer' => "Placement Requirements:\n\n" .
                    "1. ✅ Approved course verification\n" .
                    "2. ✅ Complete placement application form\n" .
                    "3. ✅ Submit required documents\n" .
                    "4. ✅ Wait for committee and coordinator approval"
            ],
            [
                'key' => 'document',
                'title' => 'Required Documents',
                'answer' => "Required Documents:\n\n" .
                    "📄 For Placement Application:\n" .
                    "• Offer letter from company\n" .
                    "• Acceptance form\n" .
                    "• Any other documents specified by the company\n\n" .
                    "📄 For Course Verification:\n" .
                    "• Course registration document\n" .
                    "• Transcript or academic record\n" .
                    "• Any other supporting documents"
            ],
            [
                'key' => 'apply',
                'title' => 'How to Apply',
                'answer' => "How to Apply for Placement:\n\n" .
                    "1. 📋 Go to 'Internship Placement' page in the navigation menu\n" .
                    "2. ➕ Click 'New Application' button\n" .
                    "3. ✍️ Fill in all company details (name, address, contact, etc.)\n" .
                    "4. 📎 Upload required documents (offer letter, acceptance form)\n" .
                    "5. ✅ Submit your application\n\n" .
                    "Note: Your application will be reviewed by the committee and coordinator."
            ],
            [
                'key' => 'supervisor',
                'title' => 'Supervisor Assignment',
                'answer' => "Supervisor Assignment:\n\n" .
                    "👨‍🏫 When are supervisors assigned?\n" .
                    "Supervisors are automatically assigned after you accept a placement offer.\n\n" .
                    "⚙️ How are supervisors selected?\n" .
                    "The system automatically considers:\n" .
                    "• Distance between supervisor and company location\n" .
                    "• Supervisor's current workload (quota)\n" .
                    "• Supervisor's preferences and availability\n\n" .
                    "📧 Notification:\n" .
                    "You will be notified via email once a supervisor is assigned to you."
            ],
            [
                'key' => 'time',
                'title' => 'Processing Time',
                'answer' => "Processing Time:\n\n" .
                    "📋 Course Verification:\n" .
                    "• Usually takes 1-2 weeks\n" .
                    "• Depends on lecturer availability\n\n" .
                    "💼 Placement Application:\n" .
                    "• Committee review: Usually 1-2 weeks\n" .
                    "• Coordinator review: Usually 1-2 weeks\n" .
                    "• Total: Usually 2-4 weeks\n\n" .
                    "👨‍🏫 Supervisor Assignment:\n" .
                    "• Usually assigned within 1 week after accepting placement\n" .
                    "• May vary depending on supervisor availability"
            ],
        ];

        $this->storedFAQs = $faqs;
        $this->faqMenuShown = true;

        $menu = "Frequently Asked Questions:\n\n";
        $menu .= "Please click a button below to view the answer:\n\n";

        $icons = ['📋', '📄', '💼', '👨‍🏫', '⏱️'];
        foreach ($faqs as $index => $faq) {
            $menu .= "" . ($index + 1) . ". {$icons[$index]} {$faq['title']}\n";
        }

        $buttons = [];
        foreach ($faqs as $index => $faq) {
            $buttons[] = [
                'label' => ($index + 1) . '. ' . $faq['title'],
                'action' => 'view_faq',
                'data' => $index + 1
            ];
        }
        $buttons[] = ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu'];

        return [
            'content' => $menu,
            'buttons' => $buttons
        ];
    }

    private function handleViewFAQ($number): array
    {
        if (empty($this->storedFAQs)) {
            return [
                'content' => "I don't have the FAQ list. Please click 'FAQs' first.",
                'buttons' => [['label' => '❓ FAQs', 'action' => 'faqs']]
            ];
        }

        if ($number === null || !is_numeric($number)) {
            return [
                'content' => "Please select a valid FAQ number.",
                'buttons' => $this->getFAQMenuButtons()
            ];
        }

        $index = (int)$number - 1;
        if (!isset($this->storedFAQs[$index])) {
            $maxNumber = count($this->storedFAQs);
            return [
                'content' => "Invalid selection. Please choose a number between 1 and {$maxNumber}.",
                'buttons' => $this->getFAQMenuButtons()
            ];
        }

        $faq = $this->getFAQByIndex($index);
        return [
            'content' => $faq['answer'],
            'buttons' => [
                ['label' => '📋 Back to FAQ Menu', 'action' => 'back_to_faq_menu'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleBackToMenu(): array
    {
        $this->viewingDetails = false;
        $this->storedSummaries = [];
        $this->faqMenuShown = false;
        $this->storedFAQs = [];

        // Reset game and tip form states
        $this->currentGame = null;
        $this->currentGameData = null;
        $this->gameAnswer = '';
        $this->showingGameAnswer = false;
        $this->showingTipForm = false;
        $this->tipNickname = '';
        $this->tipContent = '';

        return [
            'content' => "Welcome back! What would you like to do?",
            'buttons' => [
                ['label' => '📊 Evaluate Placement', 'action' => 'evaluate_placement'],
                ['label' => '📋 Check Status', 'action' => 'check_status'],
                ['label' => '❓ FAQs', 'action' => 'faqs'],
                ['label' => '🎤 Interview Prep', 'action' => 'interview_prep'],
                ['label' => '💡 Daily Tip', 'action' => 'daily_tip'],
                ['label' => '👥 Peer Support', 'action' => 'peer_support'],
                ['label' => '🎮 Mini Games', 'action' => 'mini_games']
            ]
        ];
    }

    private function handleBackToSummary($student): array
    {
        $this->viewingDetails = false;
        return $this->showSummaryListWithButtons($student);
    }

    private function showSummaryListWithButtons($student): array
    {
        $applications = $student->placementApplications()
            ->orderBy('applicationDate', 'desc')
            ->get();

        $response = "📊 Your Placement Applications Summary ({$applications->count()} applications)\n\n";

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

        $this->storedSummaries = $summaries;
        $this->viewingDetails = false;

        $response .= "Ranked by Score:\n\n";
        foreach ($summaries as $rank => $summary) {
            $response .= "#" . ($rank + 1) . " - {$summary['company']}\n";
            $response .= "📅 Applied: {$summary['date']}\n";
            $response .= "⭐ Score: {$summary['score']}/100 ({$summary['rating']})\n";
            $response .= "💡 {$summary['recommendation']}\n\n";
        }

        $response .= "Click a button below to view detailed evaluation:";

        $buttons = [];
        foreach ($summaries as $rank => $summary) {
            $buttons[] = [
                'label' => "#" . ($rank + 1) . " - {$summary['company']}",
                'action' => 'view_application',
                'data' => $rank + 1
            ];
        }
        $buttons[] = ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu'];

        return [
            'content' => $response,
            'buttons' => $buttons
        ];
    }

    private function getApplicationListButtons(): array
    {
        $buttons = [];
        foreach ($this->storedSummaries as $rank => $summary) {
            $buttons[] = [
                'label' => "#" . ($rank + 1) . " - {$summary['company']}",
                'action' => 'view_application',
                'data' => $rank + 1
            ];
        }
        $buttons[] = ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu'];
        return $buttons;
    }

    private function getFAQMenuButtons(): array
    {
        $buttons = [];
        foreach ($this->storedFAQs as $index => $faq) {
            $icons = ['📋', '📄', '💼', '👨‍🏫', '⏱️'];
            $buttons[] = [
                'label' => ($index + 1) . '. ' . $faq['title'],
                'action' => 'view_faq',
                'data' => $index + 1
            ];
        }
        $buttons[] = ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu'];
        return $buttons;
    }

    private function getFAQByIndex($index): array
    {
        if (empty($this->storedFAQs) || !isset($this->storedFAQs[$index])) {
            return ['title' => 'Unknown', 'answer' => 'FAQ not found.'];
        }

        return $this->storedFAQs[$index];
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

        return "📊 Placement Evaluation for {$application->companyName}\n\n" .
            "Overall Score: {$score}/{$maxScore} ({$rating})\n\n" .
            "Evaluation Factors:\n" . implode("\n", $factors) . "\n\n" .
            "Recommendation:\n{$recommendation}";
    }

    /**     * Calculate jobscope match score (40 points max)
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

    /**     * Extract keywords from student program
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

    /**     * Get jobscope factor text
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

    /**     * Calculate distance score (20 points max)
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

    /**     * Get distance factor text
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

    /**     * Calculate company history score (20 points max)
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

    /**     * Get company history factor text
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

    /**     * Calculate allowance score (20 points max)
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

    /**     * Get allowance factor text
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
            return "✅ Highly Recommended - This placement demonstrates excellent alignment with your preferences and qualifications.";
        } elseif ($score >= 60) {
            return "✅ Recommended - This placement is suitable and meets most of your criteria.";
        } elseif ($score >= 40) {
            return "⚠️ Moderately Recommended - This placement meets some criteria but has certain limitations to review.";
        } elseif ($score >= 20) {
            return "⚠️ Consider Carefully - This placement has notable issues, review carefully before proceeding.";
        } else {
            return "❌ Not Recommended - This placement does not sufficiently match your requirements. Explore other options.";
        }
    }

    /**     * Perform quick evaluation for summary list (just score and rating, no detailed factors)
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

    /**     * Get quick recommendation text for summary list
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
            $statusIcon = $this->getStatusIcon(strtolower($courseVerification->status));
            $statusInfo[] = "📋 Course Verification: {$statusIcon} " . ucfirst($courseVerification->status);
        } else {
            $statusInfo[] = "📋 Course Verification: ⚪ Not submitted";
        }

        // Placement Applications
        $applications = $student->placementApplications()
            ->orderBy('applicationDate', 'desc')
            ->get();

        if ($applications->count() > 0) {
            $statusInfo[] = "\n💼 Placement Applications: {$applications->count()} total";
            foreach ($applications->take(3) as $app) {
                $status = $app->overall_status;
                $statusIcon = $this->getStatusIcon(strtolower($status));
                $statusInfo[] = "   • {$app->companyName}: {$statusIcon} {$status}";
            }
        } else {
            $statusInfo[] = "\n💼 Placement Applications: None submitted";
        }

        // Supervisor Assignment
        $supervisorAssignment = $student->supervisorAssignment;
        if ($supervisorAssignment) {
            $supervisor = $supervisorAssignment->supervisor;
            $statusInfo[] = "\n👨‍🏫 Supervisor: ✅ {$supervisor->user->name}";
        } else {
            $statusInfo[] = "\n👨‍🏫 Supervisor: ⚪ Not assigned yet";
        }

        return implode("\n", $statusInfo);
    }

    private function getStatusIcon($status): string
    {
        $status = strtolower($status);
        switch ($status) {
            case 'approved':
                return '✅';
            case 'rejected':
                return '❌';
            case 'pending':
                return '⏳';
            default:
                return '⚪';
        }
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

        $insights = "💼 Company Insights: {$companyStats->companyName}\n\n";
        $insights .= "📊 Statistics:\n";
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
            $insights .= "\n📋 Common Positions:\n";
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

        $comparison = "📊 Company Comparison\n\n";

        foreach ($companies as $companyName) {
            $stats = PlacementApplication::where('companyName', 'like', '%' . $companyName . '%')
                ->where('studentAcceptance', 'Accepted')
                ->select(
                    DB::raw('COUNT(*) as total_students'),
                    DB::raw('AVG(allowance) as avg_allowance')
                )
                ->first();

            if ($stats) {
                $comparison .= "{$companyName}:\n";
                $comparison .= "• Students Accepted: {$stats->total_students}\n";
                if ($stats->avg_allowance) {
                    $comparison .= "• Avg Allowance: RM " . number_format($stats->avg_allowance, 2) . "\n";
                }
            } else {
                $comparison .= "{$companyName}: No data available\n";
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
                'answer' => "Placement Requirements:\n\n" .
                    "1. ✅ Approved course verification\n" .
                    "2. ✅ Complete placement application form\n" .
                    "3. ✅ Submit required documents\n" .
                    "4. ✅ Wait for committee and coordinator approval"
            ],
            [
                'key' => 'document',
                'title' => 'Required Documents',
                'answer' => "Required Documents:\n\n" .
                    "📄 For Placement Application:\n" .
                    "• Offer letter from company\n" .
                    "• Acceptance form\n" .
                    "• Any other documents specified by the company\n\n" .
                    "📄 For Course Verification:\n" .
                    "• Course registration document\n" .
                    "• Transcript or academic record\n" .
                    "• Any other supporting documents"
            ],
            [
                'key' => 'apply',
                'title' => 'How to Apply',
                'answer' => "How to Apply for Placement:\n\n" .
                    "1. 📋 Go to 'Internship Placement' page in the navigation menu\n" .
                    "2. ➕ Click 'New Application' button\n" .
                    "3. ✍️ Fill in all company details (name, address, contact, etc.)\n" .
                    "4. 📎 Upload required documents (offer letter, acceptance form)\n" .
                    "5. ✅ Submit your application\n\n" .
                    "Note: Your application will be reviewed by the committee and coordinator."
            ],
            [
                'key' => 'supervisor',
                'title' => 'Supervisor Assignment',
                'answer' => "Supervisor Assignment:\n\n" .
                    "👨‍🏫 When are supervisors assigned?\n" .
                    "Supervisors are automatically assigned after you accept a placement offer.\n\n" .
                    "⚙️ How are supervisors selected?\n" .
                    "The system automatically considers:\n" .
                    "• Distance between supervisor and company location\n" .
                    "• Supervisor's current workload (quota)\n" .
                    "• Supervisor's preferences and availability\n\n" .
                    "📧 Notification:\n" .
                    "You will be notified via email once a supervisor is assigned to you."
            ],
            [
                'key' => 'time',
                'title' => 'Processing Time',
                'answer' => "Processing Time:\n\n" .
                    "📋 Course Verification:\n" .
                    "• Usually takes 1-2 weeks\n" .
                    "• Depends on lecturer availability\n\n" .
                    "💼 Placement Application:\n" .
                    "• Committee review: Usually 1-2 weeks\n" .
                    "• Coordinator review: Usually 1-2 weeks\n" .
                    "• Total: Usually 2-4 weeks\n\n" .
                    "👨‍🏫 Supervisor Assignment:\n" .
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

    /**     * Show FAQ menu with numbered options
     */
    private function showFAQMenu(): string
    {
        $menu = "Frequently Asked Questions:\n\n";
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
            $menu .= "" . ($index + 1) . ". {$icons[$index]} {$faq['title']}\n";
        }

        $menu .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $menu .= "💬 To view an answer, type the number (e.g., 1, 2, 3, 4, or 5)\n";
        $menu .= "Or type 'back' to return to main menu";

        return $menu;
    }

    /**     * Show FAQ answer based on number selection
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

    /**     * Get navigation options after showing FAQ answer
     */
    private function getFAQNavigationOptions(): string
    {
        $options = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $options .= "💬 What would you like to do next?\n\n";

        if (!empty($this->storedFAQs)) {
            $maxNumber = count($this->storedFAQs);
            $options .= "• Type 1 to {$maxNumber} to view another FAQ\n";
        }

        $options .= "• Type 'back' or 'menu' to return to FAQ menu\n";
        $options .= "• Type 'exit' to return to main menu";

        return $options;
    }

    /**     * Show summary list of all applications
     */
    private function showSummaryList($student): string
    {
        $applications = $student->placementApplications()
            ->orderBy('applicationDate', 'desc')
            ->get();

        $response = "📊 Your Placement Applications Summary ({$applications->count()} applications)\n\n";

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

        $response .= "Ranked by Score:\n\n";
        foreach ($summaries as $rank => $summary) {
            $response .= "#" . ($rank + 1) . " - {$summary['company']}\n";
            $response .= "📅 Applied: {$summary['date']}\n";
            $response .= "⭐ Score: {$summary['score']}/100 ({$summary['rating']})\n";
            $response .= "💡 {$summary['recommendation']}\n\n";
        }

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $response .= "💬 Want more details? Type:\n";
        $response .= "• 1 (to see details of #1)\n";
        $response .= "• 2 (to see details of #2)\n";
        $response .= "• Type 'back' to return to main menu\n";

        return $response;
    }

    /**     * Show details for a specific application by number
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

    /**     * Get navigation options after showing details
     */
    private function getNavigationOptions(): string
    {
        $options = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $options .= "💬 What would you like to do next?\n\n";

        if (!empty($this->storedSummaries)) {
            $maxNumber = count($this->storedSummaries);
            $options .= "• Type 1 to {$maxNumber} to view another application's details\n";
            $options .= "• Type 'back' to return to summary list\n";
        }
        $options .= "• Or ask me something else!";

        return $options;
    }

    /**     * Extract number selection from message (e.g., "#1", "show #1", "details for #1")
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

    // ==================== INTERVIEW PREPARATION ====================

    private function handleInterviewPrep(): array
    {
        $content = "🎤 Interview Preparation Hub\n\n";
        $content .= "Get ready for your placement interviews! Choose what you'd like to learn:\n\n";
        $content .= "• 💡 Interview Tips - Essential tips for success\n";
        $content .= "• ❓ Common Questions - Most asked interview questions\n";
        $content .= "• 📝 Practice Questions - Test your knowledge\n";
        $content .= "• ✅ Do's & Don'ts - What to do and avoid";

        return [
            'content' => $content,
            'buttons' => [
                ['label' => '💡 Interview Tips', 'action' => 'interview_tips'],
                ['label' => '❓ Common Questions', 'action' => 'common_questions'],
                ['label' => '📝 Practice Questions', 'action' => 'practice_questions'],
                ['label' => '✅ Do\'s & Don\'ts', 'action' => 'interview_do_dont'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleInterviewTips(): array
    {
        $tips = [
            "1. Research the Company 📚\n" .
                "Learn about the company's mission, values, recent news, and the role you're applying for.",

            "2. Prepare Your STAR Stories ⭐\n" .
                "Prepare Situation-Task-Action-Result stories that showcase your skills and experiences.",

            "3. Dress Professionally 👔\n" .
                "First impressions matter. Dress appropriately for the company culture.",

            "4. Practice Your Answers 🗣️\n" .
                "Practice common questions out loud. Record yourself to improve delivery.",

            "5. Prepare Questions to Ask ❓\n" .
                "Have 2-3 thoughtful questions ready. It shows genuine interest.",

            "6. Arrive Early ⏰\n" .
                "Arrive 10-15 minutes early for in-person interviews. Test tech for virtual ones.",

            "7. Show Enthusiasm 😊\n" .
                "Be positive and show genuine interest in the role and company.",

            "8. Follow Up 📧\n" .
                "Send a thank-you email within 24 hours after the interview."
        ];

        $randomTip = $tips[array_rand($tips)];

        return [
            'content' => "💡 Interview Tips\n\n" . $randomTip . "\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "Would you like another tip?",
            'buttons' => [
                ['label' => '💡 Another Tip', 'action' => 'interview_tips'],
                ['label' => '🎤 Interview Hub', 'action' => 'interview_prep'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleCommonQuestions(): array
    {
        $questions = [
            [
                'q' => "Tell me about yourself.",
                'a' => "Keep it concise (2-3 minutes). Start with your current situation, highlight relevant experience, and connect it to why you're interested in this role."
            ],
            [
                'q' => "Why do you want this internship?",
                'a' => "Show genuine interest. Mention specific aspects of the company/role, how it aligns with your career goals, and what you hope to learn."
            ],
            [
                'q' => "What are your strengths?",
                'a' => "Pick 2-3 relevant strengths. Provide specific examples. For example: 'I'm detail-oriented, which helped me catch errors in my group project.'"
            ],
            [
                'q' => "What are your weaknesses?",
                'a' => "Be honest but show growth. Mention a real weakness and explain how you're working to improve it. Example: 'I used to struggle with time management, but I now use a planner.'"
            ],
            [
                'q' => "Where do you see yourself in 5 years?",
                'a' => "Show ambition but stay realistic. Mention how this internship fits into your career path and what skills you want to develop."
            ],
            [
                'q' => "Why should we hire you?",
                'a' => "Highlight unique qualities. Combine your skills, enthusiasm, and what you can contribute. Be specific and confident."
            ],
            [
                'q' => "Do you have any questions for us?",
                'a' => "Always say yes! Ask about: company culture, team dynamics, learning opportunities, or what success looks like in this role."
            ]
        ];

        $selected = $questions[array_rand($questions)];

        return [
            'content' => "❓ Common Interview Question\n\n" .
                "Q: {$selected['q']}\n\n" .
                "A: {$selected['a']}\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "Want to see another question?",
            'buttons' => [
                ['label' => '❓ Another Question', 'action' => 'common_questions'],
                ['label' => '🎤 Interview Hub', 'action' => 'interview_prep'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }
    private function handlePracticeQuestions(): array
    {
        $scenarios = [
            [
                'scenario' => "You're asked: 'Describe a time you worked in a team.'",
                'tip' => "Use STAR method:\n" .
                    "• Situation: Set the context\n" .
                    "• Task: What was your responsibility?\n" .
                    "• Action: What did you do?\n" .
                    "• Result: What was the outcome?"
            ],
            [
                'scenario' => "You're asked: 'How do you handle stress?'",
                'tip' => "Show resilience:\n" .
                    "• Mention specific stress management techniques\n" .
                    "• Give an example of handling a stressful situation\n" .
                    "• Show you can perform under pressure"
            ],
            [
                'scenario' => "You're asked: 'What's your biggest failure?'",
                'tip' => "Show growth mindset:\n" .
                    "• Choose a real but not catastrophic failure\n" .
                    "• Focus on what you learned\n" .
                    "• Explain how it made you better"
            ]
        ];

        $selected = $scenarios[array_rand($scenarios)];

        return [
            'content' => "📝 Practice Scenario\n\n" .
                "Scenario:\n{$selected['scenario']}\n\n" .
                "💡 How to Answer:\n{$selected['tip']}\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "Try practicing your answer out loud!",
            'buttons' => [
                ['label' => '📝 Another Scenario', 'action' => 'practice_questions'],
                ['label' => '🎤 Interview Hub', 'action' => 'interview_prep'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleInterviewDoDont(): array
    {
        $content = "✅ Interview Do's & Don'ts\n\n";

        $content .= "✅ DO:\n";
        $content .= "• Research the company thoroughly\n";
        $content .= "• Prepare questions to ask the interviewer\n";
        $content .= "• Arrive 10-15 minutes early\n";
        $content .= "• Make eye contact and smile\n";
        $content .= "• Listen carefully before answering\n";
        $content .= "• Show enthusiasm and interest\n";
        $content .= "• Follow up with a thank-you email\n\n";

        $content .= "❌ DON'T:\n";
        $content .= "• Arrive late or unprepared\n";
        $content .= "• Speak negatively about previous experiences\n";
        $content .= "• Interrupt the interviewer\n";
        $content .= "• Use your phone during the interview\n";
        $content .= "• Give one-word answers\n";
        $content .= "• Forget to ask questions\n";
        $content .= "• Dress inappropriately";

        return [
            'content' => $content,
            'buttons' => [
                ['label' => '🎤 Interview Hub', 'action' => 'interview_prep'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    // ==================== DAILY TIP & MOTIVATION ====================
    private function handleDailyTip(): array
    {
        $tips = [
            [
                'type' => '💡 Tip',
                'content' => "Start Your Day Right!\n\n" .
                    "Check your application status every morning. Staying updated helps you respond quickly to any changes or requests.",
                'motivation' => "✨ Remember: Every expert was once a beginner. Your internship journey is just beginning!"
            ],
            [
                'type' => '💡 Tip',
                'content' => "Network Smartly\n\n" .
                    "Connect with professionals on LinkedIn before applying. A warm introduction can make a huge difference!",
                'motivation' => "🌟 Success is not final, failure is not fatal: it is the courage to continue that counts."
            ],
            [
                'type' => '💡 Tip',
                'content' => "Customize Your Applications\n\n" .
                    "Tailor each application to the specific company. Generic applications rarely stand out.",
                'motivation' => "💪 The only way to do great work is to love what you do. Keep pushing forward!"
            ],
            [
                'type' => '💡 Tip',
                'content' => "Follow Up Strategically\n\n" .
                    "Send a follow-up email 1-2 weeks after submitting if you haven't heard back. Be polite and brief.",
                'motivation' => "🚀 Your future is created by what you do today, not tomorrow. Take action now!"
            ],
            [
                'type' => '💡 Tip',
                'content' => "Prepare for Rejections\n\n" .
                    "Rejections are part of the process. Learn from each one and keep applying. Persistence pays off!",
                'motivation' => "🌈 After every storm comes a rainbow. Keep going, your opportunity is coming!"
            ],
            [
                'type' => '💡 Tip',
                'content' => "Document Everything\n\n" .
                    "Keep a spreadsheet of all applications: company, date applied, status, and follow-up dates.",
                'motivation' => "⭐ You are capable of amazing things. Believe in yourself and your abilities!"
            ],
            [
                'type' => '💡 Tip',
                'content' => "Practice Your Elevator Pitch\n\n" .
                    "Be ready to introduce yourself in 30 seconds. Practice until it feels natural and confident.",
                'motivation' => "🎯 Focus on progress, not perfection. Every step forward is a victory!"
            ],
            [
                'type' => '💡 Tip',
                'content' => "Stay Organized\n\n" .
                    "Use a calendar to track deadlines, interviews, and follow-ups. Organization reduces stress!",
                'motivation' => "💎 You are stronger than you think. Keep pushing through challenges!"
            ]
        ];

        // Always show random tip (removed dayOfYear logic)
        $selected = $tips[array_rand($tips)];

        return [
            'content' => "{$selected['type']} Daily Tip\n\n" .
                "{$selected['content']}\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "{$selected['motivation']}",
            'buttons' => [
                ['label' => '💡 Another Tip', 'action' => 'daily_tip'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    // ==================== MINI GAMES ====================

    private function handleMiniGames(): array
    {
        $content = "🎮 Mini Games Hub\n\n";
        $content .= "Take a break and have some fun while learning!\n\n";
        $content .= "• 🎲 Trivia Game - Test your knowledge about internships\n";
        $content .= "• 🧠 Quick Quiz - Answer quick questions\n";
        $content .= "• 🔤 Word Game - Guess the internship-related word";

        return [
            'content' => $content,
            'buttons' => [
                ['label' => '🎲 Trivia Game', 'action' => 'game_trivia'],
                ['label' => '🧠 Quick Quiz', 'action' => 'game_quiz'],
                ['label' => '🔤 Word Game', 'action' => 'game_word'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleTriviaGame(): array
    {
        $trivia = [
            [
                'q' => "What is the STAR method used for?",
                'a' => "Answering behavioral interview questions (Situation, Task, Action, Result)",
                'correct' => 'B',
                'options' => ['A) Writing resumes', 'B) Answering interview questions', 'C) Networking', 'D) Writing cover letters']
            ],
            [
                'q' => "How long should you wait before following up on an application?",
                'a' => "1-2 weeks is the standard waiting period",
                'correct' => 'B',
                'options' => ['A) 1 day', 'B) 1-2 weeks', 'C) 1 month', 'D) Never follow up']
            ],
            [
                'q' => "What should you do before an interview?",
                'a' => "Research the company, practice answers, and prepare questions",
                'correct' => 'B',
                'options' => ['A) Nothing', 'B) Research and prepare', 'C) Just show up', 'D) Only practice answers']
            ],
            [
                'q' => "What is the best way to stand out in applications?",
                'a' => "Customize each application to the specific company and role",
                'correct' => 'B',
                'options' => ['A) Use the same application', 'B) Customize each one', 'C) Apply to many quickly', 'D) Only apply to big companies']
            ],
            [
                'q' => "When should you start applying for internships?",
                'a' => "2-3 months before you want to start",
                'correct' => 'B',
                'options' => ['A) 1 week before', 'B) 2-3 months before', 'C) 1 year before', 'D) After graduation']
            ]
        ];

        $selected = $trivia[array_rand($trivia)];

        // Store game data for answer submission
        $this->currentGame = 'trivia';
        $this->currentGameData = $selected;
        $this->gameAnswer = '';
        $this->showingGameAnswer = false;

        return [
            'content' => "🎲 Trivia Question\n\n" .
                "{$selected['q']}\n\n" .
                implode("\n", $selected['options']) . "\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "💬 Type your answer (A, B, C, or D) in the input field below, then click 'Submit Answer' to see if you're correct!",
            'buttons' => [
                ['label' => '✅ Submit Answer', 'action' => 'submit_game_answer'],
                ['label' => '🎮 Games Hub', 'action' => 'mini_games'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ],
            'inputPlaceholder' => 'Enter your answer (A, B, C, or D)'
        ];
    }

    private function handleQuickQuiz(): array
    {
        $quizzes = [
            [
                'topic' => "Interview Preparation",
                'question' => "What should you bring to an in-person interview?",
                'correct' => 'D',
                'options' => [
                    'A) Just your ID',
                    'B) Your phone and charger',
                    'C) Nothing, they have everything',
                    'D) Multiple copies of resume, portfolio, questions list, and positive attitude'
                ],
                'answer' => "Multiple copies of your resume, portfolio (if applicable), questions list, and a positive attitude!"
            ],
            [
                'topic' => "Application Process",
                'question' => "How many companies should you apply to?",
                'correct' => 'B',
                'options' => [
                    'A) As many as possible, quantity matters',
                    'B) 10-20 quality applications',
                    'C) Only 1-2 companies',
                    'D) Wait for companies to contact you'
                ],
                'answer' => "There's no magic number, but applying to 10-20 quality applications is better than 50 generic ones."
            ],
            [
                'topic' => "Networking",
                'question' => "What's the best way to network online?",
                'correct' => 'C',
                'options' => [
                    'A) Only ask for favors',
                    'B) Send generic messages to everyone',
                    'C) Be genuine, provide value, engage with content',
                    'D) Only connect with famous people'
                ],
                'answer' => "Be genuine, provide value, engage with content, and don't just ask for favors. Build relationships!"
            ],
            [
                'topic' => "Rejections",
                'question' => "What should you do after a rejection?",
                'correct' => 'D',
                'options' => [
                    'A) Give up and stop applying',
                    'B) Complain about the company',
                    'C) Ignore it and move on',
                    'D) Thank them, ask for feedback, learn from it, and keep applying'
                ],
                'answer' => "Thank them for the opportunity, ask for feedback if possible, learn from it, and keep applying!"
            ]
        ];

        $selected = $quizzes[array_rand($quizzes)];

        // Store game data for answer submission
        $this->currentGame = 'quiz';
        $this->currentGameData = $selected;
        $this->gameAnswer = '';
        $this->showingGameAnswer = false;

        return [
            'content' => "🧠 Quick Quiz\n\n" .
                "Topic: {$selected['topic']}\n\n" .
                "Q: {$selected['question']}\n\n" .
                implode("\n", $selected['options']) . "\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "💬 Type your answer (A, B, C, or D) in the input field below, then click 'Submit Answer' to see if you're correct!",
            'buttons' => [
                ['label' => '✅ Submit Answer', 'action' => 'submit_game_answer'],
                ['label' => '🎮 Games Hub', 'action' => 'mini_games'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ],
            'inputPlaceholder' => 'Enter your answer (A, B, C, or D)'
        ];
    }

    private function handleWordGame(): array
    {
        $words = [
            [
                'hint' => "A formal meeting to assess a candidate",
                'word' => "INTERVIEW",
                'scrambled' => "IETRNWIVE"
            ],
            [
                'hint' => "A document summarizing your qualifications",
                'word' => "RESUME",
                'scrambled' => "REESMU"
            ],
            [
                'hint' => "A person who guides and evaluates your work",
                'word' => "SUPERVISOR",
                'scrambled' => "SPERVISUOR"
            ],
            [
                'hint' => "A period of work experience",
                'word' => "INTERNSHIP",
                'scrambled' => "INTENRSHIP"
            ],
            [
                'hint' => "A company where you work",
                'word' => "EMPLOYER",
                'scrambled' => "EMPOYLER"
            ]
        ];

        $selected = $words[array_rand($words)];

        // Store game data for answer submission
        $this->currentGame = 'word';
        $this->currentGameData = $selected;
        $this->gameAnswer = '';
        $this->showingGameAnswer = false;

        return [
            'content' => "🔤 Word Game\n\n" .
                "Unscramble the word!\n\n" .
                "Hint: {$selected['hint']}\n\n" .
                "Scrambled: {$selected['scrambled']}\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "💬 Type your answer in the input field below, then click 'Submit Answer' to see if you're correct!",
            'buttons' => [
                ['label' => '✅ Submit Answer', 'action' => 'submit_game_answer'],
                ['label' => '🎮 Games Hub', 'action' => 'mini_games'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleSubmitGameAnswer(): array
    {
        if (empty($this->gameAnswer) || empty($this->currentGameData)) {
            return [
                'content' => "Please enter your answer first!",
                'buttons' => [
                    ['label' => '🎮 Games Hub', 'action' => 'mini_games'],
                    ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
                ]
            ];
        }

        $userAnswer = strtoupper(trim($this->gameAnswer));
        $isCorrect = false;
        $feedback = '';
        $gameType = $this->currentGame; // Store before resetting

        if ($gameType === 'trivia' || $gameType === 'quiz') {
            $correctAnswer = $this->currentGameData['correct'];
            $isCorrect = ($userAnswer === $correctAnswer);

            if ($isCorrect) {
                $feedback = "🎉 Correct! Well done!";
            } else {
                $feedback = "❌ Not quite right. The correct answer is {$correctAnswer}.";
            }
        } elseif ($gameType === 'word') {
            $correctWord = strtoupper($this->currentGameData['word']);
            $isCorrect = ($userAnswer === $correctWord);

            if ($isCorrect) {
                $feedback = "🎉 Correct! Great job!";
            } else {
                $feedback = "❌ Not quite right. The correct word is {$correctWord}.";
            }
        }

        $content = "Your Answer: {$this->gameAnswer}\n\n";
        $content .= "{$feedback}\n\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        if ($gameType === 'trivia' || $gameType === 'quiz') {
            $content .= "Correct Answer: {$this->currentGameData['a']}\n\n";
        } else {
            $content .= "Correct Word: {$this->currentGameData['word']}\n\n";
        }

        // Determine next action button
        $nextAction = 'game_trivia';
        if ($gameType === 'quiz') {
            $nextAction = 'game_quiz';
        } elseif ($gameType === 'word') {
            $nextAction = 'game_word';
        }

        // Reset game state
        $this->gameAnswer = '';
        $this->currentGame = null;
        $this->currentGameData = null;

        return [
            'content' => $content,
            'buttons' => [
                ['label' => '🎲 Another Question', 'action' => $nextAction],
                ['label' => '🎮 Games Hub', 'action' => 'mini_games'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    // ==================== PEER SUPPORT ====================

    private function handlePeerSupport(): array
    {
        $content = "👥 Peer Support Hub\n\n";
        $content .= "Connect with fellow students and learn from their experiences!\n\n";
        $content .= "• 💬 View Tips - Read tips shared by other students\n";
        $content .= "• ✍️ Share a Tip - Help others by sharing your experience";

        return [
            'content' => $content,
            'buttons' => [
                ['label' => '💬 View Tips', 'action' => 'view_tips'],
                ['label' => '✍️ Share a Tip', 'action' => 'share_tip'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleViewPeerTips(): array
    {
        // Fetch tips from database
        $tips = PeerTip::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($tips->isEmpty()) {
            return [
                'content' => "💬 Peer Tips\n\n" .
                    "No tips have been shared yet. Be the first to share a helpful tip! 🌟",
                'buttons' => [
                    ['label' => '✍️ Share a Tip', 'action' => 'share_tip'],
                    ['label' => '👥 Peer Support', 'action' => 'peer_support'],
                    ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
                ]
            ];
        }

        // Get a random tip
        $selectedTip = $tips->random();

        $content = "💬 Peer Tip\n\n";
        $content .= "From: {$selectedTip->nickname}\n\n";
        $content .= "{$selectedTip->tip_content}\n\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $content .= "📅 Shared " . $selectedTip->created_at->diffForHumans();

        return [
            'content' => $content,
            'buttons' => [
                ['label' => '💬 Another Tip', 'action' => 'view_tips'],
                ['label' => '✍️ Share a Tip', 'action' => 'share_tip'],
                ['label' => '👥 Peer Support', 'action' => 'peer_support'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleShareTip(): array
    {
        $this->showingTipForm = true;
        $this->tipNickname = '';
        $this->tipContent = '';

        return [
            'content' => "✍️ Share Your Tip\n\n" .
                "Help your fellow students by sharing a helpful tip!\n\n" .
                "Instructions:\n" .
                "• Enter a nickname (can be anonymous)\n" .
                "• Share your tip or advice\n" .
                "• Your tip will be visible to other students\n\n" .
                "💡 Fill in the form below and click 'Submit Tip'",
            'buttons' => [
                ['label' => '✅ Submit Tip', 'action' => 'submit_tip'],
                ['label' => '👥 Peer Support', 'action' => 'peer_support'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    private function handleSubmitTip(): array
    {
        // Validate input
        if (empty(trim($this->tipNickname))) {
            return [
                'content' => "❌ Error\n\nPlease enter a nickname!",
                'buttons' => [
                    ['label' => '✍️ Share a Tip', 'action' => 'share_tip'],
                    ['label' => '👥 Peer Support', 'action' => 'peer_support'],
                    ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
                ],
            ];
        }

        if (empty(trim($this->tipContent))) {
            return [
                'content' => "❌ Error\n\nPlease enter your tip!",
                'buttons' => [
                    ['label' => '✍️ Share a Tip', 'action' => 'share_tip'],
                    ['label' => '👥 Peer Support', 'action' => 'peer_support'],
                    ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
                ],
            ];
        }

        // Save to database
        $student = Auth::user()->student;

        PeerTip::create([
            'nickname' => trim($this->tipNickname),
            'tip_content' => trim($this->tipContent),
            'studentID' => $student ? $student->studentID : null, // Optional for anonymity
        ]);

        // Reset form
        $this->showingTipForm = false;
        $this->tipNickname = '';
        $this->tipContent = '';

        return [
            'content' => "✅ Thank You!\n\n" .
                "Your tip has been shared successfully! 🌟\n\n" .
                "Other students can now benefit from your advice. Keep sharing and helping each other!",
            'buttons' => [
                ['label' => '💬 View Tips', 'action' => 'view_tips'],
                ['label' => '✍️ Share Another Tip', 'action' => 'share_tip'],
                ['label' => '👥 Peer Support', 'action' => 'peer_support'],
                ['label' => '🏠 Back to Menu', 'action' => 'back_to_menu']
            ]
        ];
    }

    // Helper method for main menu buttons
    private function getMainMenuButtons(): array
    {
        return [
            ['label' => '📊 Evaluate Placement', 'action' => 'evaluate_placement'],
            ['label' => '📋 Check Status', 'action' => 'check_status'],
            ['label' => '❓ FAQs', 'action' => 'faqs'],
            ['label' => '🎤 Interview Prep', 'action' => 'interview_prep'],
            ['label' => '💡 Daily Tip', 'action' => 'daily_tip'],
            ['label' => '👥 Peer Support', 'action' => 'peer_support'],
            ['label' => '🎮 Mini Games', 'action' => 'mini_games']
        ];
    }


    public function render()
    {
        return view('livewire.student.placementChatbot');
    }
}
