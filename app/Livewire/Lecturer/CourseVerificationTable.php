<?php

namespace App\Livewire\Lecturer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CourseVerification;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CourseVerificationStatusNotification;

class CourseVerificationTable extends Component
{
    use WithPagination;

    // Search and sort properties
    public $search = '';
    public $sortField = 'applicationDate';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $statusFilter = '';
    public $program = '';
    public $semester = '';
    public $year = '';

    // Modal properties
    public $showDetailModal = false;
    public $selectedApplication = null;
    public $remarks = '';

    // Bulk selection properties
    public $selectedApplications = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'applicationDate'],
        'sortDirection' => ['except' => 'asc'],
        'statusFilter' => ['except' => ''],
        'program' => ['except' => ''],
        'semester' => ['except' => ''],
        'year' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingProgram()
    {
        $this->resetPage();
    }

    public function updatingSemester()
    {
        $this->resetPage();
    }

    public function updatingYear()
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

    public function viewApplication($id)
    {
        $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'academicAdvisor', 'files'])
            ->findOrFail($id);

        // Load existing remarks if any
        $this->remarks = $this->selectedApplication->remarks ?? '';

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedApplication = null;
        $this->remarks = '';
    }

    public function approveApplication($id)
    {
        try {
            $application = CourseVerification::findOrFail($id);
            $lecturer = Auth::user()->lecturer;

            if (!$lecturer) {
                session()->flash('error', 'Lecturer profile not found.');
                return;
            }

            // Check if user is academic advisor or coordinator
            $hasCoordinatorAndAcademicAdvisor = $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
            $hasCommitteeAndAcademicAdvisor = $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            $hasAllThreeRoles = $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            
            if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                // If lecturer has Academic Advisor combined with Coordinator/Committee, 
                // check which role they're acting as based on application status
                $application = CourseVerification::findOrFail($id);
                
                // If academic advisor hasn't reviewed yet, act as academic advisor
                if ($application->academicAdvisorStatus === null && 
                    $application->student->academicAdvisorID === $lecturer->lecturerID) {
                    $this->approveAsAcademicAdvisor($id, $lecturer);
                } 
                // If academic advisor has approved, act as coordinator
                elseif ($application->academicAdvisorStatus === 'approved') {
                    $this->approveAsCoordinator($id, $lecturer);
                } else {
                    session()->flash('error', 'This application cannot be approved in its current state.');
                    return;
                }
            } elseif ($lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
                // Academic Advisor approval - check eligibility
                $this->approveAsAcademicAdvisor($id, $lecturer);
            } elseif ($lecturer->isCoordinator || $lecturer->isCommittee) {
                // Coordinator/Committee approval - final approval
                $this->approveAsCoordinator($id, $lecturer);
            } else {
                session()->flash('error', 'You do not have permission to approve applications.');
                return;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while approving the application: ' . $e->getMessage());
        }
    }

    private function approveAsAcademicAdvisor($id, $lecturer)
    {
        $application = CourseVerification::findOrFail($id);

        // Verify this is the student's academic advisor
        if ($application->student->academicAdvisorID !== $lecturer->lecturerID) {
            session()->flash('error', 'You can only review applications from your advisees.');
            return;
        }

        // Check if already reviewed by academic advisor
        if ($application->academicAdvisorStatus !== null) {
            session()->flash('error', 'This application has already been reviewed by an academic advisor.');
            return;
        }

        // Update academic advisor approval
        $updated = $application->update([
            'academicAdvisorStatus' => 'approved',
            'academicAdvisorID' => $lecturer->lecturerID,
            'remarks' => $this->remarks ?: 'Eligible for coordinator review.',
        ]);

        if ($updated) {
            $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'academicAdvisor', 'files'])
                ->findOrFail($id);

            // Send email notification to coordinators
            try {
                $coordinators = Lecturer::where(function($q) {
                    $q->where('isCoordinator', true)
                      ->orWhere('isCommittee', true);
                })->where('status', 'active')
                  ->with('user')
                  ->get();

                foreach ($coordinators as $coordinator) {
                    if ($coordinator->user) {
                        $coordinator->user->notify(
                            new \App\Notifications\CourseVerificationCoordinatorNotification($this->selectedApplication)
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send coordinator notification: ' . $e->getMessage());
            }

            session()->flash('message', 'Application marked as eligible! It will now appear for coordinator review. Coordinators have been notified.');

            $this->resetPage();
            $this->dispatch('application-status-updated');
        } else {
            session()->flash('error', 'Failed to update application status.');
        }
    }

    private function approveAsCoordinator($id, $lecturer)
    {
        $application = CourseVerification::findOrFail($id);

        // Check if academic advisor has approved
        if ($application->academicAdvisorStatus !== 'approved') {
            session()->flash('error', 'This application must be approved by the academic advisor first.');
            return;
        }

        // Update final approval
        $updated = $application->update([
            'status' => 'approved',
            'lecturerID' => $lecturer->lecturerID,
            'remarks' => $this->remarks,
        ]);

        if ($updated) {
            $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'academicAdvisor', 'files'])
                ->findOrFail($id);

            // Send email notification to student
            try {
                $this->selectedApplication->student->user->notify(
                    new CourseVerificationStatusNotification($this->selectedApplication)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send approval notification: ' . $e->getMessage());
            }

            session()->flash('message', 'Application approved successfully! Email notification sent to student.');

            $this->resetPage();
            $this->dispatch('application-status-updated');
        } else {
            session()->flash('error', 'Failed to update application status.');
        }
    }

    public function rejectApplication($id)
    {
        try {
            $application = CourseVerification::findOrFail($id);
            $lecturer = Auth::user()->lecturer;

            if (!$lecturer) {
                session()->flash('error', 'Lecturer profile not found.');
                return;
            }

            // Check if user is academic advisor or coordinator
            $hasCoordinatorAndAcademicAdvisor = $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
            $hasCommitteeAndAcademicAdvisor = $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            $hasAllThreeRoles = $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            
            if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                // If lecturer has Academic Advisor combined with Coordinator/Committee, 
                // check which role they're acting as based on application status
                $application = CourseVerification::findOrFail($id);
                
                // If academic advisor hasn't reviewed yet, act as academic advisor
                if ($application->academicAdvisorStatus === null && 
                    $application->student->academicAdvisorID === $lecturer->lecturerID) {
                    $this->rejectAsAcademicAdvisor($id, $lecturer);
                } 
                // If academic advisor has approved, act as coordinator
                elseif ($application->academicAdvisorStatus === 'approved') {
                    $this->rejectAsCoordinator($id, $lecturer);
                } else {
                    session()->flash('error', 'This application cannot be rejected in its current state.');
                    return;
                }
            } elseif ($lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
                // Academic Advisor rejection
                $this->rejectAsAcademicAdvisor($id, $lecturer);
            } elseif ($lecturer->isCoordinator || $lecturer->isCommittee) {
                // Coordinator/Committee rejection
                $this->rejectAsCoordinator($id, $lecturer);
            } else {
                session()->flash('error', 'You do not have permission to reject applications.');
                return;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while rejecting the application: ' . $e->getMessage());
        }
    }

    private function rejectAsAcademicAdvisor($id, $lecturer)
    {
        $application = CourseVerification::findOrFail($id);

        // Verify this is the student's academic advisor
        if ($application->student->academicAdvisorID !== $lecturer->lecturerID) {
            session()->flash('error', 'You can only review applications from your advisees.');
            return;
        }

        // Check if already reviewed
        if ($application->academicAdvisorStatus !== null) {
            session()->flash('error', 'This application has already been reviewed by an academic advisor.');
            return;
        }

        if (empty($this->remarks)) {
            session()->flash('error', 'Please provide remarks for rejection.');
            return;
        }

        // Update academic advisor rejection
        $updated = $application->update([
            'academicAdvisorStatus' => 'rejected',
            'academicAdvisorID' => $lecturer->lecturerID,
            'status' => 'rejected', // Also set final status to rejected
            'lecturerID' => $lecturer->lecturerID,
            'remarks' => $this->remarks,
        ]);

        if ($updated) {
            $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'academicAdvisor', 'files'])
                ->findOrFail($id);

            // Send email notification to student
            try {
                $this->selectedApplication->student->user->notify(
                    new CourseVerificationStatusNotification($this->selectedApplication)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send rejection notification: ' . $e->getMessage());
            }

            session()->flash('message', 'Application rejected successfully! Email notification sent to student.');

            $this->resetPage();
            $this->dispatch('application-status-updated');
        } else {
            session()->flash('error', 'Failed to update application status.');
        }
    }

    private function rejectAsCoordinator($id, $lecturer)
    {
        $application = CourseVerification::findOrFail($id);

        // Check if academic advisor has approved
        if ($application->academicAdvisorStatus !== 'approved') {
            session()->flash('error', 'This application must be approved by the academic advisor first.');
            return;
        }

        if (empty($this->remarks)) {
            session()->flash('error', 'Please provide remarks for rejection.');
            return;
        }

        // Update final rejection
        $updated = $application->update([
            'status' => 'rejected',
            'lecturerID' => $lecturer->lecturerID,
            'remarks' => $this->remarks,
        ]);

        if ($updated) {
            $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'academicAdvisor', 'files'])
                ->findOrFail($id);

            // Send email notification to student
            try {
                $this->selectedApplication->student->user->notify(
                    new CourseVerificationStatusNotification($this->selectedApplication)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send rejection notification: ' . $e->getMessage());
            }

            session()->flash('message', 'Application rejected successfully! Email notification sent to student.');

            $this->resetPage();
            $this->dispatch('application-status-updated');
        } else {
            session()->flash('error', 'Failed to update application status.');
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'program', 'semester', 'year', 'sortField', 'sortDirection']);
        $this->sortField = 'applicationDate';
        $this->sortDirection = 'asc';
        $this->resetPage();
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

    private function getFilteredApplications()
    {
        $lecturer = Auth::user()->lecturer;

        $query = CourseVerification::with(['student.user', 'lecturer', 'academicAdvisor', 'files']);

        // Filter based on lecturer role
        // Show applications if lecturer has:
        // - (Coordinator AND Academic Advisor) OR
        // - (Committee AND Academic Advisor) OR
        // - (Coordinator AND Committee AND Academic Advisor) OR
        // - (Academic Advisor only) OR
        // - (Coordinator/Committee only)
        if ($lecturer) {
            $hasCoordinatorAndAcademicAdvisor = $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
            $hasCommitteeAndAcademicAdvisor = $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            $hasAllThreeRoles = $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            $isAcademicAdvisorOnly = $lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee;
            $isCoordinatorOrCommitteeOnly = ($lecturer->isCoordinator || $lecturer->isCommittee) && !$lecturer->isAcademicAdvisor;
            
            if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                // If lecturer has Academic Advisor combined with Coordinator/Committee, show both:
                // 1. Their advisees' applications (as academic advisor)
                // 2. Applications approved by academic advisor (as coordinator/committee)
                $query->where(function($q) use ($lecturer) {
                    $q->whereHas('student', function ($subQ) use ($lecturer) {
                        $subQ->where('academicAdvisorID', $lecturer->lecturerID);
                    })
                    ->orWhere('academicAdvisorStatus', 'approved');
                });
            } elseif ($isAcademicAdvisorOnly) {
                // Academic Advisor only: Show all their advisees' applications (pending and reviewed history)
                $query->whereHas('student', function ($q) use ($lecturer) {
                    $q->where('academicAdvisorID', $lecturer->lecturerID);
                });
            } elseif ($isCoordinatorOrCommitteeOnly) {
                // Coordinator/Committee only: Show all applications approved by academic advisor (pending and reviewed history)
                $query->where('academicAdvisorStatus', 'approved');
            }
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('currentCredit', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
                    ->orWhere('academicAdvisorStatus', 'like', '%' . $this->search . '%')
                    ->orWhere('applicationDate', 'like', '%' . $this->search . '%')
                    ->orWhere('courseVerificationID', 'like', '%' . $this->search . '%')
                    ->orWhereHas('student', function ($subQ) {
                        $subQ->where('studentID', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('student.user', function ($subQ) {
                        $subQ->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $hasCoordinatorAndAcademicAdvisor = $lecturer && $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
            $hasCommitteeAndAcademicAdvisor = $lecturer && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            $hasAllThreeRoles = $lecturer && $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            
            if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                // For lecturers with combined roles, allow filtering by both statuses
                // This is complex, so we'll filter by the most relevant status
                if ($this->statusFilter === 'pending') {
                    $query->where(function($q) {
                        $q->whereNull('academicAdvisorStatus')
                          ->orWhere('status', 'pending');
                    });
                } else {
                    // For approved/rejected, check both statuses
                    $query->where(function($q) {
                        $q->where('academicAdvisorStatus', $this->statusFilter)
                          ->orWhere('status', $this->statusFilter);
                    });
                }
            } elseif ($lecturer && $lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
                // For academic advisors, filter by academicAdvisorStatus
                if ($this->statusFilter === 'pending') {
                    $query->whereNull('academicAdvisorStatus');
                } else {
                    $query->where('academicAdvisorStatus', $this->statusFilter);
                }
            } else {
                // For coordinators, filter by final status
                $query->where('status', $this->statusFilter);
            }
        }

        // Apply program filter
        if ($this->program) {
            $programFullName = $this->getProgramFullName($this->program);
            if ($programFullName) {
                $query->whereHas('student', function ($q) use ($programFullName) {
                    $q->where('program', $programFullName);
                });
            }
        }

        // Semester filter
        if ($this->semester) {
            $query->whereHas('student', function ($q) {
                $q->where('semester', $this->semester);
            });
        }

        // Year filter
        if ($this->year) {
            $query->whereHas('student', function ($q) {
                $q->where('year', $this->year);
            });
        }

        // Apply custom sorting - prioritize pending status and oldest applications
        if ($this->sortField === 'applicationDate') {
            $hasCoordinatorAndAcademicAdvisor = $lecturer && $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
            $hasCommitteeAndAcademicAdvisor = $lecturer && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            $hasAllThreeRoles = $lecturer && $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            
            if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                // For combined roles, prioritize by both academic advisor and coordinator status
                $query->orderByRaw(
                    "
                    CASE
                        WHEN academicAdvisorStatus IS NULL THEN 1
                        WHEN academicAdvisorStatus = 'approved' AND status = 'pending' THEN 2
                        WHEN academicAdvisorStatus = 'approved' AND status = 'approved' THEN 3
                        WHEN academicAdvisorStatus = 'approved' AND status = 'rejected' THEN 4
                        WHEN academicAdvisorStatus = 'rejected' THEN 5
                        ELSE 6
                    END ASC,
                    applicationDate " . $this->sortDirection
                );
            } elseif ($lecturer && $lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
                // For academic advisors, prioritize by academicAdvisorStatus
                $query->orderByRaw(
                    "
                    CASE
                        WHEN academicAdvisorStatus IS NULL THEN 1
                        WHEN academicAdvisorStatus = 'approved' THEN 2
                        WHEN academicAdvisorStatus = 'rejected' THEN 3
                        ELSE 4
                    END ASC,
                    applicationDate " . $this->sortDirection
                );
            } else {
                // For coordinators, prioritize by final status
                $query->orderByRaw(
                    "
                    CASE
                        WHEN status = 'pending' THEN 1
                        WHEN status = 'approved' THEN 2
                        WHEN status = 'rejected' THEN 3
                        ELSE 4
                    END ASC,
                    applicationDate " . $this->sortDirection
                );
            }
        } else {
            // Apply regular sorting
            if (in_array($this->sortField, ['status', 'created_at', 'courseVerificationID'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            } elseif ($this->sortField === 'studentName') {
                $query->join('students', 'course_verifications.studentID', '=', 'students.studentID')
                    ->join('users', 'students.user_id', '=', 'users.id')
                    ->orderBy('users.name', $this->sortDirection)
                    ->select('course_verifications.*');
            } elseif ($this->sortField === 'studentID') {
                $query->orderBy('studentID', $this->sortDirection);
            }
        }

        return $query;
    }

    public function downloadFile($fileId)
    {
        $file = File::find($fileId);

        if (!$file || !Storage::disk('public')->exists($file->file_path)) {
            session()->flash('error', 'File not found.');
            return;
        }

        // Use the stored filename (CourseVerification{UserID}.{extension}) instead of original_name
        $downloadName = basename($file->file_path);
        
        return response()->download(Storage::disk('public')->path($file->file_path), $downloadName);
    }

    // Bulk selection methods
    public function updatedSelectAll($value)
    {
        if ($value) {
            $lecturer = Auth::user()->lecturer;
            $query = $this->getFilteredApplications();

            // Select all eligible applications on current page
            $hasCoordinatorAndAcademicAdvisor = $lecturer && $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
            $hasCommitteeAndAcademicAdvisor = $lecturer && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            $hasAllThreeRoles = $lecturer && $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
            
            if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                // For combined roles, select both pending academic advisor reviews and pending coordinator reviews
                $this->selectedApplications = $query->where(function($q) {
                    $q->whereNull('academicAdvisorStatus')
                      ->orWhere(function($subQ) {
                          $subQ->where('academicAdvisorStatus', 'approved')
                               ->where('status', 'pending');
                      });
                })->pluck('courseVerificationID')->toArray();
            } elseif ($lecturer && $lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
                // Academic advisor: select only applications not yet reviewed (can't bulk select already reviewed ones)
                $this->selectedApplications = $query->whereNull('academicAdvisorStatus')
                    ->pluck('courseVerificationID')
                    ->toArray();
            } else {
                // Coordinator: select applications approved by academic advisor but pending coordinator approval
                $this->selectedApplications = $query->where('status', 'pending')
                    ->pluck('courseVerificationID')
                    ->toArray();
            }
        } else {
            $this->selectedApplications = [];
        }
    }

    public function bulkApprove()
    {
        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to approve.');
            return;
        }

        try {
            $lecturer = Auth::user()->lecturer;

            if (!$lecturer) {
                session()->flash('error', 'Lecturer profile not found.');
                return;
            }

            $count = 0;
            foreach ($this->selectedApplications as $id) {
                $application = CourseVerification::with('student')->find($id);

                if (!$application) continue;

                $hasCoordinatorAndAcademicAdvisor = $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
                $hasCommitteeAndAcademicAdvisor = $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
                $hasAllThreeRoles = $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
                
                if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                    // For combined roles, check which role to act as
                    if ($application->academicAdvisorStatus === null &&
                        $application->student->academicAdvisorID === $lecturer->lecturerID) {
                        // Act as academic advisor
                        $application->update([
                            'academicAdvisorStatus' => 'approved',
                            'academicAdvisorID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks ?: 'Eligible for coordinator review.',
                        ]);
                        
                        // Send email notification to coordinators (excluding self)
                        try {
                            $coordinators = Lecturer::where(function($q) {
                                $q->where('isCoordinator', true)
                                  ->orWhere('isCommittee', true);
                            })->where('status', 'active')
                              ->where('lecturerID', '!=', $lecturer->lecturerID)
                              ->with('user')
                              ->get();

                            foreach ($coordinators as $coordinator) {
                                if ($coordinator->user) {
                                    $coordinator->user->notify(
                                        new \App\Notifications\CourseVerificationCoordinatorNotification($application->fresh())
                                    );
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to send coordinator notification: ' . $e->getMessage());
                        }
                        
                        $count++;
                    } elseif ($application->academicAdvisorStatus === 'approved' && $application->status === 'pending') {
                        // Act as coordinator
                        $application->update([
                            'status' => 'approved',
                            'lecturerID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks ?: 'Approved',
                        ]);

                        // Send notification
                        try {
                            $application->load('student.user');
                            $application->student->user->notify(
                                new CourseVerificationStatusNotification($application)
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send approval notification: ' . $e->getMessage());
                        }

                        $count++;
                    }
                } elseif ($lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
                    // Academic advisor bulk approval
                    if ($application->academicAdvisorStatus === null &&
                        $application->student->academicAdvisorID === $lecturer->lecturerID) {
                        $application->update([
                            'academicAdvisorStatus' => 'approved',
                            'academicAdvisorID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks ?: 'Eligible for coordinator review.',
                        ]);
                        
                        // Send email notification to coordinators
                        try {
                            $coordinators = Lecturer::where(function($q) {
                                $q->where('isCoordinator', true)
                                  ->orWhere('isCommittee', true);
                            })->where('status', 'active')
                              ->with('user')
                              ->get();

                            foreach ($coordinators as $coordinator) {
                                if ($coordinator->user) {
                                    $coordinator->user->notify(
                                        new \App\Notifications\CourseVerificationCoordinatorNotification($application->fresh())
                                    );
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to send coordinator notification: ' . $e->getMessage());
                        }
                        
                        $count++;
                    }
                } elseif ($lecturer->isCoordinator || $lecturer->isCommittee) {
                    // Coordinator bulk approval
                    if ($application->academicAdvisorStatus === 'approved' && $application->status === 'pending') {
                        $application->update([
                            'status' => 'approved',
                            'lecturerID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks ?: 'Approved',
                        ]);

                        // Send notification
                        try {
                            $application->load('student.user');
                            $application->student->user->notify(
                                new CourseVerificationStatusNotification($application)
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send approval notification: ' . $e->getMessage());
                        }

                        $count++;
                    }
                }
            }

            session()->flash('message', "Successfully approved {$count} application(s)! Email notifications sent to students.");
            $this->selectedApplications = [];
            $this->selectAll = false;
            $this->remarks = '';
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred during bulk approval: ' . $e->getMessage());
        }
    }

    public function bulkReject()
    {
        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to reject.');
            return;
        }

        if (empty($this->remarks)) {
            session()->flash('error', 'Please provide remarks for rejection.');
            return;
        }

        try {
            $lecturer = Auth::user()->lecturer;

            if (!$lecturer) {
                session()->flash('error', 'Lecturer profile not found.');
                return;
            }

            $count = 0;
            foreach ($this->selectedApplications as $id) {
                $application = CourseVerification::with('student')->find($id);

                if (!$application) continue;

                $hasCoordinatorAndAcademicAdvisor = $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
                $hasCommitteeAndAcademicAdvisor = $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
                $hasAllThreeRoles = $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
                
                if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
                    // For combined roles, check which role to act as
                    if ($application->academicAdvisorStatus === null &&
                        $application->student->academicAdvisorID === $lecturer->lecturerID) {
                        // Act as academic advisor
                        $application->update([
                            'academicAdvisorStatus' => 'rejected',
                            'academicAdvisorID' => $lecturer->lecturerID,
                            'status' => 'rejected',
                            'lecturerID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks,
                        ]);

                        // Send notification
                        try {
                            $application->load('student.user');
                            $application->student->user->notify(
                                new CourseVerificationStatusNotification($application)
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send rejection notification: ' . $e->getMessage());
                        }

                        $count++;
                    } elseif ($application->academicAdvisorStatus === 'approved' && $application->status === 'pending') {
                        // Act as coordinator
                        $application->update([
                            'status' => 'rejected',
                            'lecturerID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks,
                        ]);

                        // Send notification
                        try {
                            $application->load('student.user');
                            $application->student->user->notify(
                                new CourseVerificationStatusNotification($application)
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send rejection notification: ' . $e->getMessage());
                        }

                        $count++;
                    }
                } elseif ($lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
                    // Academic advisor bulk rejection
                    if ($application->academicAdvisorStatus === null &&
                        $application->student->academicAdvisorID === $lecturer->lecturerID) {
                        $application->update([
                            'academicAdvisorStatus' => 'rejected',
                            'academicAdvisorID' => $lecturer->lecturerID,
                            'status' => 'rejected',
                            'lecturerID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks,
                        ]);

                        // Send notification
                        try {
                            $application->load('student.user');
                            $application->student->user->notify(
                                new CourseVerificationStatusNotification($application)
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send rejection notification: ' . $e->getMessage());
                        }

                        $count++;
                    }
                } elseif ($lecturer->isCoordinator || $lecturer->isCommittee) {
                    // Coordinator bulk rejection
                    if ($application->academicAdvisorStatus === 'approved' && $application->status === 'pending') {
                        $application->update([
                            'status' => 'rejected',
                            'lecturerID' => $lecturer->lecturerID,
                            'remarks' => $this->remarks,
                        ]);

                        // Send notification
                        try {
                            $application->load('student.user');
                            $application->student->user->notify(
                                new CourseVerificationStatusNotification($application)
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send rejection notification: ' . $e->getMessage());
                        }

                        $count++;
                    }
                }
            }

            session()->flash('message', "Successfully rejected {$count} application(s)! Email notifications sent to students.");
            $this->selectedApplications = [];
            $this->selectAll = false;
            $this->remarks = '';
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred during bulk rejection: ' . $e->getMessage());
        }
    }

    public function bulkDownload()
    {
        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to download documents.');
            return;
        }

        try {
            $applications = CourseVerification::with('files')
                ->whereIn('courseVerificationID', $this->selectedApplications)
                ->get();

            if ($applications->isEmpty()) {
                session()->flash('error', 'No applications found.');
                return;
            }

            // Create a temporary zip file
            $zipFileName = 'course_verification_documents_' . date('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($applications as $application) {
                    foreach ($application->files as $file) {
                        $filePath = storage_path('app/public/' . $file->file_path);
                        if (file_exists($filePath)) {
                            // Create a meaningful filename with student ID
                            $fileName = $application->studentID . '_' . $file->original_name;
                            $zip->addFile($filePath, $fileName);
                        }
                    }
                }
                $zip->close();

                // Return download and cleanup
                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            } else {
                session()->flash('error', 'Failed to create zip file.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred during bulk download: ' . $e->getMessage());
            Log::error('Bulk download error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $applications = $this->getFilteredApplications()->paginate($this->perPage);
        $lecturer = Auth::user()->lecturer;

        // Get statistics based on role - each role only sees their own analytics
        $hasCoordinatorAndAcademicAdvisor = $lecturer && $lecturer->isCoordinator && $lecturer->isAcademicAdvisor;
        $hasCommitteeAndAcademicAdvisor = $lecturer && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
        $hasAllThreeRoles = $lecturer && $lecturer->isCoordinator && $lecturer->isCommittee && $lecturer->isAcademicAdvisor;
        
        if ($hasCoordinatorAndAcademicAdvisor || $hasCommitteeAndAcademicAdvisor || $hasAllThreeRoles) {
            // For combined roles, show combined statistics
            $totalApplications = CourseVerification::where(function($q) use ($lecturer) {
                $q->whereHas('student', function ($subQ) use ($lecturer) {
                    $subQ->where('academicAdvisorID', $lecturer->lecturerID);
                })->orWhere('academicAdvisorStatus', 'approved');
            })->count();
            
            $pendingApplications = CourseVerification::where(function($q) use ($lecturer) {
                $q->whereHas('student', function ($subQ) use ($lecturer) {
                    $subQ->where('academicAdvisorID', $lecturer->lecturerID);
                })->whereNull('academicAdvisorStatus');
            })->orWhere(function($q) {
                $q->where('academicAdvisorStatus', 'approved')
                  ->where('status', 'pending');
            })->count();
            
            $approvedApplications = CourseVerification::where(function($q) use ($lecturer) {
                $q->whereHas('student', function ($subQ) use ($lecturer) {
                    $subQ->where('academicAdvisorID', $lecturer->lecturerID);
                })->where('academicAdvisorStatus', 'approved');
            })->orWhere(function($q) {
                $q->where('academicAdvisorStatus', 'approved')
                  ->where('status', 'approved');
            })->count();
            
            $rejectedApplications = CourseVerification::where(function($q) use ($lecturer) {
                $q->whereHas('student', function ($subQ) use ($lecturer) {
                    $subQ->where('academicAdvisorID', $lecturer->lecturerID);
                })->where('academicAdvisorStatus', 'rejected');
            })->orWhere(function($q) {
                $q->where('academicAdvisorStatus', 'approved')
                  ->where('status', 'rejected');
            })->count();
        } elseif ($lecturer && $lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
            // Academic advisor statistics - all their advisees' applications (including history)
            $totalApplications = CourseVerification::whereHas('student', function ($q) use ($lecturer) {
                $q->where('academicAdvisorID', $lecturer->lecturerID);
            })->count();
            $pendingApplications = CourseVerification::whereHas('student', function ($q) use ($lecturer) {
                $q->where('academicAdvisorID', $lecturer->lecturerID);
            })->whereNull('academicAdvisorStatus')->count();
            $approvedApplications = CourseVerification::whereHas('student', function ($q) use ($lecturer) {
                $q->where('academicAdvisorID', $lecturer->lecturerID);
            })->where('academicAdvisorStatus', 'approved')->count();
            $rejectedApplications = CourseVerification::whereHas('student', function ($q) use ($lecturer) {
                $q->where('academicAdvisorID', $lecturer->lecturerID);
            })->where('academicAdvisorStatus', 'rejected')->count();
        } elseif ($lecturer && ($lecturer->isCoordinator || $lecturer->isCommittee)) {
            // Coordinator/Committee statistics - all applications approved by academic advisor (including history)
            $totalApplications = CourseVerification::where('academicAdvisorStatus', 'approved')->count();
            $pendingApplications = CourseVerification::where('academicAdvisorStatus', 'approved')
                ->where('status', 'pending')->count();
            $approvedApplications = CourseVerification::where('academicAdvisorStatus', 'approved')
                ->where('status', 'approved')->count();
            $rejectedApplications = CourseVerification::where('academicAdvisorStatus', 'approved')
                ->where('status', 'rejected')->count();
        } else {
            // Default statistics (should not happen, but fallback)
            $totalApplications = 0;
            $pendingApplications = 0;
            $approvedApplications = 0;
            $rejectedApplications = 0;
        }

        return view('livewire.lecturer.courseVerificationTable', [
            'applications' => $applications,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'approvedApplications' => $approvedApplications,
            'rejectedApplications' => $rejectedApplications,
            'isAcademicAdvisor' => $lecturer && $lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee,
            'isCoordinator' => $lecturer && (($lecturer->isCoordinator || $lecturer->isCommittee) && ($lecturer->isAcademicAdvisor || !$lecturer->isAcademicAdvisor)),
        ]);
    }
}
