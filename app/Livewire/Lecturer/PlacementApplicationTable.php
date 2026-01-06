<?php

namespace App\Livewire\Lecturer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PlacementApplication;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\PlacementApplicationStatusNotification;
use Dompdf\Dompdf;
use Dompdf\Options;

class PlacementApplicationTable extends Component
{
    use WithPagination;

    // Search and filter properties
    public $search = '';
    public $sortField = 'applicationDate';
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
    public $session = '';
    public $exportFormat = 'csv';

    // Modal properties
    public $showDetailModal = false;
    public $selectedApplication = null;
    public $remarks = '';

    // Analytics properties
    public $showAnalytics = true;

    // Bulk selection properties
    public $selectedApplications = [];
    public $selectAll = false;
    public $bulkRemarks = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'applicationDate'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'studentFilter' => ['except' => ''],
        'companyFilter' => ['except' => ''],
        'program' => ['except' => ''],
        'semester' => ['except' => ''],
        'session' => ['except' => ''],
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

    public function updatingSession()
    {
        $this->resetPage();
    }

    public function updatingCompanyFilter()
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

    public function viewApplication($id)
    {
        $this->selectedApplication = PlacementApplication::with(['student.user', 'committee', 'coordinator', 'files'])
            ->findOrFail($id);

        // Load existing remarks
        $this->remarks = $this->selectedApplication->remarks ?? '';

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedApplication = null;
        $this->remarks = '';
    }

    public function approveAsCommittee($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can approve applications as committee.');
            return;
        }

