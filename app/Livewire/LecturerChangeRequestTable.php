<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RequestJustification;
use App\Models\PlacementApplication;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\ChangeRequestStatusNotification;

class LecturerChangeRequestTable extends Component
{
    use WithPagination;

    // Search and filter properties
    public $search = '';
    public $sortField = 'requestDate';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';
    public $studentFilter = '';
    public $companyFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $roleFilter = ''; // coordinator or committee

    // Modal properties
    public $showDetailModal = false;
    public $selectedRequest = null;
    public $remarks = '';

    // Analytics properties
    public $showAnalytics = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'requestDate'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'studentFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        // Check if user is coordinator or committee member
        $user = Auth::user();
        if (!$user->lecturer) {
            abort(403, 'Access denied. Lecturer profile required.');
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

    public function updatingStudentFilter()
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
            'committee',
            'coordinator',
            'files'
        ])->findOrFail($id);

        // Load existing remarks
        $this->remarks = $this->selectedRequest->remarks ?? '';

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedRequest = null;
        $this->remarks = '';
    }

    public function approveAsCommittee($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can approve requests as committee.');
            return;
        }

        $this->processApproval($id, 'committee', 'Approved');
    }

    public function rejectAsCommittee($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can reject requests as committee.');
            return;
        }

        $this->processApproval($id, 'committee', 'Rejected');
    }

    public function approveAsCoordinator($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can approve requests as coordinator.');
            return;
        }

        $this->processApproval($id, 'coordinator', 'Approved');
    }

    public function rejectAsCoordinator($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can reject requests as coordinator.');
            return;
        }

        $this->processApproval($id, 'coordinator', 'Rejected');
    }

    private function processApproval($id, $role, $status)
    {
        try {
            $request = RequestJustification::findOrFail($id);
            $lecturer = Auth::user()->lecturer;

            if (!$lecturer) {
                session()->flash('error', 'Lecturer profile not found.');
                return;
            }

            // Update request based on role
            $updateData = [
                'remarks' => $this->remarks,
            ];

            if ($role === 'committee') {
                $updateData['committeeID'] = $lecturer->lecturerID;
                $updateData['committeeStatus'] = $status;

                // Special case: If committee rejects, also set coordinator to rejected
                if ($status === 'Rejected') {
                    $updateData['coordinatorStatus'] = 'Rejected';
                }
            } else {
                $updateData['coordinatorID'] = $lecturer->lecturerID;
                $updateData['coordinatorStatus'] = $status;
            }

            // Set decision date when both approvals are complete
            if (($role === 'committee' && $status === 'Rejected') ||
                ($role === 'coordinator' && ($status === 'Approved' || $status === 'Rejected'))) {
                $updateData['decisionDate'] = now()->format('Y-m-d');
            }

            $request->update($updateData);

            // Refresh the selected request
            if ($this->selectedRequest) {
                $this->selectedRequest = RequestJustification::with([
                    'placementApplication.student.user',
                    'committee',
                    'coordinator',
                    'files'
                ])->findOrFail($id);
            }

            // Send email notification
            $this->sendStatusNotification($request);

            // Generate appropriate success message
            if ($status === 'Approved') {
                $message = $role === 'committee' ? 'Change request approved by committee successfully!' : 'Change request approved by coordinator successfully! Student can now submit a new placement application.';
            } else {
                if ($role === 'committee') {
                    $message = 'Change request rejected by committee. Coordinator status also set to rejected.';
                } else {
                    $message = 'Change request rejected by coordinator.';
                }
            }
            session()->flash('message', $message);

            // Reset pagination and dispatch update event
            $this->resetPage();
            $this->dispatch('change-request-status-updated');

        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function sendStatusNotification($request)
    {
        try {
            $studentEmail = $request->placementApplication->student->user->email;
            Mail::to($studentEmail)->send(new ChangeRequestStatusNotification($request));

            Log::info('Change request status notification sent', [
                'request_id' => $request->justificationID,
                'student_email' => $studentEmail,
                'status' => $request->overall_status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send change request status notification', [
                'request_id' => $request->justificationID,
                'error' => $e->getMessage()
            ]);
            // Don't throw the error to avoid disrupting the approval process
        }
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

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'studentFilter', 'companyFilter', 'dateFrom', 'dateTo', 'roleFilter']);
        $this->resetPage();
    }

    public function toggleAnalytics()
    {
        $this->showAnalytics = !$this->showAnalytics;
    }

    private function getFilteredRequests()
    {
        $query = RequestJustification::with([
            'placementApplication.student.user',
            'committee',
            'coordinator',
            'files'
        ]);

        // Advanced search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reason', 'like', '%' . $this->search . '%')
                    ->orWhere('justificationID', 'like', '%' . $this->search . '%')
                    ->orWhereHas('placementApplication.student', function ($subQ) {
                        $subQ->where('studentID', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('placementApplication.student.user', function ($subQ) {
                        $subQ->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('placementApplication', function ($subQ) {
                        $subQ->where('companyName', 'like', '%' . $this->search . '%')
                            ->orWhere('position', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Status filter
        if ($this->statusFilter) {
            if ($this->statusFilter === 'Pending') {
                $query->where(function ($q) {
                    $q->where('committeeStatus', 'Pending')
                      ->orWhere('coordinatorStatus', 'Pending');
                });
            } elseif ($this->statusFilter === 'Approved') {
                $query->where('committeeStatus', 'Approved')
                      ->where('coordinatorStatus', 'Approved');
            } elseif ($this->statusFilter === 'Rejected') {
                $query->where(function ($q) {
                    $q->where('committeeStatus', 'Rejected')
                      ->orWhere('coordinatorStatus', 'Rejected');
                });
            }
        }

        // Company filter
        if ($this->companyFilter) {
            $query->whereHas('placementApplication', function ($q) {
                $q->where('companyName', 'like', '%' . $this->companyFilter . '%');
            });
        }

        // Student filter
        if ($this->studentFilter) {
            $query->whereHas('placementApplication.student', function ($q) {
                $q->where('studentID', 'like', '%' . $this->studentFilter . '%');
            });
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->where('requestDate', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('requestDate', '<=', $this->dateTo);
        }

        // Role-based filter
        if ($this->roleFilter === 'committee_pending') {
            $query->where('committeeStatus', 'Pending');
        } elseif ($this->roleFilter === 'coordinator_pending') {
            $query->where('coordinatorStatus', 'Pending')
                  ->where('committeeStatus', 'Approved');
        }

        // Apply sorting
        if ($this->sortField) {
            if (in_array($this->sortField, ['justificationID', 'requestDate', 'decisionDate', 'committeeStatus', 'coordinatorStatus'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            } elseif ($this->sortField === 'studentName') {
                $query->join('placement_applications', 'request_justifications.applicationID', '=', 'placement_applications.applicationID')
                      ->join('students', 'placement_applications.studentID', '=', 'students.studentID')
                      ->join('users', 'students.user_id', '=', 'users.id')
                      ->orderBy('users.name', $this->sortDirection)
                      ->select('request_justifications.*');
            } elseif ($this->sortField === 'companyName') {
                $query->join('placement_applications', 'request_justifications.applicationID', '=', 'placement_applications.applicationID')
                      ->orderBy('placement_applications.companyName', $this->sortDirection)
                      ->select('request_justifications.*');
            }
        }

        return $query;
    }

    public function getAnalyticsData()
    {
        $analytics = [
            'total_requests' => RequestJustification::count(),
            'pending_requests' => RequestJustification::where('committeeStatus', 'Pending')
                ->orWhere('coordinatorStatus', 'Pending')->count(),
            'approved_requests' => RequestJustification::where('committeeStatus', 'Approved')
                ->where('coordinatorStatus', 'Approved')->count(),
            'rejected_requests' => RequestJustification::where('committeeStatus', 'Rejected')
                ->orWhere('coordinatorStatus', 'Rejected')->count(),
            'committee_pending' => RequestJustification::where('committeeStatus', 'Pending')->count(),
            'coordinator_pending' => RequestJustification::where('coordinatorStatus', 'Pending')
                ->where('committeeStatus', 'Approved')->count(),
            'requests_this_month' => RequestJustification::whereMonth('requestDate', now()->month)
                ->whereYear('requestDate', now()->year)->count(),
        ];

        return $analytics;
    }

    public function render()
    {
        $requests = $this->getFilteredRequests()->paginate($this->perPage);
        $analytics = $this->showAnalytics ? $this->getAnalyticsData() : null;

        // Get unique students and companies for filters
        $students = Student::with('user')->get()->pluck('user.name', 'studentID')->sort();
        $companies = PlacementApplication::distinct()->pluck('companyName')->sort();

        return view('livewire.lecturer-change-request-table', [
            'requests' => $requests,
            'analytics' => $analytics,
            'students' => $students,
            'companies' => $companies,
        ]);
    }
}
