<?php

namespace App\Livewire;

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

class LecturerCourseVerificationTable extends Component
{
    use WithPagination;

    // Search and sort properties
    public $search = '';
    public $sortField = 'applicationDate';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $statusFilter = '';

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
        $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'files'])
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

            // Update the application
            $updated = $application->update([
                'status' => 'approved',
                'lecturerID' => $lecturer->lecturerID,
                'remarks' => $this->remarks,
            ]);

            if ($updated) {
                // Refresh the selected application to show updated status
                $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'files'])
                    ->findOrFail($id);

                // Send email notification to student
                try {
                    $this->selectedApplication->student->user->notify(
                        new CourseVerificationStatusNotification($this->selectedApplication)
                    );
                } catch (\Exception $e) {
                    // Log email error but don't fail the approval process
                    Log::error('Failed to send approval notification: ' . $e->getMessage());
                }

                session()->flash('message', 'Application approved successfully! Email notification sent to student.');

                // Reset pagination to refresh the table
                $this->resetPage();

                // Dispatch event to refresh student views if they're open
                $this->dispatch('application-status-updated');
            } else {
                session()->flash('error', 'Failed to update application status.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while approving the application: ' . $e->getMessage());
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

            // Update the application
            $updated = $application->update([
                'status' => 'rejected',
                'lecturerID' => $lecturer->lecturerID,
                'remarks' => $this->remarks,
            ]);

            if ($updated) {
                // Refresh the selected application to show updated status
                $this->selectedApplication = CourseVerification::with(['student.user', 'lecturer', 'files'])
                    ->findOrFail($id);

                // Send email notification to student
                try {
                    $this->selectedApplication->student->user->notify(
                        new CourseVerificationStatusNotification($this->selectedApplication)
                    );
                } catch (\Exception $e) {
                    // Log email error but don't fail the rejection process
                    Log::error('Failed to send rejection notification: ' . $e->getMessage());
                }

                session()->flash('message', 'Application rejected successfully! Email notification sent to student.');

                // Reset pagination to refresh the table
                $this->resetPage();

                // Dispatch event to refresh student views if they're open
                $this->dispatch('application-status-updated');
            } else {
                session()->flash('error', 'Failed to update application status.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while rejecting the application: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'sortField', 'sortDirection']);
        $this->sortField = 'applicationDate';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    private function getFilteredApplications()
    {
        $query = CourseVerification::with(['student.user', 'lecturer', 'files']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('currentCredit', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
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
            $query->where('status', $this->statusFilter);
        }

        // Apply custom sorting - prioritize pending status and oldest applications
        if ($this->sortField === 'applicationDate') {
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
        } else {
            // Apply regular sorting
            if (in_array($this->sortField, ['currentCredit', 'status', 'created_at', 'courseVerificationID'])) {
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

        return response()->download(Storage::disk('public')->path($file->file_path), $file->original_name);
    }

    // Bulk selection methods
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select all PENDING applications on current page only
            $this->selectedApplications = $this->getFilteredApplications()
                ->where('status', 'pending')
                ->pluck('courseVerificationID')
                ->toArray();
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
                $application = CourseVerification::find($id);
                if ($application && $application->status === 'pending') {
                    $application->update([
                        'status' => 'approved',
                        'lecturerID' => $lecturer->lecturerID,
                        'remarks' => $this->remarks ?: 'Bulk approved',
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
                $application = CourseVerification::find($id);
                if ($application && $application->status === 'pending') {
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

        // Get statistics
        $totalApplications = CourseVerification::count();
        $pendingApplications = CourseVerification::where('status', 'pending')->count();
        $approvedApplications = CourseVerification::where('status', 'approved')->count();
        $rejectedApplications = CourseVerification::where('status', 'rejected')->count();

        return view('livewire.lecturer-course-verification-table', [
            'applications' => $applications,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'approvedApplications' => $approvedApplications,
            'rejectedApplications' => $rejectedApplications,
        ]);
    }
}
