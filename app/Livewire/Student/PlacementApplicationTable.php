<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\PlacementApplication;
use App\Models\CourseVerification;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\File;
use App\Models\RequestJustification;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\GeocodingService;
use Illuminate\Support\Collection;

class PlacementApplicationTable extends Component
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
    public $industrySupervisorName = '';
    public $industrySupervisorContact = '';
    public $industrySupervisorEmail = '';
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

    // Company status warning properties
    public $showCompanyWarningModal = false;
    public $warningCompanyName = '';
    public $warningCompanyStatus = '';
    public $confirmedCompanyWarning = false;
    public $selectedApplicationForChange = null;

    // Guide visibility
    public $showGuide = false;

    // Company selection
    public $selectedCompanyId = null;
    public $isNewCompany = false;

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
            'companyName' => 'required|string|max:50',
            'companyAddressLine' => 'required|string|max:255',
            'companyCity' => 'required|string|max:20',
            'companyPostcode' => 'required|regex:/^\d{1,10}$/|max:10',
            'companyState' => 'required|string',
            'companyCountry' => 'nullable|string',
            'companyEmail' => 'required|email|max:50',
            'companyNumber' => 'required|regex:/^\d{1,15}$/|max:15',
            'industrySupervisorName' => 'required|string|max:50',
            'industrySupervisorContact' => 'required|regex:/^\d{1,15}$/|max:15',
            'industrySupervisorEmail' => 'required|email|max:50',
            'allowance' => 'required|regex:/^\d{1,5}$/|max:5',
            'position' => 'required|string|max:50',
            'jobscope' => 'required|string',
            'methodOfWork' => 'required|in:WFO,WOS,WOC,WFH,WFO & WFH',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'applicationFiles.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max per file
        ];
    }

    protected function getChangeRequestRules()
    {
        return [
            'changeRequestReason' => 'required|string|min:20|max:1000',
            'changeRequestFiles' => 'required|array|min:1',
            'changeRequestFiles.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];
    }

    protected $messages = [
        'companyName.required' => 'Company name is required.',
        'companyName.max' => 'Company name must not exceed 50 characters.',
        'companyAddressLine.required' => 'Company address is required.',
        'companyAddressLine.max' => 'Company address must not exceed 255 characters.',
        'companyCity.required' => 'City is required.',
        'companyCity.max' => 'City must not exceed 20 characters.',
        'companyPostcode.required' => 'Postcode is required.',
        'companyPostcode.regex' => 'Postcode must contain only numbers (maximum 10 digits).',
        'companyPostcode.max' => 'Postcode must not exceed 10 digits.',
        'companyState.required' => 'State is required.',
        'companyEmail.required' => 'Company email is required.',
        'companyEmail.email' => 'Please provide a valid email address.',
        'companyEmail.max' => 'Company email must not exceed 50 characters.',
        'companyNumber.required' => 'Company phone number is required.',
        'companyNumber.regex' => 'Company phone number must contain only numbers (maximum 15 digits).',
        'companyNumber.max' => 'Company phone number must not exceed 15 digits.',
        'industrySupervisorName.required' => 'Supervisor name is required.',
        'industrySupervisorName.max' => 'Supervisor name must not exceed 50 characters.',
        'industrySupervisorContact.required' => 'Supervisor contact number is required.',
        'industrySupervisorContact.regex' => 'Supervisor phone number must contain only numbers (maximum 15 digits).',
        'industrySupervisorContact.max' => 'Supervisor phone number must not exceed 15 digits.',
        'industrySupervisorEmail.required' => 'Supervisor email is required.',
        'industrySupervisorEmail.email' => 'Please provide a valid email address for the supervisor.',
        'industrySupervisorEmail.max' => 'Supervisor email must not exceed 50 characters.',
        'allowance.required' => 'Monthly allowance is required.',
        'allowance.regex' => 'Monthly allowance must contain only numbers (maximum 5 digits).',
        'allowance.max' => 'Monthly allowance must not exceed 5 digits.',
        'position.required' => 'Position is required.',
        'position.max' => 'Position must not exceed 50 characters.',
        'jobscope.required' => 'Job scope is required.',
        'methodOfWork.required' => 'Method of work is required.',
        'startDate.required' => 'Start date is required.',
        'startDate.after' => 'Start date must be in the future.',
        'endDate.required' => 'End date is required.',
        'endDate.after' => 'End date must be after start date.',
        'applicationFiles.*.file' => 'Each uploaded item must be a file.',
        'applicationFiles.*.mimes' => 'Files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'applicationFiles.*.max' => 'Each file cannot exceed 5MB.',
        'changeRequestReason.required' => 'Please provide a reason for the change request.',
        'changeRequestReason.min' => 'Reason must be at least 20 characters.',
        'changeRequestReason.max' => 'Reason must not exceed 1000 characters.',
        'changeRequestFiles.required' => 'At least one supporting document is required.',
        'changeRequestFiles.min' => 'At least one supporting document is required.',
        'changeRequestFiles.*.required' => 'Each file is required.',
        'changeRequestFiles.*.mimes' => 'Supporting files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'changeRequestFiles.*.max' => 'Each file must be less than 5MB.',
    ];

    public function boot()
    {
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
            'industrySupervisorName',
            'industrySupervisorContact',
            'industrySupervisorEmail',
            'allowance',
            'position',
            'jobscope',
            'methodOfWork',
            'startDate',
            'endDate',
            'applicationFiles',
            'existingFiles',
            'selectedCompanyId',
            'isNewCompany',
            'confirmedCompanyWarning',
            'showCompanyWarningModal',
            'warningCompanyName',
            'warningCompanyStatus'
        ]);

        // Reset coordinates manually
        $this->companyLatitude = null;
        $this->companyLongitude = null;

        $this->editingId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedSelectedCompanyId($value)
    {
        $this->isNewCompany = false;
        $this->confirmedCompanyWarning = false;
        $this->showCompanyWarningModal = false;

        if ($value) {
            // Get company name from existing application
            $application = PlacementApplication::where('applicationID', $value)
                ->select('companyName')
                ->first();
            if ($application) {
                $this->companyName = $application->companyName;
                // Check company status immediately
                $this->checkCompanyStatus($this->companyName);
            }
        } else {
            $this->companyName = '';
        }
    }

    public function updatedCompanyName($value)
    {
        // Reset warning confirmation when company name changes
        $this->confirmedCompanyWarning = false;

        // Check company status when company name is entered (for both new and existing companies)
        // This allows checking if a manually entered company name matches an existing company with problematic status
        if ($value && strlen(trim($value)) >= 2) {
            $this->checkCompanyStatus(trim($value));
        }
    }

    private function checkCompanyStatus($companyName)
    {
        if (empty($companyName)) {
            return;
        }

        // Find company by name (case-insensitive, trim whitespace)
        $companyName = trim($companyName);

        // Try exact match first
        $company = Company::where('companyName', $companyName)->first();

        // If not found, try case-insensitive match
        if (!$company) {
            $company = Company::whereRaw('LOWER(TRIM(companyName)) = LOWER(?)', [$companyName])->first();
        }

        if ($company) {
            $problematicStatuses = ['Blacklisted', 'Closed Down', 'Downsize', 'Illegal'];

            if (in_array($company->status, $problematicStatuses)) {
                $this->warningCompanyName = $company->companyName;
                $this->warningCompanyStatus = $company->status;
                $this->showCompanyWarningModal = true;
                // Force Livewire to update the view
                $this->dispatch('$refresh');
            }
        }
    }

    public function proceedWithCompanyWarning()
    {
        $this->confirmedCompanyWarning = true;
        $this->showCompanyWarningModal = false;
    }

    public function cancelCompanyWarning()
    {
        // Clear the company selection/name
        $this->companyName = '';
        $this->selectedCompanyId = null;
        $this->confirmedCompanyWarning = false;
        $this->showCompanyWarningModal = false;
        $this->warningCompanyName = '';
        $this->warningCompanyStatus = '';
    }

    public function updatedIsNewCompany($value)
    {
        if ($value) {
            $this->selectedCompanyId = null;
            $this->companyName = '';
        }
    }

    public function updatedAllowance($value)
    {

        if ($value) {
            // Remove any non-numeric characters except digits
            $value = preg_replace('/[^0-9]/', '', $value);
            // Convert to integer and back to string to remove any decimals
            $this->allowance = $value ? (string)(int)$value : '';
        } else {
            $this->allowance = '';
        }
    }

    public function getExistingCompanies()
    {
        // Get unique company names from all placement applications
        $companies = PlacementApplication::select('companyName', 'applicationID')
            ->whereNotNull('companyName')
            ->where('companyName', '!=', '')
            ->orderBy('companyName', 'asc')
            ->get()
            ->unique('companyName')
            ->map(function ($app) {
                return [
                    'id' => $app->applicationID,
                    'name' => $app->companyName
                ];
            })
            ->values();

        return $companies;
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

        // Check if student has accepted any application - if so, they cannot edit any remaining applications
        $hasAcceptedApplication = PlacementApplication::where('studentID', Auth::user()->student->studentID)
            ->where('studentAcceptance', 'Accepted')
            ->exists();

        if ($hasAcceptedApplication) {
            session()->flash('error', 'You cannot edit any applications after accepting one. You can only view them.');
            return;
        }

        // Only allow editing if application is not accepted and BOTH statuses are still pending
        if ($application->studentAcceptance === 'Accepted') {
            session()->flash('error', 'You cannot edit an application that has been accepted.');
            return;
        }

        // Prevent editing if either committee or coordinator has approved
        if ($application->committeeStatus !== 'Pending' || $application->coordinatorStatus !== 'Pending') {
            session()->flash('error', 'You cannot edit an application once it has been reviewed by the committee or coordinator.');
            return;
        }

        $this->editingId = $id;
        $this->companyName = $application->companyName;

        // Try to find if this company name exists in the dropdown
        $existingCompanies = $this->getExistingCompanies();
        $existingCompany = $existingCompanies->firstWhere('name', $application->companyName);
        if ($existingCompany) {
            $this->selectedCompanyId = $existingCompany['id'];
            $this->isNewCompany = false;
        } else {
            $this->selectedCompanyId = 'new';
            $this->isNewCompany = true;
        }

        $this->companyAddressLine = $application->companyAddressLine ?? '';
        $this->companyCity = $application->companyCity ?? '';
        $this->companyPostcode = $application->companyPostcode ?? '';
        $this->companyState = $application->companyState ?? '';
        $this->companyCountry = $application->companyCountry ?? '';
        $this->companyLatitude = $application->companyLatitude ?? '';
        $this->companyLongitude = $application->companyLongitude ?? '';
        $this->companyEmail = $application->companyEmail;
        $this->companyNumber = $application->companyNumber;
        $this->industrySupervisorName = $application->industrySupervisorName ?? '';
        $this->industrySupervisorContact = $application->industrySupervisorContact ?? '';
        $this->industrySupervisorEmail = $application->industrySupervisorEmail ?? '';
        // Remove decimal formatting from allowance (e.g., 100.00 becomes 100)
        $this->allowance = $application->allowance ? (string)(int)$application->allowance : '';
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
        // Check if company has problematic status and warning hasn't been confirmed
        if (!empty($this->companyName) && !$this->confirmedCompanyWarning) {
            // Find company by name (case-insensitive, trim whitespace)
            $companyName = trim($this->companyName);

            // Try exact match first
            $company = Company::where('companyName', $companyName)->first();

            // If not found, try case-insensitive match
            if (!$company) {
                $company = Company::whereRaw('LOWER(TRIM(companyName)) = LOWER(?)', [$companyName])->first();
            }

            if ($company) {
                $problematicStatuses = ['Blacklisted', 'Closed Down', 'Downsize', 'Illegal'];
                if (in_array($company->status, $problematicStatuses)) {
                    $this->warningCompanyName = $company->companyName;
                    $this->warningCompanyStatus = $company->status;
                    $this->showCompanyWarningModal = true;
                    // Force Livewire to update the view
                    $this->dispatch('$refresh');
                    return;
                }
            }
        }

        // Validate file sizes first - this must pass before any other validation
        try {
            $this->validateFileSizes();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw immediately to prevent any further processing
            throw $e;
        }

        try {
            // Validate all rules
            $this->validate($this->getPlacementApplicationRules());

            // Debug logging
            \Log::info('Submit method called', [
                'user_id' => Auth::id(),
                'company_name' => $this->companyName,
                'editing_id' => $this->editingId,
                'file_count' => count($this->applicationFiles ?? []),
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
                    'industrySupervisorName' => $this->industrySupervisorName,
                    'industrySupervisorContact' => $this->industrySupervisorContact,
                    'industrySupervisorEmail' => $this->industrySupervisorEmail,
                    'position' => $this->position,
                    'startDate' => $this->startDate,
                    'endDate' => $this->endDate
                ]
            ]);

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
                'industrySupervisorName' => $this->industrySupervisorName,
                'industrySupervisorContact' => $this->industrySupervisorContact,
                'industrySupervisorEmail' => $this->industrySupervisorEmail,
                'allowance' => $this->allowance ? (int)$this->allowance : null,
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

                // Handle file updates - files are already validated at this point
                if (!empty($this->applicationFiles)) {
                    // Delete old files
                    $application->files()->delete();

                    // Upload new files (validation already passed)
                    $this->uploadFiles($application);
                }

                session()->flash('message', 'Placement application updated successfully!');
            } else {
                // Double-check file sizes before creating application (safety check)
                // This should never trigger if validateFileSizes() worked correctly
                if (!empty($this->applicationFiles)) {
                    $maxSizeBytes = 5120 * 1024; // 5MB in bytes
                    foreach ($this->applicationFiles as $file) {
                        if (!$file) {
                            continue;
                        }
                        $fileSizeBytes = $file->getSize();
                        if ($fileSizeBytes > $maxSizeBytes) {
                            $fileSizeMB = round($fileSizeBytes / 1024 / 1024, 2);
                            $fileName = $file->getClientOriginalName();
                            \Log::error('File size validation failed in safety check', [
                                'file_name' => $fileName,
                                'file_size_mb' => $fileSizeMB
                            ]);
                            throw new \Exception("File '{$fileName}' ({$fileSizeMB}MB) exceeds the maximum allowed size of 5MB. Please fix the file before submitting.");
                        }
                    }
                }

                // Create new application - only if all validations passed
                // Get current apply count for this student
                $lastApplication = PlacementApplication::where('studentID', $student->studentID)
                    ->orderBy('applyCount', 'desc')
                    ->first();

                $applicationData['applyCount'] = $lastApplication ? $lastApplication->applyCount + 1 : 1;

                \Log::info('Creating new application with data', $applicationData);

                // Final file validation check before creating application
                if (!empty($this->applicationFiles)) {
                    $maxSizeBytes = 5120 * 1024; // 5MB in bytes
                    foreach ($this->applicationFiles as $file) {
                        if (!$file) {
                            continue;
                        }
                        $fileSizeBytes = $file->getSize();
                        if ($fileSizeBytes > $maxSizeBytes) {
                            $fileSizeMB = round($fileSizeBytes / 1024 / 1024, 2);
                            $fileName = $file->getClientOriginalName();
                            \Log::error('CRITICAL: File validation failed right before application creation', [
                                'file_name' => $fileName,
                                'file_size_mb' => $fileSizeMB
                            ]);
                            throw new \Exception("Cannot create application: File '{$fileName}' ({$fileSizeMB}MB) exceeds the maximum allowed size of 5MB.");
                        }
                    }
                }

                $application = PlacementApplication::create($applicationData);
                \Log::info('Application created successfully', ['application_id' => $application->applicationID]);

                // Upload files - only if application was created successfully
                if (!empty($this->applicationFiles)) {
                    \Log::info('Uploading files', ['file_count' => count($this->applicationFiles)]);
                    $this->uploadFiles($application);
                }

                session()->flash('message', 'Placement application submitted successfully!');
            }

            $this->closeForm();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so Livewire can handle them properly
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Submit method exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'An error occurred while processing your application: ' . $e->getMessage());
        }
    }

    protected function validateFileSizes()
    {
        if (empty($this->applicationFiles)) {
            return; // No files to validate
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

            \Log::info('Validating file', [
                'file_name' => $fileName,
                'file_size_bytes' => $fileSizeBytes,
                'file_size_kb' => $fileSizeKB,
                'file_size_mb' => $fileSizeMB,
                'max_size_kb' => $maxSizeKB,
                'max_size_bytes' => $maxSizeBytes
            ]);

            if ($fileSizeBytes > $maxSizeBytes) {
                $errors[] = "File '{$fileName}' is {$fileSizeMB}MB, which exceeds the maximum allowed size of 5MB.";
            }
        }

        if (!empty($errors)) {
            \Log::error('File validation failed', [
                'errors' => $errors,
                'file_count' => count($this->applicationFiles)
            ]);

            // Throw validation exception to prevent form submission
            throw \Illuminate\Validation\ValidationException::withMessages([
                'applicationFiles' => $errors
            ]);
        }

        \Log::info('All files validated successfully', [
            'file_count' => count($this->applicationFiles)
        ]);
    }

    private function uploadFiles($application)
    {
        foreach ($this->applicationFiles as $file) {
            // Double-check file size before uploading
            $fileSizeKB = $file->getSize() / 1024;
            if ($fileSizeKB > 5120) {
                \Log::error('File size validation failed during upload', [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size_kb' => $fileSizeKB,
                    'max_size_kb' => 5120
                ]);
                throw new \Exception("File '{$file->getClientOriginalName()}' exceeds the maximum allowed size of 5MB.");
            }

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

        // Check if student already has an accepted application (excluding this one)
        $existingAccepted = PlacementApplication::where('studentID', Auth::user()->student->studentID)
            ->where('studentAcceptance', 'Accepted')
            ->where('applicationID', '!=', $application->applicationID)
            ->exists();

        if ($existingAccepted) {
            // Check if this is a new application after an approved change request
            $approvedChangeRequest = RequestJustification::whereHas('placementApplication', function ($query) use ($application) {
                $query->where('studentID', $application->studentID)
                    ->where('studentAcceptance', 'Accepted');
            })
                ->where('committeeStatus', 'Approved')
                ->where('coordinatorStatus', 'Approved')
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($approvedChangeRequest && $application->created_at > $approvedChangeRequest->updated_at) {
                // This is a new application after approved change request, allow acceptance
                // Update old application to 'Changed' status
                PlacementApplication::where('studentID', $application->studentID)
                    ->where('studentAcceptance', 'Accepted')
                    ->where('applicationID', '!=', $application->applicationID)
                    ->update(['studentAcceptance' => 'Changed']);
            } else {
                session()->flash('error', 'You can only accept one internship placement application.');
                return;
            }
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

        // Check if student status is Active (only active students can apply)
        if ($student->status !== 'Active') {
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
        // Only allow more applications if they have NOT accepted any application
        $acceptedApplication = PlacementApplication::where('studentID', $student->studentID)
            ->where('studentAcceptance', 'Accepted')
            ->first();

        // If student has NO accepted application, they can apply
        if (!$acceptedApplication) {
            return true;
        }

        // Student has an accepted application - they cannot make more applications
        return false;
    }

    private function setCannotApplyErrorMessage()
    {
        $student = Auth::user()->student;

        if (!$student) {
            session()->flash('error', 'Student profile not found.');
            return;
        }

        // Check student status first (only active students can apply)
        if ($student->status !== 'Active') {
            $statusMessage = match($student->status) {
                'Deferred' => 'You cannot make internship applications while your status is Deferred. Please contact the administration once your defer period ends.',
                'Graduated' => 'You cannot make internship applications as your status is Graduated.',
                default => 'You cannot make internship applications with your current status (' . $student->status . '). Only active students can apply.',
            };
            session()->flash('error', $statusMessage);
            return;
        }

        // Check course verification
        $approvedVerification = CourseVerification::where('studentID', $student->studentID)
            ->where('status', 'approved')
            ->exists();

        if (!$approvedVerification) {
            session()->flash('error', 'You must have an approved course verification before applying for internship placement.');
            return;
        }

        // Check if student has accepted application
        $acceptedApplication = PlacementApplication::where('studentID', $student->studentID)
            ->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved')
            ->where('studentAcceptance', 'Accepted')
            ->first();

        if ($acceptedApplication) {
            // Check if they have an approved change request
            $approvedChangeRequest = RequestJustification::whereHas('placementApplication', function ($query) use ($student) {
                $query->where('studentID', $student->studentID);
            })
                ->where('committeeStatus', 'Approved')
                ->where('coordinatorStatus', 'Approved')
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$approvedChangeRequest) {
                session()->flash('error', 'You already have an accepted placement application. You can only submit a new application if you have an approved change request.');
                return;
            }

            // Check if the accepted application was created after the change request was approved
            $acceptedApplicationIsNew = $acceptedApplication->created_at > $approvedChangeRequest->updated_at;

            if ($acceptedApplicationIsNew) {
                session()->flash('error', 'You already have an accepted placement application. You cannot submit additional applications after accepting a new placement following a change request.');
                return;
            }

            // They have approved change request and the accepted application is the old one
            // This shouldn't happen if logic is correct, but just in case
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
        $student = Auth::user()->student;

        if (!$student) {
            session()->flash('error', 'Student profile not found.');
            return;
        }

        // Check if student status is Active (only active students can make change requests)
        if ($student->status !== 'Active') {
            $statusMessage = match($student->status) {
                'Deferred' => 'You cannot make change requests while your status is Deferred. Please contact the administration once your defer period ends.',
                'Graduated' => 'You cannot make change requests as your status is Graduated.',
                default => 'You cannot make change requests with your current status (' . $student->status . '). Only active students can make change requests.',
            };
            session()->flash('error', $statusMessage);
            return;
        }

        $application = PlacementApplication::with(['student', 'changeRequests'])
            ->findOrFail($applicationID);

        // Check if this application belongs to the current student
        if ($application->studentID !== $student->studentID) {
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
        // Validate file sizes first before any other validation
        try {
            $this->validateChangeRequestFileSizes();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw immediately to prevent any further processing
            throw $e;
        }

        $this->validate($this->getChangeRequestRules());

        try {
            $student = Auth::user()->student;

            if (!$student) {
                session()->flash('error', 'Student profile not found.');
                return;
            }

            // Check if student status is Active (only active students can make change requests)
            if ($student->status !== 'Active') {
                $statusMessage = match($student->status) {
                    'Deferred' => 'You cannot make change requests while your status is Deferred. Please contact the administration once your defer period ends.',
                    'Graduated' => 'You cannot make change requests as your status is Graduated.',
                    default => 'You cannot make change requests with your current status (' . $student->status . '). Only active students can make change requests.',
                };
                session()->flash('error', $statusMessage);
                return;
            }

            // Create the change request
            $changeRequest = RequestJustification::create([
                'applicationID' => $this->changeRequestApplicationID,
                'reason' => $this->changeRequestReason,
                'requestDate' => now()->format('Y-m-d'),
            ]);

            // Refresh to ensure we have the ID
            $changeRequest->refresh();

            // Handle file uploads
            if (!empty($this->changeRequestFiles)) {
                \Log::info('Starting file upload for change request', [
                    'change_request_id' => $changeRequest->justificationID,
                    'change_request_id_type' => gettype($changeRequest->justificationID),
                    'files_to_upload' => count($this->changeRequestFiles)
                ]);
                $this->uploadChangeRequestFiles($changeRequest);

                // Verify files were saved
                $savedFiles = $changeRequest->files()->get();
                \Log::info('Files saved verification', [
                    'change_request_id' => $changeRequest->justificationID,
                    'saved_files_count' => $savedFiles->count(),
                    'file_details' => $savedFiles->map(function ($file) {
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

    protected function validateChangeRequestFileSizes()
    {
        if (empty($this->changeRequestFiles)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'changeRequestFiles' => ['At least one supporting document is required.']
            ]);
        }

        $maxSizeKB = 5120; // 5MB in kilobytes
        $maxSizeBytes = $maxSizeKB * 1024; // Convert to bytes
        $errors = [];

        // Check each file individually
        foreach ($this->changeRequestFiles as $index => $file) {
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
                'changeRequestFiles' => $errors
            ]);
        }
    }

    private function uploadChangeRequestFiles($changeRequest)
    {
        \Log::info('Uploading change request files', [
            'request_id' => $changeRequest->justificationID,
            'file_count' => count($this->changeRequestFiles)
        ]);

        foreach ($this->changeRequestFiles as $file) {
            // Double-check file size before uploading
            $fileSizeKB = $file->getSize() / 1024;
            if ($fileSizeKB > 5120) {
                throw new \Exception("File '{$file->getClientOriginalName()}' exceeds the maximum allowed size of 5MB.");
            }

            try {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('change_requests', $filename, 'public');

                if (!$path) {
                    \Log::error('File storage failed', [
                        'filename' => $file->getClientOriginalName(),
                        'request_id' => $changeRequest->justificationID
                    ]);
                    continue;
                }

                $fileRecord = File::create([
                    'fileable_id' => $changeRequest->justificationID,
                    'fileable_type' => RequestJustification::class,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);

                \Log::info('File created successfully', [
                    'file_id' => $fileRecord->id,
                    'fileable_id' => $changeRequest->justificationID,
                    'fileable_type' => RequestJustification::class,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to upload file for change request', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'request_id' => $changeRequest->justificationID,
                    'filename' => $file->getClientOriginalName()
                ]);
                throw $e; // Re-throw to be caught by parent try-catch
            }
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
            'change_requests_with_files' => $application->changeRequests->map(function ($cr) {
                return [
                    'id' => $cr->justificationID,
                    'files_count' => $cr->files->count(),
                    'files' => $cr->files->map(function ($file) {
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

    public function getAnalyticsData()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return null;
        }

        $studentID = $student->studentID;

        $analytics = [
            'total_applications' => PlacementApplication::where('studentID', $studentID)->count(),
            'pending_applications' => PlacementApplication::where('studentID', $studentID)
                ->where(function ($q) {
                    $q->where('committeeStatus', 'Pending')
                        ->orWhere('coordinatorStatus', 'Pending');
                })->count(),
            'approved_applications' => PlacementApplication::where('studentID', $studentID)
                ->where('committeeStatus', 'Approved')
                ->where('coordinatorStatus', 'Approved')
                ->count(),
            'rejected_applications' => PlacementApplication::where('studentID', $studentID)
                ->where(function ($q) {
                    $q->where('committeeStatus', 'Rejected')
                        ->orWhere('coordinatorStatus', 'Rejected');
                })->count(),
            'accepted_applications' => PlacementApplication::where('studentID', $studentID)
                ->where('studentAcceptance', 'Accepted')
                ->count(),
            'declined_applications' => PlacementApplication::where('studentID', $studentID)
                ->where('studentAcceptance', 'Declined')
                ->count(),
            'applications_this_month' => PlacementApplication::where('studentID', $studentID)
                ->whereMonth('applicationDate', now()->month)
                ->whereYear('applicationDate', now()->year)
                ->count(),
        ];

        return $analytics;
    }

    public function render()
    {
        $applications = $this->getFilteredApplications()->paginate($this->perPage);
        $canApply = $this->canStudentApply();
        $analytics = $this->getAnalyticsData();

        // Check if student is active (for change requests)
        $student = Auth::user()->student;
        $isStudentActive = $student && $student->status === 'Active';

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
            $hasApprovedChangeRequest = RequestJustification::whereHas('placementApplication', function ($query) {
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

        $existingCompanies = $this->getExistingCompanies();

        return view('livewire.student.placementApplicationTable', [
            'applications' => $applications,
            'canApply' => $canApply,
            'isStudentActive' => $isStudentActive,
            'hasAcceptedApplication' => $hasAcceptedApplication,
            'hasApprovedChangeRequest' => $hasApprovedChangeRequest,
            'hasCourseVerification' => $hasCourseVerification,
            'analytics' => $analytics,
            'existingCompanies' => $existingCompanies,
        ]);
    }
}
