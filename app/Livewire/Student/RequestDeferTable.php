<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\RequestDefer;
use App\Models\CourseVerification;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RequestDeferTable extends Component
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
    public $showViewModal = false;
    public $viewingRequest = null;
    public $showWarningModal = false;

    // Defer request form data
    public $reason = '';
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
        'reason' => 'required|string|min:10',
        'startDate' => 'required|date|after:today',
        'endDate' => 'required|date|after:startDate',
        'applicationFiles' => 'required|array|min:1',
        'applicationFiles.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
    ];

    protected $messages = [
        'reason.required' => 'Please provide a reason for the defer request.',
        'reason.min' => 'Reason must be at least 10 characters.',
        'startDate.required' => 'Start date is required.',
        'startDate.after' => 'Start date must be after today.',
        'endDate.required' => 'End date is required.',
        'endDate.after' => 'End date must be after start date.',
        'applicationFiles.required' => 'At least one supporting document is required.',
        'applicationFiles.min' => 'At least one supporting document is required.',
        'applicationFiles.*.required' => 'Each file is required.',
        'applicationFiles.*.mimes' => 'Files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'applicationFiles.*.max' => 'Each file must be less than 5MB.',
    ];

    public function mount()
    {
        // Clear error messages about accepted placement applications (not relevant for defer requests)
        if (session()->has('error')) {
            $errorMessage = session('error');
            if (str_contains($errorMessage, 'already have an accepted placement application') || 
                str_contains($errorMessage, 'cannot submit additional applications')) {
                session()->forget('error');
            }
        }
        
        // Check if student can make defer requests (must have approved course verification)
        if (!$this->canStudentMakeRequest()) {
            session()->flash('warning', 'You must have an approved course verification before making defer requests.');
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
        if (!$this->canStudentMakeRequest()) {
            session()->flash('error', 'You cannot make defer requests without approved course verification.');
            return;
        }

        // Show warning modal first
        $this->showWarningModal = true;
    }

    public function openFormDirectly()
    {
        if (!$this->canStudentMakeRequest()) {
            session()->flash('error', 'You cannot make defer requests without approved course verification.');
            return;
        }

        // Open form directly without warning modal
        $this->showForm = true;
        $this->resetForm();
    }

    public function proceedWithRequest()
    {
        // Close warning modal and open form
        $this->showWarningModal = false;
        $this->showForm = true;
        $this->resetForm();
    }

    public function cancelRequest()
    {
        // Close warning modal
        $this->showWarningModal = false;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'reason',
            'startDate',
            'endDate',
            'applicationFiles',
            'existingFiles'
        ]);
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function view($id)
    {
        $request = RequestDefer::with(['files', 'committee', 'coordinator'])->findOrFail($id);

        // Check if this request belongs to the current student
        if ($request->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only view your own requests.');
            return;
        }

        $this->viewingRequest = $request;
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingRequest = null;
    }

    public function edit($id)
    {
        $request = RequestDefer::with('files')->findOrFail($id);

        // Check if this request belongs to the current student
        if ($request->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only edit your own requests.');
            return;
        }

        // Prevent editing if either committee or coordinator has approved/rejected
        if ($request->committeeStatus !== 'Pending' || $request->coordinatorStatus !== 'Pending') {
            session()->flash('error', 'You cannot edit a request once it has been reviewed by the committee or coordinator.');
            return;
        }

        $this->editingId = $id;
        $this->reason = $request->reason;
        $this->startDate = $request->startDate instanceof \Carbon\Carbon ? $request->startDate->format('Y-m-d') : '';
        $this->endDate = $request->endDate instanceof \Carbon\Carbon ? $request->endDate->format('Y-m-d') : '';

        // Load existing files for display
        $this->existingFiles = $request->files->toArray();

        $this->showForm = true;
    }

    public function submit()
    {
        // Validate file sizes first before any other validation
        try {
            $this->validateFileSizes();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw immediately to prevent any further processing
            throw $e;
        }

        $this->validate();

        try {
            $student = Auth::user()->student;

            if (!$student) {
                session()->flash('error', 'Student profile not found.');
                return;
            }

            $data = [
                'reason' => $this->reason,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'applicationDate' => now()->format('Y-m-d'),
                'studentID' => $student->studentID,
            ];

            if ($this->editingId) {
                // Update existing request
                $request = RequestDefer::findOrFail($this->editingId);
                $request->update($data);

                // Delete old files if new ones are uploaded
                if (!empty($this->applicationFiles)) {
                    foreach ($request->files as $file) {
                        if (Storage::disk('public')->exists($file->file_path)) {
                            Storage::disk('public')->delete($file->file_path);
                        }
                        $file->delete();
                    }
                }

                session()->flash('message', 'Defer request updated successfully!');
            } else {
                // Create new request
                $request = RequestDefer::create($data);
                session()->flash('message', 'Defer request submitted successfully!');
            }

            // Handle file uploads
            if (!empty($this->applicationFiles)) {
                $this->uploadFiles($request);
            }

            $this->closeForm();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    protected function validateFileSizes()
    {
        if (empty($this->applicationFiles)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'applicationFiles' => ['At least one supporting document is required.']
            ]);
        }

        $maxSizeKB = 5120; // 5MB in kilobytes
        $maxSizeBytes = $maxSizeKB * 1024; // Convert to bytes
        $errors = [];

        // Check each file individually
        foreach ($this->applicationFiles as $index => $file) {
            if (!$file) {
                continue; // Skip null files
            }

            $fileSizeBytes = $file->getSize();
            $fileSizeKB = $fileSizeBytes / 1024; // Convert bytes to KB
            $fileSizeMB = round($fileSizeKB / 1024, 2);
            $fileName = $file->getClientOriginalName();

            if ($fileSizeBytes > $maxSizeBytes) {
                $errors[] = "File '{$fileName}' is {$fileSizeMB}MB, which exceeds the maximum allowed size of 5MB.";
            }
        }

        if (!empty($errors)) {
            // Throw validation exception to prevent form submission
            throw \Illuminate\Validation\ValidationException::withMessages([
                'applicationFiles' => $errors
            ]);
        }
    }

    private function uploadFiles($request)
    {
        foreach ($this->applicationFiles as $file) {
            // Double-check file size before uploading
            $fileSizeKB = $file->getSize() / 1024;
            if ($fileSizeKB > 5120) {
                throw new \Exception("File '{$file->getClientOriginalName()}' exceeds the maximum allowed size of 5MB.");
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('defer_requests', $filename, 'public');

            File::create([
                'fileable_id' => $request->deferID,
                'fileable_type' => RequestDefer::class,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }
    }

    private function canStudentMakeRequest(): bool
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

    private function getFilteredRequests()
    {
        $student = Auth::user()->student;
        if (!$student) {
            return RequestDefer::query()->where('id', 0); // Return empty query
        }

        $query = RequestDefer::with(['committee', 'coordinator', 'files'])
            ->forStudent($student->studentID);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reason', 'like', '%' . $this->search . '%')
                    ->orWhere('deferID', 'like', '%' . $this->search . '%');
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
        if (in_array($this->sortField, ['deferID', 'applicationDate', 'startDate', 'endDate'])) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    public function getAnalyticsData()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return null;
        }

        $studentID = $student->studentID;

        $analytics = [
            'total_requests' => RequestDefer::where('studentID', $studentID)->count(),
            'pending_requests' => RequestDefer::where('studentID', $studentID)
                ->where(function ($q) {
                    $q->where('committeeStatus', 'Pending')
                        ->orWhere('coordinatorStatus', 'Pending');
                })->count(),
            'approved_requests' => RequestDefer::where('studentID', $studentID)
                ->where('committeeStatus', 'Approved')
                ->where('coordinatorStatus', 'Approved')
                ->count(),
            'rejected_requests' => RequestDefer::where('studentID', $studentID)
                ->where(function ($q) {
                    $q->where('committeeStatus', 'Rejected')
                        ->orWhere('coordinatorStatus', 'Rejected');
                })->count(),
            'requests_this_month' => RequestDefer::where('studentID', $studentID)
                ->whereMonth('applicationDate', now()->month)
                ->whereYear('applicationDate', now()->year)
                ->count(),
        ];

        return $analytics;
    }

    public function render()
    {
        $requests = $this->getFilteredRequests()->paginate($this->perPage);
        $canMakeRequest = $this->canStudentMakeRequest();
        $analytics = $this->getAnalyticsData();

        return view('livewire.student.requestDeferTable', [
            'requests' => $requests,
            'canMakeRequest' => $canMakeRequest,
            'analytics' => $analytics,
        ]);
    }
}
