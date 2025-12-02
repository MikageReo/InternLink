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
use App\Services\SupervisorRecommendationService;
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
    public $semesterFilter = '';
    public $yearFilter = '';
    public $program = '';

    // Modal properties
    public $showAssignModal = false;
    public $showDetailModal = false;
    public $showEditModal = false;
    public $selectedStudent = null;
    public $selectedAssignment = null;
    public $selectedAssignmentID = null;
    public $recommendedSupervisors = [];
    public $selectedSupervisorID = null;
    public $assignmentNotes = '';
    public $quotaOverride = false;
    public $overrideReason = '';
    public $showOverrideModal = false;
    public $editAssignmentID = null;
    public $newSupervisorID = null;

    // Bulk selection properties
    public $selectedStudents = [];
    public $selectAll = false;
    public $isBulkAssigning = false;
    public $bulkAssignProgress = 0;
    public $bulkAssignTotal = 0;
    public $bulkAssignResults = [
        'success' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    protected $supervisorAssignmentService;
    protected $geocodingService;
    protected $supervisorRecommendationService;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'assigned_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'assignmentTypeFilter' => ['except' => 'unassigned'],
        'semesterFilter' => ['except' => ''],
        'yearFilter' => ['except' => ''],
        'program' => ['except' => ''],
    ];

    public function boot(
        SupervisorAssignmentService $supervisorAssignmentService,
        GeocodingService $geocodingService,
        SupervisorRecommendationService $supervisorRecommendationService
    ) {
        $this->supervisorAssignmentService = $supervisorAssignmentService;
        $this->geocodingService = $geocodingService;
        $this->supervisorRecommendationService = $supervisorRecommendationService;
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

    public function updatingSemesterFilter()
    {
        $this->resetPage();
    }

    public function updatingYearFilter()
    {
        $this->resetPage();
    }

    public function updatingProgram()
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

        // Get recommended supervisors with scores using SupervisorRecommendationService
        $recommendations = $this->supervisorRecommendationService->getRecommendedSupervisors(
            $this->selectedStudent,
            10,
            $this->quotaOverride
        );

        // Convert to array format that Livewire can serialize
        // Note: Lecturer models will be serialized by Livewire automatically
        $this->recommendedSupervisors = $recommendations->map(function($rec) {
            return [
                'lecturer' => $rec['lecturer'],
                'score' => $rec['score'],
                'breakdown' => $rec['breakdown'],
                'distance_km' => $rec['distance_km'],
                'distance' => $rec['distance_km'],
                'available_quota' => $rec['available_quota'],
            ];
        })->values()->toArray();

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
            $recommendations = $this->supervisorRecommendationService->getRecommendedSupervisors(
                $this->selectedStudent,
                10,
                $this->quotaOverride
            );

            // Convert to array format that Livewire can serialize
            // Note: Lecturer models will be serialized by Livewire automatically
            $this->recommendedSupervisors = $recommendations->map(function($rec) {
                return [
                    'lecturer' => $rec['lecturer'],
                    'score' => $rec['score'],
                    'breakdown' => $rec['breakdown'],
                    'distance_km' => $rec['distance_km'],
                    'distance' => $rec['distance_km'],
                    'available_quota' => $rec['available_quota'],
                ];
            })->values()->toArray();
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

    /**
     * Toggle student selection for bulk operations
     * Only allows selection of unassigned students
     */
    public function toggleStudentSelection($studentID)
    {
        // Check if student already has an assignment
        $hasAssignment = SupervisorAssignment::where('studentID', $studentID)
            ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
            ->exists();

        if ($hasAssignment) {
            // Cannot select assigned students
            return;
        }

        if (in_array($studentID, $this->selectedStudents)) {
            $this->selectedStudents = array_values(array_diff($this->selectedStudents, [$studentID]));
        } else {
            $this->selectedStudents[] = $studentID;
        }
        $this->updateSelectAllState();
    }

    /**
     * Toggle select all students on current page
     * Only selects unassigned students
     */
    public function toggleSelectAll()
    {
        // Get current page student IDs from the render query
        $query = Student::query()
            ->with(['user', 'acceptedPlacementApplication', 'supervisorAssignment.supervisor.user'])
            ->whereHas('placementApplications', function ($q) {
                $q->where('studentAcceptance', 'Accepted');
            });

        // Apply same filters as render method
        if ($this->assignmentTypeFilter === 'assigned') {
            $query->whereHas('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            });
        } elseif ($this->assignmentTypeFilter === 'unassigned') {
            $query->whereDoesntHave('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            });
        }

        if ($this->semesterFilter) {
            $query->where('semester', $this->semesterFilter);
        }

        if ($this->yearFilter) {
            $query->where('year', $this->yearFilter);
        }

        if ($this->program) {
            $programFullName = $this->getProgramFullName($this->program);
            if ($programFullName) {
                $query->where('program', $programFullName);
            }
        }

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

        // Filter to only unassigned students
        $unassignedStudentIDs = $students->filter(function ($student) {
            return !$student->supervisorAssignment ||
                $student->supervisorAssignment->status !== SupervisorAssignment::STATUS_ASSIGNED;
        })->pluck('studentID')->toArray();

        // Get currently selected unassigned students from current page
        $selectedUnassignedOnPage = array_intersect($this->selectedStudents, $unassignedStudentIDs);

        // Check if all unassigned students on current page are selected
        $allUnassignedSelected = !empty($unassignedStudentIDs) &&
            count($selectedUnassignedOnPage) === count($unassignedStudentIDs);

        if ($allUnassignedSelected) {
            // Deselect all unassigned students from current page
            $this->selectedStudents = array_values(array_diff($this->selectedStudents, $unassignedStudentIDs));
            $this->selectAll = false;
        } else {
            // Select all unassigned students on current page
            $this->selectedStudents = array_unique(array_merge($this->selectedStudents, $unassignedStudentIDs));
            $this->selectAll = true;
        }
    }

    /**
     * Update select all state based on current selections
     */
    protected function updateSelectAllState()
    {
        // This will be called after render, so we'll check in the view instead
        // The selectAll state will be computed in the view
    }

    /**
     * Check if student is selected
     */
    public function isStudentSelected($studentID): bool
    {
        return in_array($studentID, $this->selectedStudents);
    }

    /**
     * Check if student can be selected (must be unassigned)
     */
    public function canSelectStudent($studentID): bool
    {
        $hasAssignment = SupervisorAssignment::where('studentID', $studentID)
            ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
            ->exists();

        return !$hasAssignment;
    }

    /**
     * Clear all selections
     */
    public function clearSelection()
    {
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    /**
     * Bulk auto-assign supervisors to selected students
     */
    public function bulkAutoAssign()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Please select at least one student.');
            return;
        }

        // Filter out any assigned students (safety check)
        $unassignedStudents = [];
        foreach ($this->selectedStudents as $studentID) {
            $hasAssignment = SupervisorAssignment::where('studentID', $studentID)
                ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
                ->exists();

            if (!$hasAssignment) {
                $unassignedStudents[] = $studentID;
            }
        }

        if (empty($unassignedStudents)) {
            session()->flash('error', 'No unassigned students selected. Please select students without supervisors.');
            return;
        }

        $this->isBulkAssigning = true;
        $this->bulkAssignTotal = count($unassignedStudents);
        $this->bulkAssignProgress = 0;
        $this->bulkAssignResults = ['success' => 0, 'failed' => 0, 'skipped' => 0];

        try {
            foreach ($unassignedStudents as $studentID) {
                try {
                    // Check if student already has an assignment
                    $existingAssignment = SupervisorAssignment::where('studentID', $studentID)
                        ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
                        ->first();

                    if ($existingAssignment) {
                        $this->bulkAssignResults['skipped']++;
                        $this->bulkAssignProgress++;
                        continue;
                    }

                    // Try to auto-assign
                    $assignment = $this->supervisorAssignmentService->autoAssignNearestSupervisor($studentID);

                    if ($assignment) {
                        $this->bulkAssignResults['success']++;
                    } else {
                        $this->bulkAssignResults['failed']++;
                    }
                } catch (\Exception $e) {
                    $this->bulkAssignResults['failed']++;
                    Log::error('Failed to auto-assign supervisor in bulk operation', [
                        'student_id' => $studentID,
                        'error' => $e->getMessage(),
                    ]);
                }

                $this->bulkAssignProgress++;
            }

            // Show results
            $message = sprintf(
                'Bulk auto-assignment completed: %d successful, %d failed, %d skipped.',
                $this->bulkAssignResults['success'],
                $this->bulkAssignResults['failed'],
                $this->bulkAssignResults['skipped']
            );

            if ($this->bulkAssignResults['success'] > 0) {
                session()->flash('success', $message);
            } elseif ($this->bulkAssignResults['failed'] > 0) {
                session()->flash('error', $message);
            } else {
                session()->flash('info', $message);
            }

            // Clear selection and reset
            $this->clearSelection();
            $this->resetPage();
        } finally {
            $this->isBulkAssigning = false;
            $this->bulkAssignProgress = 0;
            $this->bulkAssignTotal = 0;
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

    public function openEditModal($assignmentID)
    {
        $assignment = SupervisorAssignment::with(['student.user', 'supervisor.user'])
            ->findOrFail($assignmentID);

        $this->editAssignmentID = $assignmentID;
        $this->newSupervisorID = $assignment->supervisor->lecturerID;
        $this->assignmentNotes = $assignment->assignment_notes;

        // Get recommended supervisors for reassignment
        $this->recommendedSupervisors = $this->supervisorAssignmentService->getRecommendedSupervisors(
            $assignment->studentID,
            10,
            true // Include full quota for editing
        );

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editAssignmentID = null;
        $this->newSupervisorID = null;
        $this->assignmentNotes = '';
        $this->recommendedSupervisors = [];
        $this->resetValidation();
    }

    public function updateAssignment()
    {
        $this->validate([
            'newSupervisorID' => 'required|exists:lecturers,lecturerID',
            'assignmentNotes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Get fresh assignment data
            $assignment = SupervisorAssignment::lockForUpdate()->findOrFail($this->editAssignmentID);
            $oldSupervisorID = $assignment->supervisorID;
            $oldSupervisor = Lecturer::lockForUpdate()->where('lecturerID', $oldSupervisorID)->firstOrFail();
            $student = $assignment->student;

            // Calculate new distance if supervisor changed
            $newDistance = $assignment->distance_km;
            if ($oldSupervisorID !== $this->newSupervisorID) {
                $newSupervisor = Lecturer::lockForUpdate()->where('lecturerID', $this->newSupervisorID)->firstOrFail();
                $placement = $student->acceptedPlacementApplication;

                // Recalculate distance
                if (
                    $placement && $placement->companyLatitude && $placement->companyLongitude
                    && $newSupervisor->latitude && $newSupervisor->longitude
                ) {
                    $newDistance = $this->geocodingService->calculateDistance(
                        (float) $placement->companyLatitude,
                        (float) $placement->companyLongitude,
                        (float) $newSupervisor->latitude,
                        (float) $newSupervisor->longitude
                    );
                } elseif (
                    $student->latitude && $student->longitude
                    && $newSupervisor->latitude && $newSupervisor->longitude
                ) {
                    $newDistance = $this->geocodingService->calculateDistance(
                        (float) $student->latitude,
                        (float) $student->longitude,
                        (float) $newSupervisor->latitude,
                        (float) $newSupervisor->longitude
                    );
                } else {
                    $newDistance = null;
                }

                // Update quota counts BEFORE updating assignment
                // Decrease old supervisor's count (only if assignment status is 'assigned')
                if ($assignment->status === SupervisorAssignment::STATUS_ASSIGNED) {
                    $oldSupervisor->decrement('current_assignments');
                }

                // Update assignment
                $assignment->update([
                    'supervisorID' => $this->newSupervisorID,
                    'assignment_notes' => $this->assignmentNotes,
                    'distance_km' => $newDistance,
                ]);

                // Increase new supervisor's count (only if assignment status is 'assigned')
                if ($assignment->status === SupervisorAssignment::STATUS_ASSIGNED) {
                    $newSupervisor->increment('current_assignments');
                }

                // Refresh models to get updated counts
                $oldSupervisor->refresh();
                $newSupervisor->refresh();
            } else {
                // Supervisor didn't change, just update notes
                $assignment->update([
                    'assignment_notes' => $this->assignmentNotes,
                ]);
            }

            DB::commit();

            session()->flash('success', 'Supervisor assignment updated successfully!');
            $this->closeEditModal();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to update assignment: ' . $e->getMessage());
            Log::error('Failed to update supervisor assignment', [
                'assignment_id' => $this->editAssignmentID,
                'old_supervisor_id' => $oldSupervisorID ?? 'N/A',
                'new_supervisor_id' => $this->newSupervisorID ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function removeAssignment($assignmentID)
    {
        DB::beginTransaction();
        try {
            $assignment = SupervisorAssignment::lockForUpdate()->findOrFail($assignmentID);
            $supervisorID = $assignment->supervisorID;
            $assignmentStatus = $assignment->status;

            // Get supervisor with lock
            $supervisor = Lecturer::lockForUpdate()->where('lecturerID', $supervisorID)->firstOrFail();

            // Delete assignment
            $assignment->delete();

            // Decrease supervisor's count only if assignment was in 'assigned' status
            if ($assignmentStatus === SupervisorAssignment::STATUS_ASSIGNED) {
                $supervisor->decrement('current_assignments');
                $supervisor->refresh();
            }

            DB::commit();

            session()->flash('success', 'Supervisor assignment removed successfully!');
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to remove assignment: ' . $e->getMessage());
            Log::error('Failed to remove supervisor assignment', [
                'assignment_id' => $assignmentID,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get program mapping from code to full name for filtering
     */
    private function getProgramFullName($code): ?string
    {
        $programs = [
            'BCS' => 'Bachelor of Computer Science (Software Engineering) with Honours',
            'BCN' => 'Bachelor of Computer Science (Computer Systems & Networking) with Honours',
            'BCM' => 'Bachelor of Computer Science (Multimedia Software) with Honours',
            'BCY' => 'Bachelor of Computer Science (Cyber Security) with Honours',
            'DRC' => 'Diploma in Computer Science',
        ];

        return $programs[$code] ?? null;
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

        // Filter by semester
        if ($this->semesterFilter) {
            $query->where('semester', $this->semesterFilter);
        }

        // Filter by year
        if ($this->yearFilter) {
            $query->where('year', $this->yearFilter);
        }

        // Program filter
        if ($this->program) {
            $programFullName = $this->getProgramFullName($this->program);
            if ($programFullName) {
                $query->where('program', $programFullName);
            }
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

        // Get available years and semesters for filters
        $availableYears = Student::distinct()->pluck('year')->filter()->sort()->values();
        $availableSemesters = [1, 2];

        return view('livewire.supervisor-assignment-table', [
            'students' => $students,
            'stats' => $stats,
            'availableYears' => $availableYears,
            'availableSemesters' => $availableSemesters,
        ]);
    }
}
