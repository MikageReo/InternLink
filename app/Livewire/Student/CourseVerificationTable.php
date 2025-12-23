<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CourseVerification;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
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

    // Guide visibility
    public $showGuide = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'applicationDate'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
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
        // Clear error messages about accepted placement applications (not relevant for course verification)
        if (session()->has('error')) {
            $errorMessage = session('error');
            if (
                str_contains($errorMessage, 'already have an accepted placement application') ||
                str_contains($errorMessage, 'cannot submit additional applications')
            ) {
                session()->forget('error');
            }
        }

        // You can set the total credit required from a config or database
        $this->totalCreditRequired = 130;
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

        // Only allow editing if status is pending
        if ($verification->status !== 'pending') {
            session()->flash('error', 'You can only edit pending applications.');
            return;
        }

        $this->editingId = $id;
        $this->currentCredit = $verification->currentCredit;
        $this->showForm = true;
    }

    public function submit()
    {
        $this->validate();

        try {
            $student = Auth::user()->student;

            if (!$student) {
                session()->flash('error', 'Student profile not found.');
                return;
            }

            // Get a lecturer for assignment (you might want to implement a better assignment logic)
            $lecturer = Lecturer::where('isAcademicAdvisor', true)->first();
            if (!$lecturer) {
                $lecturer = Lecturer::first(); // Fallback to any lecturer
            }

            if (!$lecturer) {
                session()->flash('error', 'No lecturer available for assignment.');
                return;
            }

            // Store the uploaded file
            $filePath = $this->submittedFile->store('course-verification-files', 'public');

            if ($this->editingId) {
                // Update existing verification
                $verification = CourseVerification::findOrFail($this->editingId);

                // Delete old file(s) if new file is uploaded
                $verification->files()->delete(); // This will also delete the actual files via the File model's boot method

                $verification->update([
                    'currentCredit' => $this->currentCredit,
                    'status' => 'pending', // Reset status to pending when edited
                    'applicationDate' => now()->toDateString(),
                ]);

                // Create new file record
                $verification->files()->create([
                    'file_path' => $filePath,
                    'original_name' => $this->submittedFile->getClientOriginalName(),
                    'file_size' => $this->submittedFile->getSize(),
                    'mime_type' => $this->submittedFile->getMimeType(),
                ]);

                session()->flash('message', 'Course verification application updated successfully!');
            } else {
                // Create new verification
                $verification = CourseVerification::create([
                    'currentCredit' => $this->currentCredit,
                    'status' => 'pending',
                    'applicationDate' => now()->toDateString(),
                    'lecturerID' => $lecturer->lecturerID,
                    'studentID' => $student->studentID,
                ]);

                // Create file record
                $verification->files()->create([
                    'file_path' => $filePath,
                    'original_name' => $this->submittedFile->getClientOriginalName(),
                    'file_size' => $this->submittedFile->getSize(),
                    'mime_type' => $this->submittedFile->getMimeType(),
                ]);

                session()->flash('message', 'Course verification application submitted successfully!');
            }

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

        // Only allow deletion if status is pending
        if ($verification->status !== 'pending') {
            session()->flash('error', 'You can only delete pending applications.');
            return;
        }

        try {
            // Delete the files (this will also delete actual files via the File model's boot method)
            $verification->files()->delete();

            // Delete the record
            $verification->delete();

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
            ->with(['lecturer', 'student', 'files']);

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

        // Apply sorting
        if ($this->sortField) {
            if (in_array($this->sortField, ['currentCredit', 'status', 'applicationDate', 'created_at'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            }
        }

        return $query;
    }

    public function render()
    {
        $verifications = $this->getFilteredVerifications()->paginate($this->perPage);

        // Get current application (latest application for this student)
        $student = Auth::user()->student;
        $currentApplication = null;
        $canApply = false;

        if ($student) {
            $currentApplication = CourseVerification::where('studentID', $student->studentID)
                ->with(['lecturer', 'academicAdvisor', 'files'])
                ->latest('applicationDate')
                ->latest('courseVerificationID')
                ->first();

            // Check if student has any approved application
            $hasApprovedApplication = CourseVerification::where('studentID', $student->studentID)
                ->where('status', 'approved')
                ->exists();

            // Student can apply if they have no approved application AND (no application at all OR latest is rejected)
            $canApply = !$hasApprovedApplication && (!$currentApplication || $currentApplication->status === 'rejected');
        }

        return view('livewire.course-verification-table', [
            'verifications' => $verifications,
            'totalCreditRequired' => $this->totalCreditRequired,
            'currentApplication' => $currentApplication,
            'canApply' => $canApply,
            'hasApprovedApplication' => $hasApprovedApplication ?? false,
        ]);
    }
}