        $this->processApproval($id, 'committee', 'Approved');
    }

    public function rejectAsCommittee($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can reject applications as committee.');
            return;
        }

        $this->processApproval($id, 'committee', 'Rejected');
    }

    public function approveAsCoordinator($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can approve applications as coordinator.');
            return;
        }

        $this->processApproval($id, 'coordinator', 'Approved');
    }

    public function rejectAsCoordinator($id)
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can reject applications as coordinator.');
            return;
        }

        $this->processApproval($id, 'coordinator', 'Rejected');
    }

    private function processApproval($id, $role, $status)
    {
        try {
            $application = PlacementApplication::findOrFail($id);
            $lecturer = Auth::user()->lecturer;

            if (!$lecturer) {
                session()->flash('error', 'Lecturer profile not found.');
                return;
            }

            // Update application based on role
            $updateData = [
                'remarks' => $this->remarks,
            ];

            if ($role === 'committee') {
                $updateData['committeeID'] = $lecturer->lecturerID;
                $updateData['committeeStatus'] = $status;

                // Special case: If committee rejects, also set coordinator to rejected
                // This prevents the need for coordinator review on rejected applications
                if ($status === 'Rejected') {
                    $updateData['coordinatorStatus'] = 'Rejected';
                }
            } else {
                $updateData['coordinatorID'] = $lecturer->lecturerID;
                $updateData['coordinatorStatus'] = $status;
            }

            $application->update($updateData);

            // Refresh the selected application
            if ($this->selectedApplication) {
                $this->selectedApplication = PlacementApplication::with(['student.user', 'committee', 'coordinator', 'files'])
                    ->findOrFail($id);
            }

            // Send email notification
            $this->sendStatusNotification($application);

            // Generate appropriate success message
            if ($status === 'Approved') {
                $message = $role === 'committee' ? 'Application approved by committee successfully!' : 'Application approved by coordinator successfully!';
            } else {
                if ($role === 'committee') {
                    $message = 'Application rejected by committee. Coordinator status also set to rejected.';
                } else {
                    $message = 'Application rejected by coordinator.';
                }
            }
            session()->flash('message', $message);

            // Reset pagination and dispatch update event
            $this->resetPage();
            $this->dispatch('application-status-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function sendStatusNotification($application)
    {
        try {
            // Send email to student
            Mail::to($application->student->user->email)
                ->send(new PlacementApplicationStatusNotification($application));
        } catch (\Exception $e) {
            Log::error('Failed to send placement application notification: ' . $e->getMessage());
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
        $this->reset(['search', 'statusFilter', 'studentFilter', 'companyFilter', 'dateFrom', 'dateTo', 'roleFilter', 'program', 'semester', 'session']);
        $this->resetPage();
    }

    public function toggleAnalytics()
    {
        $this->showAnalytics = !$this->showAnalytics;
    }

    // Bulk selection methods
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select all applications on current filtered results (only pending ones for the user's role)
            $lecturer = Auth::user()->lecturer;
            $query = $this->getFilteredApplications();

            if ($lecturer->isCommittee && !$lecturer->isCoordinator) {
                // Committee members can only select applications pending committee review
                $query->where('committeeStatus', 'Pending');
            } elseif ($lecturer->isCoordinator && !$lecturer->isCommittee) {
                // Coordinators can only select applications pending coordinator review
                $query->where('coordinatorStatus', 'Pending')
                    ->where('committeeStatus', 'Approved');
            } elseif ($lecturer->isCommittee && $lecturer->isCoordinator) {
                // If user is both, select all pending
                $query->where(function ($q) {
                    $q->where('committeeStatus', 'Pending')
                        ->orWhere(function ($q2) {
                            $q2->where('coordinatorStatus', 'Pending')
                                ->where('committeeStatus', 'Approved');
                        });
                });
            }

            $this->selectedApplications = $query->pluck('applicationID')->toArray();
        } else {
            $this->selectedApplications = [];
        }
    }

    public function bulkApproveCommittee()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can approve applications as committee.');
            return;
        }

        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to approve.');
            return;
        }

        $this->processBulkApproval('committee', 'Approved');
    }

    public function bulkRejectCommittee()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCommittee) {
            session()->flash('error', 'Access denied. Only committee members can reject applications as committee.');
            return;
        }

        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to reject.');
            return;
        }

        $this->processBulkApproval('committee', 'Rejected');
    }

    public function bulkApproveCoordinator()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can approve applications as coordinator.');
            return;
        }

        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to approve.');
            return;
        }

        $this->processBulkApproval('coordinator', 'Approved');
    }

    public function bulkRejectCoordinator()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer || !$lecturer->isCoordinator) {
            session()->flash('error', 'Access denied. Only coordinators can reject applications as coordinator.');
            return;
        }

        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to reject.');
            return;
        }

        $this->processBulkApproval('coordinator', 'Rejected');
    }

    private function processBulkApproval($role, $status)
    {
        try {
            $lecturer = Auth::user()->lecturer;
            $count = 0;

            foreach ($this->selectedApplications as $id) {
                $application = PlacementApplication::find($id);

                if (!$application) {
                    continue;
                }

                // Verify the application can be processed by this role
                if ($role === 'committee' && $application->committeeStatus !== 'Pending') {
                    continue;
                }
                if ($role === 'coordinator' && $application->coordinatorStatus !== 'Pending') {
                    continue;
                }

                // Update application based on role
                $updateData = [
                    'remarks' => $this->bulkRemarks ?: ($status === 'Approved' ? 'Approved' : 'Rejected'),
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

                $application->update($updateData);

                // Send email notification
                $this->sendStatusNotification($application);

                $count++;
            }

            if ($count > 0) {
                $roleText = $role === 'committee' ? 'committee' : 'coordinator';
                $actionText = $status === 'Approved' ? 'approved' : 'rejected';
                session()->flash('message', "Successfully {$actionText} {$count} application(s) as {$roleText}!");
            } else {
                session()->flash('error', 'No valid applications were processed. They may have already been reviewed.');
            }

            // Clear selections
            $this->selectedApplications = [];
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
        if (empty($this->selectedApplications)) {
            session()->flash('error', 'Please select at least one application to download documents.');
            return;
        }

        try {
            $applications = PlacementApplication::with('files', 'student')
                ->whereIn('applicationID', $this->selectedApplications)
                ->get();

            if ($applications->isEmpty()) {
                session()->flash('error', 'No applications found.');
                return;
            }

            // Create a temporary zip file
            $zipFileName = 'placement_documents_' . date('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($applications as $application) {
                    // Create a folder for each application
                    $folderName = 'App_' . $application->applicationID . '_' . $application->student->studentID;

                    foreach ($application->files as $file) {
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

    private function getFilteredApplications()
    {
        $query = PlacementApplication::with(['student.user', 'committee', 'coordinator', 'files', 'changeRequests']);

        // Advanced search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('companyName', 'like', '%' . $this->search . '%')
                    ->orWhere('position', 'like', '%' . $this->search . '%')
                    ->orWhere('applicationID', 'like', '%' . $this->search . '%')
                    ->orWhere('methodOfWork', 'like', '%' . $this->search . '%')
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

        // Company filter
        if ($this->companyFilter) {
            $query->where('companyName', 'like', '%' . $this->companyFilter . '%');
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

        // Program filter
        if ($this->program) {
            $programFullName = $this->getProgramFullName($this->program);
            if ($programFullName) {
                $query->whereHas('student', function ($q) use ($programFullName) {
                    $q->where('program', $programFullName);
                });
            }
        }

        // Semester filter
        if ($this->semester) {
            $query->whereHas('student', function ($q) {
                $q->where('semester', $this->semester);
            });
        }

        // Session filter
        if ($this->session) {
            $query->whereHas('student', function ($q) {
                $q->where('session', $this->session);
            });
        }

        // Apply sorting
        if ($this->sortField) {
            if (in_array($this->sortField, ['applicationID', 'companyName', 'applicationDate', 'committeeStatus', 'coordinatorStatus'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            } elseif ($this->sortField === 'studentID') {
                $query->join('students', 'placement_applications.studentID', '=', 'students.studentID')
                    ->orderBy('students.studentID', $this->sortDirection)
                    ->select('placement_applications.*');
            } elseif ($this->sortField === 'studentName') {
                $query->join('students', 'placement_applications.studentID', '=', 'students.studentID')
                    ->join('users', 'students.user_id', '=', 'users.id')
                    ->orderBy('users.name', $this->sortDirection)
                    ->select('placement_applications.*');
            } elseif ($this->sortField === 'applyCount') {
                // Sort by the number of applications per student
                $query->selectRaw('placement_applications.*,
                    (SELECT COUNT(*) FROM placement_applications pa2
                     WHERE pa2.studentID = placement_applications.studentID) as apply_count')
                    ->orderBy('apply_count', $this->sortDirection);
            } elseif ($this->sortField === 'placementStatus') {
                // Sort by placement status (computed from overall status and student acceptance)
                // Priority: Active (Approved+Accepted) > Defer (Approved but not accepted) > Inactive (Rejected) > Pending
                $query->selectRaw('placement_applications.*,
                    CASE
                        WHEN (committeeStatus = "Approved" AND coordinatorStatus = "Approved" AND studentAcceptance = "Accepted") THEN 1
                        WHEN (committeeStatus = "Approved" AND coordinatorStatus = "Approved" AND studentAcceptance IS NULL) THEN 2
                        WHEN (committeeStatus = "Rejected" OR coordinatorStatus = "Rejected") THEN 3
                        ELSE 4
                    END as placement_status_order')
                    ->orderBy('placement_status_order', $this->sortDirection)
                    ->orderBy('studentAcceptance', $this->sortDirection === 'asc' ? 'desc' : 'asc')
                    ->orderBy('committeeStatus', $this->sortDirection)
                    ->orderBy('coordinatorStatus', $this->sortDirection);
            }
        } else {
            // Default sorting if no sort field is specified
            $query->orderBy('applicationDate', 'desc');
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
            'total_applications' => PlacementApplication::count(),
            'pending_applications' => PlacementApplication::where('committeeStatus', 'Pending')
                ->orWhere('coordinatorStatus', 'Pending')->count(),
            'approved_applications' => PlacementApplication::where('committeeStatus', 'Approved')
                ->where('coordinatorStatus', 'Approved')->count(),
            'rejected_applications' => PlacementApplication::where('committeeStatus', 'Rejected')
                ->orWhere('coordinatorStatus', 'Rejected')->count(),
            'committee_pending' => PlacementApplication::where('committeeStatus', 'Pending')->count(),
            'coordinator_pending' => PlacementApplication::where('coordinatorStatus', 'Pending')
                ->where('committeeStatus', 'Approved')->count(),
            'student_accepted' => PlacementApplication::where('studentAcceptance', 'Accepted')->count(),
            'student_declined' => PlacementApplication::where('studentAcceptance', 'Declined')->count(),
            'applications_this_month' => PlacementApplication::whereMonth('applicationDate', now()->month)
                ->whereYear('applicationDate', now()->year)->count(),
            'top_companies' => PlacementApplication::selectRaw('companyName, COUNT(*) as count')
                ->groupBy('companyName')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
            'applications_by_method' => PlacementApplication::selectRaw('methodOfWork, COUNT(*) as count')
                ->groupBy('methodOfWork')
                ->get(),
        ];

        return $analytics;
    }

    public function exportData()
    {
        // Get all filtered applications (not paginated)
        $applications = $this->getFilteredApplications()->get();

        if ($applications->isEmpty()) {
            session()->flash('error', 'No data to export. Please apply filters to select applications.');
            return;
        }

        // Route to appropriate export method based on format
        switch ($this->exportFormat) {
            case 'csv':
                return $this->exportCSV($applications);
            case 'pdf':
                return $this->exportPDF($applications);
            default:
                return $this->exportCSV($applications);
        }
    }

    private function exportCSV($applications)
    {
        // Create descriptive filename
        $statusText = $this->statusFilter ? '_' . strtolower($this->statusFilter) : '';
        $sessionText = $this->session ? '_' . str_replace('/', '-', $this->session) : '';
        $semesterText = $this->semester ? '_sem' . $this->semester : '';
        $searchText = $this->search ? '_filtered' : '';

        $filename = 'placement_applications' . $statusText . $sessionText . $semesterText . $searchText . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Generate CSV content
        $csvData = $this->generateCSV($applications);

        // Flash success message
        session()->flash('message', 'CSV file exported successfully! Downloaded ' . $applications->count() . ' records.');

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportPDF($applications)
    {
        // Create descriptive filename
        $statusText = $this->statusFilter ? '_' . strtolower($this->statusFilter) : '';
        $sessionText = $this->session ? '_' . str_replace('/', '-', $this->session) : '';
        $semesterText = $this->semester ? '_sem' . $this->semester : '';
        $searchText = $this->search ? '_filtered' : '';

        $filename = 'placement_applications' . $statusText . $sessionText . $semesterText . $searchText . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        // Generate PDF content
        $htmlData = $this->generatePDF($applications);

        // Setup DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlData);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Flash success message
        session()->flash('message', 'PDF file exported successfully! Downloaded ' . $applications->count() . ' records.');

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function generateCSV($applications)
    {
        $output = '';

        // Add filter information at the top
        $filters = [];
        if ($this->statusFilter) $filters['Status'] = $this->statusFilter;
        if ($this->program) $filters['Program'] = $this->program;
        if ($this->semester) $filters['Semester'] = $this->semester;
        if ($this->session) $filters['Session'] = $this->session;
        if ($this->studentFilter) $filters['Student'] = $this->studentFilter;
        if ($this->companyFilter) $filters['Company'] = $this->companyFilter;
        if ($this->dateFrom) $filters['Date From'] = $this->dateFrom;
        if ($this->dateTo) $filters['Date To'] = $this->dateTo;

        if (!empty($filters)) {
            $output .= '"Applied Filters:"' . "\n";
            foreach ($filters as $key => $value) {
                $output .= '"' . $key . ': ' . $value . '"' . "\n";
            }
            $output .= "\n";
        }

        // Headers
        $headers = [
            'Application ID',
            'Student ID',
            'Student Name',
            'Company Name',
            'Position',
            'Application Date',
            'Start Date',
            'End Date',
            'Method of Work',
            'Allowance',
            'Committee Status',
            'Coordinator Status',
            'Program',
            'Semester',
            'Session',
        ];

        $output .= '"' . implode('","', $headers) . '"' . "\n";

        // Data rows
        foreach ($applications as $app) {
            $row = [
                $app->applicationID ?? '',
                $app->student->studentID ?? '',
                $app->student->user->name ?? '',
                $app->companyName ?? '',
                $app->position ?? '',
                $app->applicationDate ? \Carbon\Carbon::parse($app->applicationDate)->format('Y-m-d') : '',
                $app->startDate ? \Carbon\Carbon::parse($app->startDate)->format('Y-m-d') : '',
                $app->endDate ? \Carbon\Carbon::parse($app->endDate)->format('Y-m-d') : '',
                $app->methodOfWork ?? '',
                $app->allowance ?? '',
                $app->committeeStatus ?? '',
                $app->coordinatorStatus ?? '',
                $app->student->program ?? '',
                $app->student->semester ?? '',
                $app->student->session ?? '',
            ];

            $output .= '"' . implode('","', array_map(function ($field) {
                return str_replace('"', '""', $field);
            }, $row)) . '"' . "\n";
        }

        return $output;
    }

    private function generatePDF($applications)
    {
        $logoPath = public_path('LOGO IL.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Placement Applications Report</title>
    <style>
        @page {
            margin: 20mm 15mm;
            footer: html_footer;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9px;
            color: #1f2937;
            line-height: 1.4;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 20px 25px;
            margin: -20mm -15mm 20px -15mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header-content {
            flex: 1;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            font-size: 12px;
            opacity: 0.95;
            font-weight: 300;
        }
        .logo {
            max-height: 60px;
            max-width: 120px;
            margin-left: 20px;
        }
        .report-info {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .report-info p {
            margin: 4px 0;
            font-size: 10px;
            color: #4b5563;
        }
        .report-info strong {
            color: #1f2937;
            font-weight: 600;
        }
        .filters-box {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 15px;
            margin: 15px 0;
        }
        .filters-box p {
            margin: 3px 0;
            font-size: 9px;
            color: #6b7280;
        }
        .filters-box strong {
            color: #374151;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        thead {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
        }
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #1e3a8a;
        }
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        td {
            padding: 8px;
            font-size: 8.5px;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            margin-top: 20px;
        }
        .page-number {
            font-weight: 600;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Placement Applications Report</h1>
            <div class="subtitle">InternLink Management System</div>
        </div>';

        if ($logoBase64) {
            $html .= '<img src="' . $logoBase64 . '" alt="InternLink Logo" class="logo" />';
        }

        $html .= '</div>

    <div class="report-info">
        <p><strong>Generated:</strong> ' . now()->format('F d, Y \a\t H:i:s') . '</p>
        <p><strong>Total Records:</strong> ' . $applications->count() . ' application(s)</p>
        <p><strong>Generated By:</strong> ' . (Auth::user()->name ?? 'System') . '</p>
    </div>';

        $filters = [];
        if ($this->statusFilter) $filters['Status'] = $this->statusFilter;
        if ($this->program) $filters['Program'] = $this->program;
        if ($this->semester) $filters['Semester'] = $this->semester;
        if ($this->session) $filters['Session'] = $this->session;
        if ($this->studentFilter) $filters['Student'] = $this->studentFilter;
        if ($this->companyFilter) $filters['Company'] = $this->companyFilter;
        if ($this->dateFrom) $filters['Date From'] = $this->dateFrom;
        if ($this->dateTo) $filters['Date To'] = $this->dateTo;

        if (!empty($filters)) {
            $html .= '<div class="filters-box">
                <p><strong>Applied Filters:</strong></p>';
            foreach ($filters as $key => $value) {
                $html .= '<p>' . htmlspecialchars($key) . ': <strong>' . htmlspecialchars($value) . '</strong></p>';
            }
            $html .= '</div>';
        }

        $html .= '<table>
        <thead>
            <tr>
                <th>App ID</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Company</th>
                <th>Position</th>
                <th>App Date</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Work Method</th>
                <th>Allowance</th>
                <th>Committee</th>
                <th>Coordinator</th>
                <th>Program</th>
                <th>Sem</th>
                <th>Session</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($applications as $app) {
            $committeeStatusClass = 'status-' . strtolower($app->committeeStatus ?? 'pending');
            $coordinatorStatusClass = 'status-' . strtolower($app->coordinatorStatus ?? 'pending');

            $html .= '<tr>
                <td><strong>' . htmlspecialchars($app->applicationID ?? '') . '</strong></td>
                <td>' . htmlspecialchars($app->student->studentID ?? '') . '</td>
                <td>' . htmlspecialchars($app->student->user->name ?? '') . '</td>
                <td>' . htmlspecialchars($app->companyName ?? '') . '</td>
                <td>' . htmlspecialchars($app->position ?? '') . '</td>
                <td>' . ($app->applicationDate ? \Carbon\Carbon::parse($app->applicationDate)->format('M d, Y') : '') . '</td>
                <td>' . ($app->startDate ? \Carbon\Carbon::parse($app->startDate)->format('M d, Y') : '') . '</td>
                <td>' . ($app->endDate ? \Carbon\Carbon::parse($app->endDate)->format('M d, Y') : '') . '</td>
                <td>' . htmlspecialchars($app->methodOfWork ?? '') . '</td>
                <td>' . ($app->allowance ? 'RM ' . number_format($app->allowance, 0) : '-') . '</td>
                <td><span class="status-badge ' . $committeeStatusClass . '">' . htmlspecialchars($app->committeeStatus ?? 'Pending') . '</span></td>
                <td><span class="status-badge ' . $coordinatorStatusClass . '">' . htmlspecialchars($app->coordinatorStatus ?? 'Pending') . '</span></td>
                <td>' . htmlspecialchars($app->student->program ?? '') . '</td>
                <td>' . htmlspecialchars($app->student->semester ?? '') . '</td>
                <td>' . htmlspecialchars($app->student->session ?? '') . '</td>
            </tr>';
        }

        $html .= '</tbody>
    </table>

    <div class="footer">
        <p>This is an official report generated from InternLink Management System</p>
        <p class="page-number">Page {PAGENO} of {nbpg}</p>
        <p>© ' . date('Y') . ' InternLink. All rights reserved.</p>
    </div>
</body>
</html>';

        return $html;
    }

    public function render()
    {
        $applications = $this->getFilteredApplications()->paginate($this->perPage);
        $analytics = $this->getAnalyticsData();

        // Get unique companies and students for filters
        $companies = PlacementApplication::distinct('companyName')->pluck('companyName')->sort();
        $students = Student::with('user')->get()->pluck('user.name', 'studentID')->sort();

        // Calculate applyCount for each student
        $applyCounts = PlacementApplication::selectRaw('studentID, COUNT(*) as count')
            ->groupBy('studentID')
            ->pluck('count', 'studentID')
            ->toArray();

        // Add applyCount to each application
        foreach ($applications as $application) {
            // If sorting by applyCount, use the calculated value from the query
            if ($this->sortField === 'applyCount' && isset($application->apply_count)) {
                $application->applyCount = $application->apply_count;
            } else {
                $application->applyCount = $applyCounts[$application->studentID] ?? 0;
            }
        }

        return view('livewire.lecturer.placementApplicationTable', [
            'applications' => $applications,
            'analytics' => $analytics,
            'companies' => $companies,
            'students' => $students,
        ]);
    }
}
