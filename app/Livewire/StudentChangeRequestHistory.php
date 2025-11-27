<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestJustification;
use App\Models\PlacementApplication;
use Carbon\Carbon;

class StudentChangeRequestHistory extends Component
{
    use WithPagination;

    // Search and filter properties
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'requestDate';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Modal properties
    public $showDetailModal = false;
    public $selectedRequest = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'requestDate'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
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

        return view('livewire.student-change-request-history', [
            'requests' => $requests,
            'analytics' => $analytics,
        ]);
    }
}
