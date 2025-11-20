<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\SupervisorAssignment;
use App\Models\PlacementApplication;
use App\Services\SupervisorAssignmentService;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupervisorAssignmentTable extends Component
{
    use WithPagination;

    // Search and filter properties
    public $search = '';
    public $sortField = 'assigned_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';
    public $studentFilter = '';
    public $supervisorFilter = '';
    public $assignmentTypeFilter = ''; // 'assigned', 'unassigned', 'all'

    // Modal properties
    public $showAssignModal = false;
    public $showDetailModal = false;
    public $selectedStudent = null;
    public $selectedAssignment = null;
    public $selectedAssignmentID = null;
    public $recommendedSupervisors = [];
    public $selectedSupervisorID = null;
    public $assignmentNotes = '';
    public $quotaOverride = false;
    public $overrideReason = '';
    public $showOverrideModal = false;

    protected $supervisorAssignmentService;
    protected $geocodingService;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'assigned_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'assignmentTypeFilter' => ['except' => 'unassigned'],
    ];

    public function boot(SupervisorAssignmentService $supervisorAssignmentService, GeocodingService $geocodingService)
    {
        $this->supervisorAssignmentService = $supervisorAssignmentService;
        $this->geocodingService = $geocodingService;
    }

    public function mount()
    {
        // Check if user is coordinator
        $user = Auth::user();
        if (!$user->lecturer || !$user->lecturer->isCoordinator) {
            abort(403, 'Access denied. Only coordinators can assign supervisors.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingAssignmentTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openAssignModal($studentID)
    {
        $this->selectedStudent = Student::with(['user', 'acceptedPlacementApplication'])
            ->findOrFail($studentID);

        // Get recommended supervisors
        $this->recommendedSupervisors = $this->supervisorAssignmentService->getRecommendedSupervisors(
            $studentID,
            10,
            $this->quotaOverride
        );

        $this->selectedSupervisorID = null;
        $this->assignmentNotes = '';
        $this->quotaOverride = false;
        $this->overrideReason = '';
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->selectedStudent = null;
        $this->recommendedSupervisors = [];
        $this->selectedSupervisorID = null;
        $this->assignmentNotes = '';
        $this->quotaOverride = false;
        $this->overrideReason = '';
        $this->resetValidation();
    }

    public function toggleQuotaOverride()
    {
        $this->quotaOverride = !$this->quotaOverride;

        // Reload recommendations if student is selected
        if ($this->selectedStudent) {
            $this->recommendedSupervisors = $this->supervisorAssignmentService->getRecommendedSupervisors(
                $this->selectedStudent->studentID,
                10,
                $this->quotaOverride
            );
        }
    }

    public function assignSupervisor()
    {
        $this->validate([
            'selectedSupervisorID' => 'required|exists:lecturers,lecturerID',
            'assignmentNotes' => 'nullable|string|max:1000',
            'overrideReason' => 'required_if:quotaOverride,true|nullable|string|max:500',
        ], [
            'selectedSupervisorID.required' => 'Please select a supervisor.',
            'selectedSupervisorID.exists' => 'Selected supervisor does not exist.',
            'overrideReason.required_if' => 'Please provide a reason for quota override.',
        ]);

        try {
            $assignment = $this->supervisorAssignmentService->assignSupervisor(
                $this->selectedStudent->studentID,
                $this->selectedSupervisorID,
                null, // Will use current coordinator
                $this->assignmentNotes ?: null,
                $this->quotaOverride,
                $this->overrideReason ?: null
            );

            session()->flash('success', 'Supervisor assigned successfully!');
            $this->closeAssignModal();

            // Reset pagination to show the newly assigned student
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            Log::error('Failed to assign supervisor', [
                'student_id' => $this->selectedStudent->studentID,
                'supervisor_id' => $this->selectedSupervisorID,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function autoAssignSupervisor($studentID)
    {
        try {
            $assignment = $this->supervisorAssignmentService->autoAssignNearestSupervisor($studentID);

            if ($assignment) {
                session()->flash('success', 'Supervisor auto-assigned successfully!');
            } else {
                session()->flash('error', 'No available supervisors found for auto-assignment.');
            }

            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            Log::error('Failed to auto-assign supervisor', [
                'student_id' => $studentID,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function viewAssignment($assignmentID)
    {
        $this->selectedAssignmentID = $assignmentID;
        
        // Load the assignment with all relationships
        $assignment = SupervisorAssignment::with([
            'student.user',
            'supervisor.user',
            'assignedBy.user',
            'student.acceptedPlacementApplication'
        ])->find($assignmentID);
        
        if ($assignment) {
            // Store as array to avoid Livewire serialization issues
            $this->selectedAssignment = [
                'id' => $assignment->id,
                'student_name' => $assignment->student->user->name,
                'student_id' => $assignment->student->studentID,
                'student_program' => $assignment->student->program,
                'company_name' => $assignment->student->acceptedPlacementApplication->companyName ?? null,
                'company_city' => $assignment->student->acceptedPlacementApplication->companyCity ?? null,
                'company_state' => $assignment->student->acceptedPlacementApplication->companyState ?? null,
                'supervisor_name' => $assignment->supervisor->user->name,
                'supervisor_id' => $assignment->supervisor->lecturerID,
                'supervisor_department' => $assignment->supervisor->department,
                'supervisor_research_group' => $assignment->supervisor->researchGroup,
                'supervisor_position' => $assignment->supervisor->position,
                'assigned_by_name' => $assignment->assignedBy->user->name ?? 'N/A',
                'assigned_by_id' => $assignment->assignedBy->lecturerID ?? null,
                'status' => $assignment->status,
                'status_display' => $assignment->status_display,
                'assigned_at' => $assignment->assigned_at->format('Y-m-d H:i:s'),
                'distance_km' => $assignment->distance_km,
                'quota_override' => $assignment->quota_override,
                'override_reason' => $assignment->override_reason,
                'assignment_notes' => $assignment->assignment_notes,
            ];
            
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedAssignment = null;
        $this->selectedAssignmentID = null;
    }

    public function render()
    {
        $query = Student::query()
            ->with(['user', 'acceptedPlacementApplication', 'supervisorAssignment.supervisor.user'])
            ->whereHas('placementApplications', function ($q) {
                $q->where('studentAcceptance', 'Accepted');
            });

        // Filter by assignment type
        if ($this->assignmentTypeFilter === 'assigned') {
            $query->whereHas('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            });
        } elseif ($this->assignmentTypeFilter === 'unassigned') {
            $query->whereDoesntHave('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            });
        }

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('studentID', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('acceptedPlacementApplication', function ($appQuery) {
                        $appQuery->where('companyName', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Sort
        if ($this->sortField === 'assigned_at') {
            $query->leftJoin('supervisor_assignments', function ($join) {
                $join->on('students.studentID', '=', 'supervisor_assignments.studentID')
                    ->where('supervisor_assignments.status', '=', SupervisorAssignment::STATUS_ASSIGNED);
            })
            ->orderBy('supervisor_assignments.assigned_at', $this->sortDirection)
            ->select('students.*');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $students = $query->paginate($this->perPage);

        // Statistics
        $stats = [
            'total_eligible' => Student::whereHas('placementApplications', function ($q) {
                $q->where('studentAcceptance', 'Accepted');
            })->count(),
            'assigned' => Student::whereHas('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            })->whereHas('placementApplications', function ($q) {
                $q->where('studentAcceptance', 'Accepted');
            })->count(),
            'unassigned' => Student::whereHas('placementApplications', function ($q) {
                $q->where('studentAcceptance', 'Accepted');
            })->whereDoesntHave('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            })->count(),
        ];

        return view('livewire.supervisor-assignment-table', [
            'students' => $students,
            'stats' => $stats,
        ]);
    }
}
