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
use App\Models\RequestJustification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\GeocodingService;
use Illuminate\Support\Collection;

class StudentPlacementApplicationTable extends Component
{
    use WithPagination, WithFileUploads;

    private GeocodingService $geocodingService;

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
    public $viewingApplication = null;

    // Application form data
    public $companyName = '';
    public $companyAddressLine = '';
    public $companyCity = '';
    public $companyPostcode = '';
    public $companyState = '';
    public $companyCountry = '';
    public $companyLatitude = '';
    public $companyLongitude = '';
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

    // Change Request properties
    public $showChangeRequestForm = false;
    public $changeRequestApplicationID = null;
    public $changeRequestReason = '';
    public $changeRequestFiles = [];
    public $viewingChangeRequests = false;
    public $selectedApplicationForChange = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'applicationDate'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected function getPlacementApplicationRules()
    {
        return [
            'companyName' => 'required|string|max:255',
            'companyAddressLine' => 'required|string',
            'companyCity' => 'nullable|string',
            'companyPostcode' => 'nullable|string',
            'companyState' => 'nullable|string',
            'companyCountry' => 'nullable|string',
            'companyEmail' => 'required|email|max:255',
            'companyNumber' => 'required|string|max:20',
            'allowance' => 'nullable|numeric|min:0',
            'position' => 'required|string|max:255',
            'jobscope' => 'required|string',
            'methodOfWork' => 'required|in:WFO,WOS,WOC,WFH,WFO & WFH',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'applicationFiles.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max per file
        ];
    }

