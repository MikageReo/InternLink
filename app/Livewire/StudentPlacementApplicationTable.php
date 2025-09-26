<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\PlacementApplication;
use App\Models\CourseVerification;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class StudentPlacementApplicationTable extends Component
{
    use WithPagination, WithFileUploads;

    // Search and sort properties
    public $search = '';
    public $sortField = 'applicationDate';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';

    // Form properties
    public $showForm = false;
    public $editingId = null;

    // Application form data
    public $companyName = '';
    public $companyAddress = '';
    public $companyEmail = '';
    public $companyNumber = '';
    public $allowance = '';
    public $position = '';
    public $jobscope = '';
    public $methodOfWork = '';
    public $startDate = '';
    public $endDate = '';

    // File uploads (multiple files)
    public $applicationFiles = [];
    public $existingFiles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'applicationDate'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $rules = [
        'companyName' => 'required|string|max:255',
        'companyAddress' => 'required|string',
        'companyEmail' => 'required|email|max:255',
        'companyNumber' => 'required|string|max:20',
        'allowance' => 'nullable|numeric|min:0',
        'position' => 'required|string|max:255',
        'jobscope' => 'required|string',
        'methodOfWork' => 'required|in:WFO,WOS,WOC,WFH,WFO & WFH',
        'startDate' => 'required|date|after:today',
        'endDate' => 'required|date|after:startDate',
        'applicationFiles.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max per file
    ];

    protected $messages = [
        'companyName.required' => 'Company name is required.',
        'companyAddress.required' => 'Company address is required.',
        'companyEmail.required' => 'Company email is required.',
        'companyEmail.email' => 'Please provide a valid email address.',
        'companyNumber.required' => 'Company contact number is required.',
        'position.required' => 'Position is required.',
        'jobscope.required' => 'Job scope is required.',
        'methodOfWork.required' => 'Method of work is required.',
        'startDate.required' => 'Start date is required.',
        'startDate.after' => 'Start date must be in the future.',
        'endDate.required' => 'End date is required.',
        'endDate.after' => 'End date must be after start date.',
        'applicationFiles.*.file' => 'Each uploaded item must be a file.',
        'applicationFiles.*.mimes' => 'Files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'applicationFiles.*.max' => 'Each file cannot exceed 10MB.',
    ];

    public function mount()
    {
        // Check if student has approved course verification
        if (!$this->canStudentApply()) {
            session()->flash('error', 'You must have an approved course verification to apply for internship placement.');
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
        if (!$this->canStudentApply()) {
            session()->flash('error', 'You must have an approved course verification to apply for internship placement.');
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
        $this->reset([
            'companyName',
            'companyAddress',
            'companyEmail',
            'companyNumber',
            'allowance',
            'position',
            'jobscope',
            'methodOfWork',
            'startDate',
            'endDate',
            'applicationFiles',
            'existingFiles'
        ]);
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $application = PlacementApplication::with('files')->findOrFail($id);

        // Check if this application belongs to the current student
        if ($application->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only edit your own applications.');
            return;
        }

        // Only allow editing if status is pending
        if ($application->overall_status !== 'Pending') {
            session()->flash('error', 'You can only edit pending applications.');
            return;
        }

        $this->editingId = $id;
        $this->companyName = $application->companyName;
        $this->companyAddress = $application->companyAddress;
        $this->companyEmail = $application->companyEmail;
        $this->companyNumber = $application->companyNumber;
        $this->allowance = $application->allowance;
        $this->position = $application->position;
        $this->jobscope = $application->jobscope;
        $this->methodOfWork = $application->methodOfWork;
        $this->startDate = $application->startDate instanceof \Carbon\Carbon ? $application->startDate->format('Y-m-d') : '';
        $this->endDate = $application->endDate instanceof \Carbon\Carbon ? $application->endDate->format('Y-m-d') : '';
        $this->existingFiles = $application->files->toArray();

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

            // Check course verification requirement
            if (!$this->canStudentApply()) {
                session()->flash('error', 'You must have an approved course verification to apply for internship placement.');
                return;
            }

            $applicationData = [
                'companyName' => $this->companyName,
                'companyAddress' => $this->companyAddress,
                'companyEmail' => $this->companyEmail,
                'companyNumber' => $this->companyNumber,
                'allowance' => $this->allowance ?: null,
                'position' => $this->position,
                'jobscope' => $this->jobscope,
                'methodOfWork' => $this->methodOfWork,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'studentID' => $student->studentID,
                'applicationDate' => now()->toDateString(),
            ];

            if ($this->editingId) {
                // Update existing application
                $application = PlacementApplication::findOrFail($this->editingId);

                // Reset statuses when editing
                $applicationData['committeeStatus'] = 'Pending';
                $applicationData['coordinatorStatus'] = 'Pending';
                $applicationData['studentAcceptance'] = null;

                $application->update($applicationData);

                // Handle file updates
                if (!empty($this->applicationFiles)) {
                    // Delete old files
                    $application->files()->delete();

                    // Upload new files
                    $this->uploadFiles($application);
                }

                session()->flash('message', 'Placement application updated successfully!');
            } else {
                // Create new application
                // Get current apply count for this student
                $lastApplication = PlacementApplication::where('studentID', $student->studentID)
                    ->orderBy('applyCount', 'desc')
                    ->first();

                $applicationData['applyCount'] = $lastApplication ? $lastApplication->applyCount + 1 : 1;

                $application = PlacementApplication::create($applicationData);

                // Upload files
                if (!empty($this->applicationFiles)) {
                    $this->uploadFiles($application);
                }

                session()->flash('message', 'Placement application submitted successfully!');
            }

            $this->closeForm();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while processing your application: ' . $e->getMessage());
        }
    }

    private function uploadFiles($application)
    {
        foreach ($this->applicationFiles as $file) {
            $filePath = $file->store('placement-application-files', 'public');

            $application->files()->create([
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }
    }

    public function acceptApplication($id)
    {
        $application = PlacementApplication::findOrFail($id);

        if ($application->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only accept your own applications.');
            return;
        }

        if (!$application->can_accept) {
            session()->flash('error', 'This application cannot be accepted at this time.');
            return;
        }

        // Check if student already has an accepted application
        $existingAccepted = PlacementApplication::where('studentID', Auth::user()->student->studentID)
            ->where('studentAcceptance', 'Accepted')
            ->exists();

        if ($existingAccepted) {
            session()->flash('error', 'You can only accept one internship placement application.');
            return;
        }

        $application->update(['studentAcceptance' => 'Accepted']);
        session()->flash('message', 'Application accepted successfully!');
    }

    public function declineApplication($id)
    {
        $application = PlacementApplication::findOrFail($id);

        if ($application->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only decline your own applications.');
            return;
        }

        if (!$application->can_accept) {
            session()->flash('error', 'This application cannot be declined at this time.');
            return;
        }

        $application->update(['studentAcceptance' => 'Declined']);
        session()->flash('message', 'Application declined.');
    }

    private function canStudentApply(): bool
    {
        $student = Auth::user()->student;

        if (!$student) {
            return false;
        }

        // Check if student has approved course verification
        $approvedVerification = CourseVerification::where('studentID', $student->studentID)
            ->where('status', 'approved')
            ->exists();

        return $approvedVerification;
    }

    private function getFilteredApplications()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return PlacementApplication::query()->where('applicationID', 0); // Return empty query
        }

        $query = PlacementApplication::forStudent($student->studentID)
            ->with(['committee', 'coordinator', 'files']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('companyName', 'like', '%' . $this->search . '%')
                    ->orWhere('position', 'like', '%' . $this->search . '%')
                    ->orWhere('methodOfWork', 'like', '%' . $this->search . '%')
                    ->orWhere('applicationID', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            if ($this->statusFilter === 'Pending') {
                $query->pending();
            } elseif ($this->statusFilter === 'Approved') {
                $query->approved();
            } elseif ($this->statusFilter === 'Rejected') {
                $query->where(function ($q) {
                    $q->where('committeeStatus', 'Rejected')
                        ->orWhere('coordinatorStatus', 'Rejected');
                });
            }
        }

        // Apply sorting
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    public function render()
    {
        $applications = $this->getFilteredApplications()->paginate($this->perPage);
        $canApply = $this->canStudentApply();

        // Check if student has any accepted application
        $hasAcceptedApplication = false;
        if (Auth::user()->student) {
            $hasAcceptedApplication = PlacementApplication::where('studentID', Auth::user()->student->studentID)
                ->where('studentAcceptance', 'Accepted')
                ->exists();
        }

        return view('livewire.student-placement-application-table', [
            'applications' => $applications,
            'canApply' => $canApply,
            'hasAcceptedApplication' => $hasAcceptedApplication,
        ]);
    }
}
