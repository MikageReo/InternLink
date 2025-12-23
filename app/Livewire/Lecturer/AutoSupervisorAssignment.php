<?php

namespace App\Livewire\Lecturer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Services\SupervisorRecommendationService;
use App\Services\SupervisorAssignmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AutoSupervisorAssignment extends Component
{
    use WithPagination;

    public $selectedStudent = null;
    public $recommendations = [];
    public $showRecommendationModal = false;
    public $assignmentNotes = '';

    protected $supervisorRecommendationService;
    protected $supervisorAssignmentService;

    public function boot(
        SupervisorRecommendationService $supervisorRecommendationService,
        SupervisorAssignmentService $supervisorAssignmentService
    ) {
        $this->supervisorRecommendationService = $supervisorRecommendationService;
        $this->supervisorAssignmentService = $supervisorAssignmentService;
    }

    public function mount()
    {
        // Check if user is coordinator
        $user = Auth::user();
        if (!$user->lecturer || !$user->lecturer->isCoordinator) {
            abort(403, 'Access denied. Only coordinators can access auto supervisor assignment.');
        }
    }

    public function openRecommendationModal($studentID)
    {
        $this->selectedStudent = Student::with(['user', 'acceptedPlacementApplication'])
            ->findOrFail($studentID);

        // Get top 3 recommendations
        $this->recommendations = $this->supervisorRecommendationService
            ->getRecommendedSupervisors($this->selectedStudent, 3)
            ->toArray();

        // Log recommendations for transparency
        if (!empty($this->recommendations)) {
            $this->supervisorRecommendationService->logRecommendation(
                $this->selectedStudent,
                $this->recommendations
            );
        }

        $this->showRecommendationModal = true;
    }

    public function closeRecommendationModal()
    {
        $this->showRecommendationModal = false;
        $this->selectedStudent = null;
        $this->recommendations = [];
        $this->assignmentNotes = '';
    }

    public function assignSupervisor($lecturerID, $score)
    {
        try {
            $lecturer = $this->recommendations[array_search($lecturerID, array_column($this->recommendations, 'lecturer'))]['lecturer'] ?? null;

            if (!$lecturer) {
                session()->flash('error', 'Lecturer not found in recommendations.');
                return;
            }

            // Auto-generate notes with recommendation info
            $autoNotes = "System recommended based on coursework match, travel preference, and proximity (Score: " . round($score, 2) . ").";
            if ($this->assignmentNotes) {
                $autoNotes .= " " . $this->assignmentNotes;
            }

            $assignment = $this->supervisorAssignmentService->assignSupervisor(
                $this->selectedStudent->studentID,
                $lecturerID,
                null, // Will use current coordinator
                $autoNotes,
                false, // No quota override
                null
            );

            session()->flash('success', 'Supervisor assigned successfully! ' . $lecturer->user->name . ' has been assigned to ' . $this->selectedStudent->user->name);

            Log::info('Auto supervisor assignment completed', [
                'student_id' => $this->selectedStudent->studentID,
                'lecturer_id' => $lecturerID,
                'score' => $score,
                'method' => 'auto_recommendation'
            ]);

            $this->closeRecommendationModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to assign supervisor: ' . $e->getMessage());
            Log::error('Failed to assign recommended supervisor', [
                'student_id' => $this->selectedStudent->studentID,
                'lecturer_id' => $lecturerID,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $students = $this->supervisorRecommendationService
            ->getStudentsNeedingSupervisor()
            ->paginate(10);

        $stats = [
            'total_unassigned' => Student::whereHas('placementApplications', function ($q) {
                $q->where('studentAcceptance', 'Accepted');
            })->whereDoesntHave('supervisorAssignments', function ($q) {
                $q->where('status', 'assigned');
            })->count(),

            'total_assigned' => Student::whereHas('supervisorAssignments', function ($q) {
                $q->where('status', 'assigned');
            })->count(),
        ];

        return view('livewire.auto-supervisor-assignment', [
            'students' => $students,
            'stats' => $stats,
        ]);
    }
}
