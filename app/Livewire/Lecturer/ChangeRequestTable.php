<?php

namespace App\Livewire\Lecturer;

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

class ChangeRequestTable extends Component
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
    public $program = '';
    public $semester = '';
    public $year = '';

    // Modal properties
    public $showDetailModal = false;
    public $selectedRequest = null;
    public $remarks = '';

    // Analytics properties
    public $showAnalytics = true;

    // Bulk selection properties
    public $selectedRequests = [];
    public $selectAll = false;
    public $bulkRemarks = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'requestDate'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'studentFilter' => ['except' => ''],
        'program' => ['except' => ''],
        'semester' => ['except' => ''],
        'year' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        // Check if user is coordinator or committee member
        $user = Auth::user();
        if (!$user->lecturer) {
            abort(403, 'Access denied. Lecturer profile required.');
        }
        
        // Clear any flash messages from previous components
        session()->forget(['message', 'error', 'warning']);
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

    public function updatingProgram()
    {
        $this->resetPage();
    }

    public function updatingSemester()
    {
        $this->resetPage();
    }

    public function updatingYear()
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

            // Refresh the request to get updated status
            $request->refresh();

            // If both committee and coordinator have approved the change request,
            // update the original application's studentAcceptance to 'Changed'
            if ($request->committeeStatus === 'Approved' && $request->coordinatorStatus === 'Approved') {
                $originalApplication = $request->placementApplication;
                if ($originalApplication && $originalApplication->studentAcceptance === 'Accepted') {
                    $originalApplication->update(['studentAcceptance' => 'Changed']);
                }
            }

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

    // Bulk selection methods
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select all requests on current filtered results (only pending ones for the user's role)
            $lecturer = Auth::user()->lecturer;
            $query = $this->getFilteredRequests();
            
            if ($lecturer->isCommittee && !$lecturer->isCoordinator) {
                // Committee members can only select requests pending committee review
                $query->where('committeeStatus', 'Pending');
            } elseif ($lecturer->isCoordinator && !$lecturer->isCommittee) {
                // Coordinators can only select requests pending coordinator review
                $query->where('coordinatorStatus', 'Pending')
                      ->where('committeeStatus', 'Approved');
            } elseif ($lecturer->isCommittee && $lecturer->isCoordinator) {
                // If user is both, select all pending
                $query->where(function($q) {
                    $q->where('committeeStatus', 'Pending')
                      ->orWhere(function($q2) {
                          $q2->where('coordinatorStatus', 'Pending')
                             ->where('committeeStatus', 'Approved');
                      });
                });
            }
            
            $this->selectedRequests = $query->pluck('justificationID')->toArray();
        } else {
            $this->selectedRequests = [];
        }
    }

    public function toggleRequestSelection($id)
    {
        if (in_array($id, $this->selectedRequests)) {
            $this->selectedRequests = array_values(array_diff($this->selectedRequests, [$id]));
        } else {
            $this->selectedRequests[] = $id;
        }
        $this->selectAll = false;
    }

    public function bulkApproveCommittee()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can approve requests as committee.');
            return;
        }

        if (empty($this->selectedRequests)) {
            session()->flash('error', 'Please select at least one request to approve.');
            return;
        }

        $this->processBulkApproval('committee', 'Approved');
    }

    public function bulkRejectCommittee()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can reject requests as committee.');
            return;
        }

        if (empty($this->selectedRequests)) {
            session()->flash('error', 'Please select at least one request to reject.');
            return;
        }

        $this->processBulkApproval('committee', 'Rejected');
    }

    public function bulkApproveCoordinator()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can approve requests as coordinator.');
            return;
        }

        if (empty($this->selectedRequests)) {
            session()->flash('error', 'Please select at least one request to approve.');
            return;
        }

        $this->processBulkApproval('coordinator', 'Approved');
    }

    public function bulkRejectCoordinator()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can reject requests as coordinator.');
            return;
        }

        if (empty($this->selectedRequests)) {
            session()->flash('error', 'Please select at least one request to reject.');
            return;
        }

        $this->processBulkApproval('coordinator', 'Rejected');
    }

    private function processBulkApproval($role, $status)
    {
        try {
            $lecturer = Auth::user()->lecturer;
            $count = 0;

            foreach ($this->selectedRequests as $id) {
                $request = RequestJustification::find($id);
                
                if (!$request) {
                    continue;
                }

                // Verify the request can be processed by this role
                if ($role === 'committee' && $request->committeeStatus !== 'Pending') {
                    continue;
                }
                if ($role === 'coordinator' && $request->coordinatorStatus !== 'Pending') {
                    continue;
                }
                if ($role === 'coordinator' && $request->committeeStatus !== 'Approved') {
                    continue;
                }

                // Update request based on role
                $updateData = [
                    'remarks' => $this->bulkRemarks ?: ($status === 'Approved' ? 'Bulk approved' : 'Bulk rejected'),
                ];

                if ($role === 'committee') {
                    $updateData['committeeID'] = $lecturer->lecturerID;
                    $updateData['committeeStatus'] = $status;
                    
                    // If committee rejects, also set coordinator to rejected
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

                // Refresh the request to get updated status
                $request->refresh();

                // If both committee and coordinator have approved the change request,
                // update the original application's studentAcceptance to 'Changed'
                if ($request->committeeStatus === 'Approved' && $request->coordinatorStatus === 'Approved') {
                    $originalApplication = $request->placementApplication;
                    if ($originalApplication && $originalApplication->studentAcceptance === 'Accepted') {
                        $originalApplication->update(['studentAcceptance' => 'Changed']);
                    }
                }
                
                // Send email notification
                $this->sendStatusNotification($request);
                
                $count++;
            }

            if ($count > 0) {
                $roleText = $role === 'committee' ? 'committee' : 'coordinator';
                $actionText = $status === 'Approved' ? 'approved' : 'rejected';
                session()->flash('message', "Successfully {$actionText} {$count} change request(s) as {$roleText}!");
            } else {
                session()->flash('error', 'No valid requests were processed. They may have already been reviewed.');
            }

            // Clear selections
            $this->selectedRequests = [];
            $this->selectAll = false;
            $this->bulkRemarks = '';
            $this->resetPage();
            
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred during bulk processing: ' . $e->getMessage());
            Log::error('Bulk approval error: ' . $e->getMessage());
        }
    }

    public function bulkDownload()
    {
        if (empty($this->selectedRequests)) {
            session()->flash('error', 'Please select at least one request to download documents.');
            return;
        }

        try {
            $requests = RequestJustification::with('files', 'placementApplication.student')
                ->whereIn('justificationID', $this->selectedRequests)
                ->get();

            if ($requests->isEmpty()) {
                session()->flash('error', 'No requests found.');
                return;
            }

            // Create a temporary zip file
            $zipFileName = 'change_request_documents_' . date('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($requests as $request) {
                    // Create a folder for each request
                    $folderName = 'Change_' . $request->justificationID . '_' . $request->placementApplication->student->studentID;
                    
                    foreach ($request->files as $file) {
                        $filePath = storage_path('app/public/' . $file->file_path);
                        if (file_exists($filePath)) {
                            // Add file to zip with organized structure
                            $zip->addFile($filePath, $folderName . '/' . $file->original_name);
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

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'studentFilter', 'companyFilter', 'dateFrom', 'dateTo', 'roleFilter', 'program', 'semester', 'year']);
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

        // Program filter
        if ($this->program) {
            $programFullName = $this->getProgramFullName($this->program);
            if ($programFullName) {
                $query->whereHas('placementApplication.student', function ($q) use ($programFullName) {
                    $q->where('program', $programFullName);
                });
            }
        }

        // Semester filter
        if ($this->semester) {
            $query->whereHas('placementApplication.student', function ($q) {
                $q->where('semester', $this->semester);
            });
        }

        // Year filter
        if ($this->year) {
            $query->whereHas('placementApplication.student', function ($q) {
                $q->where('year', $this->year);
            });
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
            }
        }

        return $query;
    }

    /**
     * Get program mapping from code to full name for filtering
     */
    private function getProgramFullName($code): ?string
    {
        $programs = [
            'BCS' => 'Bachelor of Computer Science (Software Engineering) with Honours',
            'BCN' => 'Bachelor of Computer Science (Computer Systems & Networking) with Honours',
            'BCM' => 'Bachelor of Computer Science (Multimedia Software) with Honours',
            'BCY' => 'Bachelor of Computer Science (Cyber Security) with Honours',
            'DRC' => 'Diploma in Computer Science',
        ];

        return $programs[$code] ?? null;
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
        $analytics = $this->getAnalyticsData();

        // Get unique students and companies for filters
        $students = Student::with('user')->get()->pluck('user.name', 'studentID')->sort();
        $companies = PlacementApplication::distinct()->pluck('companyName')->sort();

        return view('livewire.lecturer.changeRequestTable', [
            'requests' => $requests,
            'analytics' => $analytics,
            'students' => $students,
            'companies' => $companies,
        ]);
    }
}
