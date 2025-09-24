<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CourseVerification;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class CourseVerificationTable extends Component
{
    use WithPagination, WithFileUploads;

    // Search and sort properties
    public $search = '';
    public $sortField = 'applicationDate';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Form properties
    public $showForm = false;
    public $currentCredit = '';
    public $submittedFile;
    public $editingId = null;

    // Student properties
    public $totalCreditRequired = 130; // Default total credit required
    public $currentApplication = null; // Current student application
    public $canApply = true; // Whether student can apply

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'applicationDate'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'application-status-updated' => 'refreshStatus',
    ];

    protected $rules = [
        'currentCredit' => 'required|integer|min:0|max:130',
        'submittedFile' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
    ];

    protected $messages = [
        'currentCredit.required' => 'Current credit is required.',
        'currentCredit.integer' => 'Current credit must be a number.',
        'currentCredit.min' => 'Current credit cannot be negative.',
        'currentCredit.max' => 'Current credit cannot exceed 130.',
        'submittedFile.required' => 'Course file is required.',
        'submittedFile.file' => 'Please upload a valid file.',
        'submittedFile.mimes' => 'File must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'submittedFile.max' => 'File size cannot exceed 10MB.',
    ];

    public function mount()
    {
        // You can set the total credit required from a config or database
        $this->totalCreditRequired = 130;

        // Check if student has existing application and determine if they can apply
        $this->checkApplicationStatus();
    }

    public function checkApplicationStatus()
    {
        $student = Auth::user()->student;

        if (!$student) {
            $this->canApply = false;
            return;
        }

        // Get the latest application for this student
        $this->currentApplication = CourseVerification::where('studentID', $student->studentID)
            ->orderBy('applicationDate', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$this->currentApplication) {
            // No application exists, can apply
            $this->canApply = true;
        } else {
            // Check the status of the latest application
            switch ($this->currentApplication->status) {
                case 'pending':
                    // Cannot apply when pending
                    $this->canApply = false;
                    break;
                case 'approved':
                    // Cannot apply when approved (already verified)
                    $this->canApply = false;
                    break;
                case 'rejected':
                    // Can apply again when rejected
                    $this->canApply = true;
                    break;
                default:
                    $this->canApply = true;
            }
        }
    }

    public function updatingSearch()
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

    public function openForm()
    {
        // Re-check application status before opening form
        $this->checkApplicationStatus();

        if (!$this->canApply) {
            if ($this->currentApplication) {
                if ($this->currentApplication->status === 'pending') {
                    session()->flash('error', 'You already have a pending application. Please wait for the result before applying again.');
                } elseif ($this->currentApplication->status === 'approved') {
                    session()->flash('error', 'Your course verification has already been approved. No further action needed.');
                }
            } else {
                session()->flash('error', 'You are not eligible to apply at this time.');
            }
            return;
        }

        $this->showForm = true;
        $this->resetForm();
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->currentCredit = '';
        $this->submittedFile = null;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $verification = CourseVerification::findOrFail($id);

        // Check if this verification belongs to the current student
        if ($verification->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only edit your own applications.');
            return;
        }

        // Re-check application status
        $this->checkApplicationStatus();

        // Only allow editing if it's the current application and status allows it
        if (!$this->currentApplication || $this->currentApplication->courseVerificationID !== $id) {
            session()->flash('error', 'You can only edit your latest application.');
            return;
        }

        // Only allow editing if status is pending or rejected
        if (!in_array($verification->status, ['pending', 'rejected'])) {
            session()->flash('error', 'You can only edit pending or rejected applications.');
            return;
        }

        // If status is approved, cannot edit
        if ($verification->status === 'approved') {
            session()->flash('error', 'You cannot edit an approved application.');
            return;
        }

        $this->editingId = $id;
        $this->currentCredit = $verification->currentCredit;
        $this->showForm = true;
    }

    public function submit()
    {
        // Re-check application status before submitting
        $this->checkApplicationStatus();

        // If editing, allow submission. If new application, check if can apply
        if (!$this->editingId && !$this->canApply) {
            session()->flash('error', 'You cannot submit a new application at this time.');
            return;
        }

        $this->validate();

        try {
            $student = Auth::user()->student;

            if (!$student) {
                session()->flash('error', 'Student profile not found.');
                return;
            }

            // Store the uploaded file
            $filePath = $this->submittedFile->store('course-verification-files', 'public');

            if ($this->editingId) {
                // Update existing verification
                $verification = CourseVerification::findOrFail($this->editingId);

                // Ensure this is the current application
                if (!$this->currentApplication || $this->currentApplication->courseVerificationID !== $this->editingId) {
                    session()->flash('error', 'You can only edit your latest application.');
                    return;
                }

                // Delete old file if new file is uploaded
                if ($verification->submittedFile && $verification->submittedFile !== $filePath) {
                    Storage::disk('public')->delete($verification->submittedFile);
                }

                $verification->update([
                    'currentCredit' => $this->currentCredit,
                    'submittedFile' => $filePath,
                    'status' => 'pending', // Reset status to pending when edited
                    'applicationDate' => now()->toDateString(),
                    'lecturerID' => null, // Reset lecturer ID when resubmitted
                ]);

                session()->flash('message', 'Course verification application updated successfully!');
            } else {
                // Check if student already has applications and get the latest one
                $latestApplication = CourseVerification::where('studentID', $student->studentID)
                    ->orderBy('applicationDate', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestApplication) {
                    // If latest application is not rejected, cannot create new one
                    if ($latestApplication->status !== 'rejected') {
                        session()->flash('error', 'You already have an application. You can only apply again after rejection.');
                        return;
                    }
                }

                // Create new verification
                CourseVerification::create([
                    'currentCredit' => $this->currentCredit,
                    'submittedFile' => $filePath,
                    'status' => 'pending',
                    'applicationDate' => now()->toDateString(),
                    'lecturerID' => null,
                    'studentID' => $student->studentID,
                ]);

                session()->flash('message', 'Course verification application submitted successfully!');
            }

            // Refresh application status after submit
            $this->checkApplicationStatus();
            $this->closeForm();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while processing your application: ' . $e->getMessage());
        }
    }

    public function deleteApplication($id)
    {
        $verification = CourseVerification::findOrFail($id);

        // Check if this verification belongs to the current student
        if ($verification->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only delete your own applications.');
            return;
        }

        // Re-check application status
        $this->checkApplicationStatus();

        // Only allow deletion if it's the current application and status allows it
        if (!$this->currentApplication || $this->currentApplication->courseVerificationID !== $id) {
            session()->flash('error', 'You can only delete your latest application.');
            return;
        }

        // Only allow deletion if status is pending or rejected
        if (!in_array($verification->status, ['pending', 'rejected'])) {
            session()->flash('error', 'You can only delete pending or rejected applications.');
            return;
        }

        try {
            // Delete the file
            if ($verification->submittedFile) {
                Storage::disk('public')->delete($verification->submittedFile);
            }

            // Delete the record
            $verification->delete();

            // Refresh application status after deletion
            $this->checkApplicationStatus();

            session()->flash('message', 'Application deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the application.');
        }
    }

    private function getFilteredVerifications()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return CourseVerification::query()->where('id', 0); // Return empty query
        }

        $query = CourseVerification::where('studentID', $student->studentID)
            ->with(['lecturer', 'student']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('currentCredit', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
                    ->orWhere('applicationDate', 'like', '%' . $this->search . '%')
                    ->orWhereHas('lecturer', function ($subQ) {
                        $subQ->where('lecturerID', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply sorting with priority for approved applications
        if ($this->sortField) {
            if ($this->sortField === 'status') {
                // Custom status ordering: approved first, then pending, then rejected
                $query->orderByRaw(
                    "
                    CASE
                        WHEN status = 'approved' THEN 1
                        WHEN status = 'pending' THEN 2
                        WHEN status = 'rejected' THEN 3
                        ELSE 4
                    END " . $this->sortDirection
                );
            } elseif ($this->sortField === 'applicationDate') {
                // For application date, also prioritize approved, then sort by date
                $query->orderByRaw(
                    "
                    CASE
                        WHEN status = 'approved' THEN 1
                        WHEN status = 'pending' THEN 2
                        WHEN status = 'rejected' THEN 3
                        ELSE 4
                    END ASC,
                    applicationDate " . $this->sortDirection
                );
            } elseif (in_array($this->sortField, ['currentCredit', 'created_at'])) {
                // For other fields, still prioritize approved but sort by the field
                $query->orderByRaw(
                    "
                    CASE
                        WHEN status = 'approved' THEN 1
                        WHEN status = 'pending' THEN 2
                        WHEN status = 'rejected' THEN 3
                        ELSE 4
                    END ASC,
                    " . $this->sortField . " " . $this->sortDirection
                );
            }
        } else {
            // Default sorting: approved first, then by application date descending
            $query->orderByRaw("
                CASE
                    WHEN status = 'approved' THEN 1
                    WHEN status = 'pending' THEN 2
                    WHEN status = 'rejected' THEN 3
                    ELSE 4
                END ASC,
                applicationDate DESC,
                created_at DESC
            ");
        }

        return $query;
    }

    public function render()
    {
        // Always refresh application status on each render to ensure latest data
        $this->checkApplicationStatus();

        $verifications = $this->getFilteredVerifications()->paginate($this->perPage);

        return view('livewire.course-verification-table', [
            'verifications' => $verifications,
            'totalCreditRequired' => $this->totalCreditRequired,
            'currentApplication' => $this->currentApplication,
            'canApply' => $this->canApply,
        ]);
    }

    // Add method to refresh status when needed
    public function refreshStatus()
    {
        $this->checkApplicationStatus();
    }
}
