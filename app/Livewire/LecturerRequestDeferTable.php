<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RequestDefer;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\RequestDeferStatusNotification;

class LecturerRequestDeferTable extends Component
{
    use WithPagination;

    // Search and filter properties
    public $search = '';
    public $sortField = 'applicationDate';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';
    public $studentFilter = '';
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
        'sortField' => ['except' => 'applicationDate'],
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
        $this->selectedRequest = RequestDefer::with(['student.user', 'committee', 'coordinator', 'files'])
            ->findOrFail($id);

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
            $request = RequestDefer::findOrFail($id);
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
                // This prevents the need for coordinator review on rejected requests
                if ($status === 'Rejected') {
                    $updateData['coordinatorStatus'] = 'Rejected';
                }
            } else {
                $updateData['coordinatorID'] = $lecturer->lecturerID;
                $updateData['coordinatorStatus'] = $status;
            }

            $request->update($updateData);

            // Refresh the selected request
            if ($this->selectedRequest) {
                $this->selectedRequest = RequestDefer::with(['student.user', 'committee', 'coordinator', 'files'])
                    ->findOrFail($id);
            }

            // Send email notification
            $this->sendStatusNotification($request);

            // Generate appropriate success message
            if ($status === 'Approved') {
                $message = $role === 'committee' ? 'Defer request approved by committee successfully! Email notification sent to student.' : 'Defer request approved by coordinator successfully! Email notification sent to student.';
            } else {
                if ($role === 'committee') {
                    $message = 'Defer request rejected by committee. Coordinator status also set to rejected. Email notification sent to student.';
                } else {
                    $message = 'Defer request rejected by coordinator. Email notification sent to student.';
                }
            }
            session()->flash('message', $message);

            // Reset pagination and dispatch update event
            $this->resetPage();
            $this->dispatch('request-status-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
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

    private function sendStatusNotification($request)
    {
        try {
            // Reload the request with all relationships to ensure we have fresh data
            $request = RequestDefer::with(['student.user', 'committee', 'coordinator'])
                ->findOrFail($request->deferID);

            // Send email to student
            Mail::to($request->student->user->email)
                ->send(new RequestDeferStatusNotification($request));

            Log::info('Defer request status notification sent', [
                'request_id' => $request->deferID,
                'student_id' => $request->student->studentID,
                'student_email' => $request->student->user->email,
                'overall_status' => $request->overall_status,
                'committee_status' => $request->committeeStatus,
                'coordinator_status' => $request->coordinatorStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send defer request status notification', [
                'request_id' => $request->deferID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't throw the exception to avoid breaking the approval process
            // Just log the error and continue
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'studentFilter', 'dateFrom', 'dateTo', 'roleFilter']);
        $this->resetPage();
    }

    public function toggleAnalytics()
    {
        $this->showAnalytics = !$this->showAnalytics;
    }

    private function getFilteredRequests()
    {
        $query = RequestDefer::with(['student.user', 'committee', 'coordinator', 'files']);

        // Advanced search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reason', 'like', '%' . $this->search . '%')
                    ->orWhere('deferID', 'like', '%' . $this->search . '%')
                    ->orWhereHas('student', function ($subQ) {
                        $subQ->where('studentID', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('student.user', function ($subQ) {
                        $subQ->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
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

        // Student filter
        if ($this->studentFilter) {
            $query->whereHas('student', function ($q) {
                $q->where('studentID', 'like', '%' . $this->studentFilter . '%');
            });
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->where('applicationDate', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('applicationDate', '<=', $this->dateTo);
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
            if (in_array($this->sortField, ['deferID', 'applicationDate', 'startDate', 'endDate', 'committeeStatus', 'coordinatorStatus'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            } elseif ($this->sortField === 'studentName') {
                $query->join('students', 'request_defers.studentID', '=', 'students.studentID')
                    ->join('users', 'students.user_id', '=', 'users.id')
                    ->orderBy('users.name', $this->sortDirection)
                    ->select('request_defers.*');
            }
        }

        return $query;
    }

    public function getAnalyticsData()
    {
        $analytics = [
            'total_requests' => RequestDefer::count(),
            'pending_requests' => RequestDefer::where('committeeStatus', 'Pending')
                ->orWhere('coordinatorStatus', 'Pending')->count(),
            'approved_requests' => RequestDefer::where('committeeStatus', 'Approved')
                ->where('coordinatorStatus', 'Approved')->count(),
            'rejected_requests' => RequestDefer::where('committeeStatus', 'Rejected')
                ->orWhere('coordinatorStatus', 'Rejected')->count(),
            'committee_pending' => RequestDefer::where('committeeStatus', 'Pending')->count(),
            'coordinator_pending' => RequestDefer::where('coordinatorStatus', 'Pending')
                ->where('committeeStatus', 'Approved')->count(),
            'requests_this_month' => RequestDefer::whereMonth('applicationDate', now()->month)
                ->whereYear('applicationDate', now()->year)->count(),
        ];

        return $analytics;
    }

    public function render()
    {
        $requests = $this->getFilteredRequests()->paginate($this->perPage);
        $analytics = $this->showAnalytics ? $this->getAnalyticsData() : null;

        // Get unique students for filters
        $students = Student::with('user')->get()->pluck('user.name', 'studentID')->sort();

        return view('livewire.lecturer-request-defer-table', [
            'requests' => $requests,
            'analytics' => $analytics,
            'students' => $students,
        ]);
    }
}