    protected function getChangeRequestRules()
    {
        return [
            'changeRequestReason' => 'required|string|min:20|max:1000',
            'changeRequestFiles.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    protected $messages = [
        'companyName.required' => 'Company name is required.',
        'companyAddressLine.required' => 'Company address line is required.',
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
        'changeRequestReason.required' => 'Please provide a reason for the change request.',
        'changeRequestReason.min' => 'Reason must be at least 20 characters.',
        'changeRequestReason.max' => 'Reason must not exceed 1000 characters.',
        'changeRequestFiles.*.mimes' => 'Supporting files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'changeRequestFiles.*.max' => 'Each file must be less than 10MB.',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->geocodingService = new GeocodingService();
    }

    public function mount()
    {
        // Check if student can apply for internship placement
        if (!$this->canStudentApply()) {
            $this->setCannotApplyErrorMessage();
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
            $this->setCannotApplyErrorMessage();
            return;
        }

        $this->showForm = true;
        $this->resetForm();
    }

    public function testSubmit()
    {
        \Log::info('Test submit method called');

        // Test validation with current data
        try {
            $this->validate($this->getPlacementApplicationRules());
            session()->flash('message', 'Test submit: Validation passed! Form data looks good.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages[] = $field . ': ' . implode(', ', $messages);
            }
            session()->flash('error', 'Validation errors: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            session()->flash('error', 'Other error: ' . $e->getMessage());
        }
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
            'companyAddressLine',
            'companyCity',
            'companyPostcode',
            'companyState',
            'companyCountry',
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

        // Reset coordinates manually
        $this->companyLatitude = null;
        $this->companyLongitude = null;

        $this->editingId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function view($id)
    {
        $application = PlacementApplication::with(['files', 'committee', 'coordinator'])->findOrFail($id);

        // Check if this application belongs to the current student
        if ($application->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only view your own applications.');
            return;
        }

        $this->viewingApplication = $application;
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingApplication = null;
    }

    public function edit($id)
    {
        $application = PlacementApplication::with('files')->findOrFail($id);

        // Check if this application belongs to the current student
        if ($application->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only edit your own applications.');
            return;
        }

        // Only allow editing if both committee and coordinator status are pending
        if ($application->committeeStatus !== 'Pending' || $application->coordinatorStatus !== 'Pending') {
            session()->flash('error', 'You can only edit applications that are still pending review by both committee and coordinator.');
            return;
        }

        $this->editingId = $id;
        $this->companyName = $application->companyName;
        $this->companyAddressLine = $application->companyAddressLine ?? '';
        $this->companyCity = $application->companyCity ?? '';
        $this->companyPostcode = $application->companyPostcode ?? '';
        $this->companyState = $application->companyState ?? '';
        $this->companyCountry = $application->companyCountry ?? '';
        $this->companyLatitude = $application->companyLatitude ?? '';
        $this->companyLongitude = $application->companyLongitude ?? '';
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
        // Debug logging
        \Log::info('Submit method called', [
            'user_id' => Auth::id(),
            'company_name' => $this->companyName,
            'editing_id' => $this->editingId,
            'all_form_data' => [
                'companyName' => $this->companyName,
                'companyEmail' => $this->companyEmail,
                'companyAddressLine' => $this->companyAddressLine,
                'companyCity' => $this->companyCity,
                'companyPostcode' => $this->companyPostcode,
                'companyState' => $this->companyState,
                'companyCountry' => $this->companyCountry,
                'companyLatitude' => $this->companyLatitude,
                'companyLongitude' => $this->companyLongitude,
                'position' => $this->position,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate
            ]
        ]);

        try {
            $this->validate($this->getPlacementApplicationRules());
            \Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            // Re-throw validation exception so Livewire can handle it properly
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Other validation error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Validation error: ' . $e->getMessage());
            return;
        }

        try {
            $student = Auth::user()->student;

            if (!$student) {
                session()->flash('error', 'Student profile not found.');
                return;
            }

            // Check if student can apply for internship placement
            if (!$this->canStudentApply()) {
                $this->setCannotApplyErrorMessage();
                return;
            }

            // Try to geocode the company address if coordinates are not provided
            $companyLatitude = $this->companyLatitude;
            $companyLongitude = $this->companyLongitude;

            if (empty($companyLatitude) || empty($companyLongitude)) {
                $geocodeResult = $this->geocodingService->geocodeStructuredAddress([
                    'street' => $this->companyAddressLine,
                    'city' => $this->companyCity,
                    'postcode' => $this->companyPostcode,
                    'state' => $this->companyState,
                    'country' => $this->companyCountry
                ]);

                if ($geocodeResult) {
                    $companyLatitude = $geocodeResult['latitude'];
                    $companyLongitude = $geocodeResult['longitude'];
                }
            }

            $applicationData = [
                'companyName' => $this->companyName,
                'companyAddressLine' => $this->companyAddressLine,
                'companyCity' => $this->companyCity,
                'companyPostcode' => $this->companyPostcode,
                'companyState' => $this->companyState,
                'companyCountry' => $this->companyCountry,
                'companyLatitude' => $companyLatitude,
                'companyLongitude' => $companyLongitude,
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

                \Log::info('Creating new application with data', $applicationData);
                $application = PlacementApplication::create($applicationData);
                \Log::info('Application created successfully', ['application_id' => $application->applicationID]);

                // Upload files
                if (!empty($this->applicationFiles)) {
                    \Log::info('Uploading files', ['file_count' => count($this->applicationFiles)]);
                    $this->uploadFiles($application);
                }

                session()->flash('message', 'Placement application submitted successfully!');
            }

            $this->closeForm();
        } catch (\Exception $e) {
            \Log::error('Submit method exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

        if (!$approvedVerification) {
            return false;
        }

        // Check if student has an accepted placement application
        $hasAcceptedApplication = PlacementApplication::where('studentID', $student->studentID)
            ->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved')
            ->where('studentAcceptance', 'Accepted')
            ->exists();

        // If student has accepted application, they can only apply if they have approved change request
        if ($hasAcceptedApplication) {
            $hasApprovedChangeRequest = RequestJustification::whereHas('placementApplication', function($query) use ($student) {
                $query->where('studentID', $student->studentID);
            })
            ->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved')
            ->exists();

            return $hasApprovedChangeRequest;
        }

        return true;
    }

    private function setCannotApplyErrorMessage()
    {
        $student = Auth::user()->student;

        if (!$student) {
            session()->flash('error', 'Student profile not found.');
            return;
        }

        // Check course verification first
        $approvedVerification = CourseVerification::where('studentID', $student->studentID)
            ->where('status', 'approved')
            ->exists();

        if (!$approvedVerification) {
            session()->flash('error', 'You must have an approved course verification before applying for internship placement.');
            return;
        }

        // Check if student has accepted application
        $hasAcceptedApplication = PlacementApplication::where('studentID', $student->studentID)
            ->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved')
            ->where('studentAcceptance', 'Accepted')
            ->exists();

        if ($hasAcceptedApplication) {
            session()->flash('error', 'You already have an accepted placement application. You can only submit a new application if you have an approved change request.');
            return;
        }

        session()->flash('error', 'You are not eligible to apply for internship placement at this time.');
    }

    private function getFilteredApplications()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return PlacementApplication::query()->where('applicationID', 0); // Return empty query
        }

        $query = PlacementApplication::forStudent($student->studentID)
            ->with(['committee', 'coordinator', 'files', 'changeRequests']);

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

    // Change Request Methods
    public function openChangeRequestForm($applicationID)
    {
        $application = PlacementApplication::with(['student', 'changeRequests'])
            ->findOrFail($applicationID);

        // Check if this application belongs to the current student
        if ($application->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only request changes for your own applications.');
            return;
        }

        // Check if application is approved and accepted
        if ($application->overall_status !== 'Approved' || $application->studentAcceptance !== 'Accepted') {
            session()->flash('error', 'You can only request changes for approved and accepted applications.');
            return;
        }

        // Check if there's already a pending change request
        $pendingChangeRequest = $application->changeRequests()
            ->where(function ($q) {
                $q->where('committeeStatus', 'Pending')
                  ->orWhere('coordinatorStatus', 'Pending');
            })
            ->exists();

        if ($pendingChangeRequest) {
            session()->flash('error', 'You already have a pending change request for this application.');
            return;
        }

        $this->changeRequestApplicationID = $applicationID;
        $this->selectedApplicationForChange = $application;
        $this->resetChangeRequestForm();
        $this->showChangeRequestForm = true;
    }

    public function closeChangeRequestForm()
    {
        $this->showChangeRequestForm = false;
        $this->resetChangeRequestForm();
    }

    private function resetChangeRequestForm()
    {
        $this->reset(['changeRequestReason', 'changeRequestFiles']);
        $this->resetErrorBag();
    }

    public function submitChangeRequest()
    {
        $this->validate($this->getChangeRequestRules());

        try {
            $student = Auth::user()->student;

            if (!$student) {
                session()->flash('error', 'Student profile not found.');
                return;
            }

            // Create the change request
            $changeRequest = RequestJustification::create([
                'applicationID' => $this->changeRequestApplicationID,
                'reason' => $this->changeRequestReason,
                'requestDate' => now()->format('Y-m-d'),
            ]);

            // Handle file uploads
            if (!empty($this->changeRequestFiles)) {
                \Log::info('Starting file upload for change request', [
                    'change_request_id' => $changeRequest->justificationID,
                    'files_to_upload' => count($this->changeRequestFiles)
                ]);
                $this->uploadChangeRequestFiles($changeRequest);

                // Verify files were saved
                $savedFiles = $changeRequest->files()->get();
                \Log::info('Files saved verification', [
                    'change_request_id' => $changeRequest->justificationID,
                    'saved_files_count' => $savedFiles->count(),
                    'file_details' => $savedFiles->map(function($file) {
                        return [
                            'id' => $file->id,
                            'original_name' => $file->original_name,
                            'file_path' => $file->file_path
                        ];
                    })->toArray()
                ]);
            }

            session()->flash('message', 'Change request submitted successfully! You will be notified once it has been reviewed.');
            $this->closeChangeRequestForm();
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function uploadChangeRequestFiles($changeRequest)
    {
        \Log::info('Uploading change request files', [
            'request_id' => $changeRequest->justificationID,
            'file_count' => count($this->changeRequestFiles)
        ]);

        foreach ($this->changeRequestFiles as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('change_requests', $filename, 'public');

            $fileRecord = File::create([
                'fileable_id' => $changeRequest->justificationID,
                'fileable_type' => 'App\\Models\\RequestJustification',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            \Log::info('File created', [
                'file_id' => $fileRecord->id,
                'fileable_id' => $changeRequest->justificationID,
                'fileable_type' => 'App\\Models\\RequestJustification',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName()
            ]);
        }
    }

    public function viewChangeRequests($applicationID)
    {
        $application = PlacementApplication::with(['changeRequests.committee', 'changeRequests.coordinator', 'changeRequests.files'])
            ->findOrFail($applicationID);

        // Check if this application belongs to the current student
        if ($application->studentID !== Auth::user()->student->studentID) {
            session()->flash('error', 'You can only view your own change requests.');
            return;
        }

        // Debug logging
        \Log::info('Viewing change requests', [
            'application_id' => $applicationID,
            'change_requests_count' => $application->changeRequests->count(),
            'change_requests_with_files' => $application->changeRequests->map(function($cr) {
                return [
                    'id' => $cr->justificationID,
                    'files_count' => $cr->files->count(),
                    'files' => $cr->files->map(function($file) {
                        return [
                            'id' => $file->id,
                            'original_name' => $file->original_name,
                            'fileable_id' => $file->fileable_id,
                            'fileable_type' => $file->fileable_type
                        ];
                    })->toArray()
                ];
            })->toArray()
        ]);

        $this->selectedApplicationForChange = $application;
        $this->viewingChangeRequests = true;
    }

    public function closeChangeRequestsView()
    {
        $this->viewingChangeRequests = false;
        $this->selectedApplicationForChange = null;
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

        // Check for approved change requests
        $hasApprovedChangeRequest = false;
        if (Auth::user()->student) {
            $hasApprovedChangeRequest = RequestJustification::whereHas('placementApplication', function($query) {
                $query->where('studentID', Auth::user()->student->studentID);
            })
            ->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved')
            ->exists();
        }

        // Check course verification status separately
        $hasCourseVerification = false;
        if (Auth::user()->student) {
            $hasCourseVerification = CourseVerification::where('studentID', Auth::user()->student->studentID)
                ->where('status', 'approved')
                ->exists();
        }

        return view('livewire.student-placement-application-table', [
            'applications' => $applications,
            'canApply' => $canApply,
            'hasAcceptedApplication' => $hasAcceptedApplication,
            'hasApprovedChangeRequest' => $hasApprovedChangeRequest,
            'hasCourseVerification' => $hasCourseVerification,
        ]);
    }
}
