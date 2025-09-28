<?php

namespace App\Livewire;

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

class StudentRequestDeferTable extends Component
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
        'applicationFiles.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
    ];

    protected $messages = [
        'reason.required' => 'Please provide a reason for the defer request.',
        'reason.min' => 'Reason must be at least 10 characters.',
        'startDate.required' => 'Start date is required.',
        'startDate.after' => 'Start date must be after today.',
        'endDate.required' => 'End date is required.',
        'endDate.after' => 'End date must be after start date.',
        'applicationFiles.*.mimes' => 'Files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'applicationFiles.*.max' => 'Each file must be less than 10MB.',
    ];

    public function mount()
    {
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

        $this->showForm = true;
        $this->resetForm();
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

        // Only allow editing if both committee and coordinator status are pending
        if ($request->committeeStatus !== 'Pending' || $request->coordinatorStatus !== 'Pending') {
            session()->flash('error', 'You can only edit requests that are still pending review by both committee and coordinator.');
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

    private function uploadFiles($request)
    {
        foreach ($this->applicationFiles as $file) {
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

    public function render()
    {
        $requests = $this->getFilteredRequests()->paginate($this->perPage);
        $canMakeRequest = $this->canStudentMakeRequest();

        return view('livewire.student-request-defer-table', [
            'requests' => $requests,
            'canMakeRequest' => $canMakeRequest,
        ]);
    }
}
