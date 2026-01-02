<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\RequestJustification;
use App\Models\PlacementApplication;
use App\Models\File;
use Carbon\Carbon;

class ChangeRequestHistory extends Component
{
    use WithPagination, WithFileUploads;

    // Search and filter properties
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'requestDate';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Modal properties
    public $showDetailModal = false;
    public $selectedRequest = null;
    
    // Edit form properties
    public $showEditForm = false;
    public $editingId = null;
    public $changeRequestReason = '';
    public $changeRequestFiles = [];
    public $existingFiles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'requestDate'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    protected function rules()
    {
        $rules = [
            'changeRequestReason' => 'required|string|min:20|max:1000',
        ];

        // If editing and has existing files, new files are optional
        // Otherwise, files are required
        if ($this->editingId && !empty($this->existingFiles)) {
            $rules['changeRequestFiles'] = 'nullable|array';
            $rules['changeRequestFiles.*'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
        } else {
            $rules['changeRequestFiles'] = 'required|array|min:1';
            $rules['changeRequestFiles.*'] = 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
        }

        return $rules;
    }

    protected $messages = [
        'changeRequestReason.required' => 'Please provide a reason for the change request.',
        'changeRequestReason.min' => 'Reason must be at least 20 characters.',
        'changeRequestReason.max' => 'Reason must not exceed 1000 characters.',
        'changeRequestFiles.required' => 'At least one supporting document is required.',
        'changeRequestFiles.min' => 'At least one supporting document is required.',
        'changeRequestFiles.*.required' => 'Each file is required.',
        'changeRequestFiles.*.mimes' => 'Supporting files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'changeRequestFiles.*.max' => 'Each file must be less than 5MB.',
    ];

    public function mount()
    {
        // Clear error messages about accepted placement applications (not relevant for change requests)
        if (session()->has('error')) {
            $errorMessage = session('error');
            if (str_contains($errorMessage, 'already have an accepted placement application') || 
                str_contains($errorMessage, 'cannot submit additional applications')) {
                session()->forget('error');
            }
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

    public function viewRequest($id)
    {
        $this->selectedRequest = RequestJustification::with([
            'placementApplication.student.user',
            'placementApplication.committee',
            'placementApplication.coordinator',
            'committee',
            'coordinator',
            'files'
        ])->findOrFail($id);

        // Verify this request belongs to the current student
        if ($this->selectedRequest->placementApplication->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only view your own change requests.');
            return;
        }

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedRequest = null;
    }

    public function clearFilters()
    {
        $this->reset([
            'search',
            'statusFilter',
            'sortField',
            'sortDirection'
        ]);
        $this->sortField = 'requestDate';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function downloadFile($fileId)
    {
        $file = \App\Models\File::find($fileId);

        if (!$file || !$file->fileable) {
            session()->flash('error', 'File not found.');
            return;
        }

        // Verify the file belongs to a change request owned by this student
        if ($file->fileable_type === 'App\\Models\\RequestJustification') {
            $changeRequest = $file->fileable;
            if ($changeRequest->placementApplication->studentID !== Auth::user()->student->studentID) {
                session()->flash('error', 'Access denied.');
                return;
            }
        } else {
            session()->flash('error', 'Invalid file type.');
            return;
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($file->file_path)) {
            session()->flash('error', 'File not found on server.');
            return;
        }

        return response()->download(
            \Illuminate\Support\Facades\Storage::disk('public')->path($file->file_path),
            $file->original_name
        );
    }

    public function edit($id)
    {
        $request = RequestJustification::with(['files', 'placementApplication'])->findOrFail($id);

        // Check if this request belongs to the current student
        if ($request->placementApplication->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only edit your own change requests.');
            return;
        }

        // Prevent editing if either committee or coordinator has approved/rejected
        if ($request->committeeStatus !== 'Pending' || $request->coordinatorStatus !== 'Pending') {
            session()->flash('error', 'You cannot edit a change request once it has been reviewed by the committee or coordinator.');
            return;
        }

        $this->editingId = $id;
        $this->changeRequestReason = $request->reason;
        
        // Load existing files for display
        $this->existingFiles = $request->files->toArray();

        $this->showEditForm = true;
    }

    public function editFromView($id)
    {
        // Close the detail modal first
        $this->closeDetailModal();
        
        // Then open the edit form
        $this->edit($id);
    }

    public function closeEditForm()
    {
        $this->showEditForm = false;
        $this->resetEditForm();
    }

    private function resetEditForm()
    {
        $this->reset(['editingId', 'changeRequestReason', 'changeRequestFiles', 'existingFiles']);
        $this->resetErrorBag();
    }

    protected function validateChangeRequestFileSizes()
    {
        // If editing and has existing files, new files are optional
        if ($this->editingId && !empty($this->existingFiles) && empty($this->changeRequestFiles)) {
            return; // No validation needed if keeping existing files
        }

        if (empty($this->changeRequestFiles)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'changeRequestFiles' => ['At least one supporting document is required.']
            ]);
        }

        $errors = [];
        $maxSizeBytes = 5120 * 1024; // 5MB in bytes

        foreach ($this->changeRequestFiles as $index => $file) {
            if (!$file) {
                continue; // Skip null files
            }

            $fileSizeBytes = $file->getSize();
            $fileSizeMB = round($fileSizeBytes / (1024 * 1024), 2);

            if ($fileSizeBytes > $maxSizeBytes) {
                $errors["changeRequestFiles.{$index}"] = "File '{$file->getClientOriginalName()}' ({$fileSizeMB}MB) exceeds the maximum allowed size of 5MB.";
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    public function updateChangeRequest()
    {
        // Validate file sizes first before any other validation
        try {
            $this->validateChangeRequestFileSizes();
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        $this->validate($this->rules());

        try {
            $request = RequestJustification::findOrFail($this->editingId);

            // Double-check that request can still be edited
            if ($request->committeeStatus !== 'Pending' || $request->coordinatorStatus !== 'Pending') {
                session()->flash('error', 'You cannot edit a change request once it has been reviewed by the committee or coordinator.');
                $this->closeEditForm();
                return;
            }

            // Update the change request
            $request->update([
                'reason' => $this->changeRequestReason,
            ]);

            // Delete old files and upload new ones if new files are provided
            if (!empty($this->changeRequestFiles)) {
                // Delete old files
                foreach ($request->files as $file) {
                    if (Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }
                    $file->delete();
                }
                
                // Upload new files
                $this->uploadChangeRequestFiles($request);
            }
            // If no new files uploaded, keep existing files

            session()->flash('message', 'Change request updated successfully!');
            $this->closeEditForm();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    protected function uploadChangeRequestFiles($changeRequest)
    {
        $maxSizeBytes = 5120 * 1024; // 5MB in bytes
        foreach ($this->changeRequestFiles as $file) {
            // Double-check file size before uploading
            $fileSizeKB = $file->getSize() / 1024;
            if ($fileSizeKB > 5120) {
                throw new \Exception("File '{$file->getClientOriginalName()}' exceeds the maximum allowed size of 5MB.");
            }

            $path = $file->store('change-requests', 'public');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();

            File::create([
                'fileable_type' => RequestJustification::class,
                'fileable_id' => $changeRequest->justificationID,
                'file_path' => $path,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ]);
        }
    }

    private function getFilteredRequests()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return RequestJustification::whereRaw('1 = 0'); // Return empty query
        }

        $query = RequestJustification::with([
            'placementApplication.student.user',
            'committee',
            'coordinator',
            'files'
        ])
        ->whereHas('placementApplication', function($q) use ($student) {
            $q->where('studentID', $student->studentID);
        });

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('reason', 'like', '%' . $this->search . '%')
                  ->orWhereHas('placementApplication', function($subQ) {
                      $subQ->where('companyName', 'like', '%' . $this->search . '%')
                           ->orWhere('position', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'Pending') {
                $query->where(function($q) {
                    $q->where('committeeStatus', 'Pending')
                      ->orWhere('coordinatorStatus', 'Pending');
                });
            } elseif (in_array($this->statusFilter, ['Approved', 'Rejected'])) {
                if ($this->statusFilter === 'Approved') {
                    $query->where('committeeStatus', 'Approved')
                          ->where('coordinatorStatus', 'Approved');
                } else {
                    $query->where(function($q) {
                        $q->where('committeeStatus', 'Rejected')
                          ->orWhere('coordinatorStatus', 'Rejected');
                    });
                }
            }
        }

        // Apply sorting
        if ($this->sortField === 'companyName') {
            $query->join('placement_applications', 'request_justifications.applicationID', '=', 'placement_applications.applicationID')
                  ->orderBy('placement_applications.companyName', $this->sortDirection)
                  ->select('request_justifications.*');
        } elseif ($this->sortField === 'position') {
            $query->join('placement_applications', 'request_justifications.applicationID', '=', 'placement_applications.applicationID')
                  ->orderBy('placement_applications.position', $this->sortDirection)
                  ->select('request_justifications.*');
        } elseif ($this->sortField === 'overallStatus') {
            // Custom sorting for overall status
            $query->orderByRaw("
                CASE
                    WHEN committeeStatus = 'Rejected' OR coordinatorStatus = 'Rejected' THEN 'Rejected'
                    WHEN committeeStatus = 'Approved' AND coordinatorStatus = 'Approved' THEN 'Approved'
                    ELSE 'Pending'
                END " . $this->sortDirection
            );
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }


    private function getAnalyticsData()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ];
        }

        $allRequests = RequestJustification::whereHas('placementApplication', function($q) use ($student) {
            $q->where('studentID', $student->studentID);
        })->get();

        $total = $allRequests->count();
        $pending = $allRequests->filter(function($request) {
            return $request->committeeStatus === 'Pending' || $request->coordinatorStatus === 'Pending';
        })->count();
        $approved = $allRequests->where('committeeStatus', 'Approved')
                               ->where('coordinatorStatus', 'Approved')->count();
        $rejected = $allRequests->filter(function($request) {
            return $request->committeeStatus === 'Rejected' || $request->coordinatorStatus === 'Rejected';
        })->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ];
    }

    public function render()
    {
        $requests = $this->getFilteredRequests()->paginate($this->perPage);
        $analytics = $this->getAnalyticsData();

        return view('livewire.student.changeRequestHistory', [
            'requests' => $requests,
            'analytics' => $analytics,
        ]);
    }
}
