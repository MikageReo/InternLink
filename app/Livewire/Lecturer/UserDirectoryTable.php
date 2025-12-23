<?php

namespace App\Livewire\Lecturer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Notifications\UserRegistrationNotification;
use App\Services\GeocodingService;
// Removed Excel import temporarily due to missing GD extension
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\UsersExport;

class UserDirectoryTable extends Component
{
    use WithPagination, WithFileUploads;

    private GeocodingService $geocodingService;

    /**
     * Convert program code to full name for database storage
     * Handles both codes (BCS, BCN, etc.) and full names
     */
    private function convertProgramToFullName($program): ?string
    {
        if (empty($program)) {
            return null;
        }

        $programs = [
            'BCS' => 'Bachelor of Computer Science (Software Engineering) with Honours',
            'BCN' => 'Bachelor of Computer Science (Computer Systems & Networking) with Honours',
            'BCM' => 'Bachelor of Computer Science (Multimedia Software) with Honours',
            'BCY' => 'Bachelor of Computer Science (Cyber Security) with Honours',
            'DRC' => 'Diploma in Computer Science',
        ];

        // If already a full name, return as is
        if (in_array($program, $programs)) {
            return $program;
        }

        // Convert code to full name
        return $programs[$program] ?? $program; // Return original if not a known code (might be a full name from CSV)
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

    /**
     * Generate filter information text for exports
     */
    private function getFilterInfo(): array
    {
        $filters = [];

        if ($this->role) {
            $filters['Role'] = ucfirst($this->role);
        }

        if ($this->semester) {
            $filters['Semester'] = 'Semester ' . $this->semester;
        }

        if ($this->year) {
            $filters['Year'] = $this->year;
        }

        if ($this->program) {
            $programFullName = $this->getProgramFullName($this->program);
            $filters['Program'] = $programFullName ?: $this->program;
        }

        if ($this->search) {
            $filters['Search'] = $this->search;
        }

        return $filters;
    }

    /**
     * Find academic advisor by ID, email, or name with ambiguity detection
     * Returns array with 'success', 'advisorID' or 'error'
     */
    private function findAcademicAdvisor($value, $rowNumber = null): array
    {
        if (empty($value)) {
            return ['success' => true, 'advisorID' => null];
        }

        $advisorValue = trim($value);
        $rowPrefix = $rowNumber ? "Row {$rowNumber}: " : '';

        // Priority 1: Check if it's a lecturer ID (short alphanumeric code like LEC001)
        if (preg_match('/^[A-Z0-9]{3,10}$/i', $advisorValue)) {
            $advisor = Lecturer::with('user')
                ->where('lecturerID', $advisorValue)
                ->where('isAcademicAdvisor', true)
                ->where('status', 'active')
                ->first();

            if ($advisor) {
                return ['success' => true, 'advisorID' => $advisor->lecturerID];
            } else {
                return [
                    'success' => false,
                    'error' => "{$rowPrefix}Academic advisor ID '{$advisorValue}' not found or not an active academic advisor."
                ];
            }
        }

        // Priority 2: Check if it's an email address
        if (filter_var($advisorValue, FILTER_VALIDATE_EMAIL)) {
            $advisor = Lecturer::with('user')
                ->whereHas('user', function ($query) use ($advisorValue) {
                    $query->where('email', $advisorValue);
                })
                ->where('isAcademicAdvisor', true)
                ->where('status', 'active')
                ->first();

            if ($advisor) {
                return ['success' => true, 'advisorID' => $advisor->lecturerID];
            } else {
                return [
                    'success' => false,
                    'error' => "{$rowPrefix}Academic advisor email '{$advisorValue}' not found or not an active academic advisor."
                ];
            }
        }

        // Priority 3: Try to match by name
        // First try exact match
        $exactMatches = Lecturer::with('user')
            ->whereHas('user', function ($query) use ($advisorValue) {
                $query->where('name', $advisorValue);
            })
            ->where('isAcademicAdvisor', true)
            ->where('status', 'active')
            ->get();

        if ($exactMatches->count() === 1) {
            // Perfect! Only one exact match
            return ['success' => true, 'advisorID' => $exactMatches->first()->lecturerID];
        } elseif ($exactMatches->count() > 1) {
            // Multiple exact matches - ambiguous
            $suggestions = $exactMatches->take(3)->map(function ($advisor) {
                return $advisor->user->name . ' (' . $advisor->lecturerID . ')';
            })->join(', ');

            return [
                'success' => false,
                'error' => "{$rowPrefix}Multiple academic advisors found with name '{$advisorValue}'. Please use email or ID. Found: {$suggestions}"
            ];
        }

        // No exact match, try partial match
        $partialMatches = Lecturer::with('user')
            ->whereHas('user', function ($query) use ($advisorValue) {
                $query->where('name', 'like', '%' . $advisorValue . '%');
            })
            ->where('isAcademicAdvisor', true)
            ->where('status', 'active')
            ->get();

        if ($partialMatches->count() === 1) {
            // Only one partial match - good enough
            return ['success' => true, 'advisorID' => $partialMatches->first()->lecturerID];
        } elseif ($partialMatches->count() > 1) {
            // Multiple partial matches - ambiguous
            $suggestions = $partialMatches->take(5)->map(function ($advisor) {
                return $advisor->user->name . ' (' . $advisor->user->email . ' / ' . $advisor->lecturerID . ')';
            })->join(', ');

            return [
                'success' => false,
                'error' => "{$rowPrefix}Multiple academic advisors found matching '{$advisorValue}'. Please use full name, email, or ID. Found: {$suggestions}"
            ];
        }

        // No match at all
        return [
            'success' => false,
            'error' => "{$rowPrefix}Academic advisor '{$advisorValue}' not found. Please use lecturer ID (e.g., LEC001), email, or full name."
        ];
    }

    // Filter properties
    public $role = '';
    public $semester = '';
    public $year = '';
    public $program = '';

    // Search and sort properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $exportFormat = 'csv';

    // UI properties
    public $showFilters = true;
    public $showBulkRegistration = false;
    public $showStudentRegistration = false;
    public $showLecturerRegistration = false;

    // CSV Upload properties
    public $csvFile;
    public $bulkSemester = '';
    public $bulkYear = '';

    // Individual Student Registration properties
    public $studentName = '';
    public $studentEmail = '';
    public $studentID = '';
    public $studentPhone = '';
    public $studentAddress = '';
    public $studentCity = '';
    public $studentPostcode = '';
    public $studentState = '';
    public $studentCountry = '';
    public $studentLatitude = '';
    public $studentLongitude = '';
    public $studentNationality = '';
    public $studentProgram = '';
    public $studentSemester = '';
    public $studentYear = '';
    public $studentAcademicAdvisorID = '';

    // Individual Lecturer Registration properties
    public $lecturerName = '';
    public $lecturerEmail = '';
    public $lecturerID = '';
    public $lecturerStaffGrade = '';
    public $lecturerRole = '';
    public $lecturerPosition = '';
    public $lecturerAddress = '';
    public $lecturerCity = '';
    public $lecturerPostcode = '';
    public $lecturerState = '';
    public $lecturerCountry = '';
    public $lecturerLatitude = '';
    public $lecturerLongitude = '';
    public $lecturerResearchGroup = '';
    public $lecturerDepartment = '';
    public $lecturerProgram = '';
    public $lecturerSemester = '';
    public $lecturerYear = '';
    public $lecturerSupervisorQuota = 0;
    public $lecturerIsAcademicAdvisor = false;
    public $lecturerIsSupervisorFaculty = false;
    public $lecturerIsCommittee = false;
    public $lecturerIsCoordinator = false;
    public $lecturerIsAdmin = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'role' => ['except' => ''],
        'semester' => ['except' => ''],
        'year' => ['except' => ''],
        'program' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function boot(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    public function mount()
    {
        $this->year = date('Y');
        $this->bulkYear = date('Y');
        $this->studentYear = date('Y');
        $this->lecturerYear = date('Y');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingProgram()
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

    public function clearFilters()
    {
        $this->reset(['search', 'role', 'semester', 'year', 'program', 'sortField', 'sortDirection']);
        $this->year = date('Y');
        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function exportData()
    {
        // Get all filtered users (not paginated)
        $users = $this->getFilteredUsers()->get();

        if ($users->isEmpty()) {
            session()->flash('message', 'No data to export. Please apply filters to select users.');
            return;
        }

        // Create descriptive filename
        $roleText = $this->role ?: 'all';
        $semesterText = $this->semester ? '_sem' . $this->semester : '';
        $yearText = $this->year ? '_' . $this->year : '';
        $searchText = $this->search ? '_filtered' : '';

        $filename = 'users_' . $roleText . $semesterText . $yearText . $searchText . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Route to appropriate export method based on format
        switch ($this->exportFormat) {
            case 'csv':
                return $this->exportCSV($users);
            case 'pdf':
                return $this->exportPDF($users);
            case 'word':
                return $this->exportWord($users);
            default:
                return $this->exportCSV($users);
        }
    }

    private function exportCSV($users)
    {
        // Create descriptive filename
        $roleText = $this->role ?: 'all';
        $semesterText = $this->semester ? '_sem' . $this->semester : '';
        $yearText = $this->year ? '_' . $this->year : '';
        $searchText = $this->search ? '_filtered' : '';

        $filename = 'users_' . $roleText . $semesterText . $yearText . $searchText . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Generate CSV content
        $csvData = $this->generateCSV($users);

        // Flash success message
        session()->flash('message', 'CSV file exported successfully! Downloaded ' . $users->count() . ' records.');

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportPDF($users)
    {
        // Create descriptive filename
        $roleText = $this->role ?: 'all';
        $semesterText = $this->semester ? '_sem' . $this->semester : '';
        $yearText = $this->year ? '_' . $this->year : '';
        $searchText = $this->search ? '_filtered' : '';

        $filename = 'users_' . $roleText . $semesterText . $yearText . $searchText . '_' . now()->format('Y-m-d_H-i-s') . '.html';

        // Generate HTML content for PDF (browser will handle PDF conversion)
        $htmlData = $this->generateHTML($users);

        // Flash success message
        session()->flash('message', 'PDF file exported successfully! Downloaded ' . $users->count() . ' records.');

        return response()->streamDownload(function () use ($htmlData) {
            echo $htmlData;
        }, $filename, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportWord($users)
    {
        // Create descriptive filename
        $roleText = $this->role ?: 'all';
        $semesterText = $this->semester ? '_sem' . $this->semester : '';
        $yearText = $this->year ? '_' . $this->year : '';
        $searchText = $this->search ? '_filtered' : '';

        $filename = 'users_' . $roleText . $semesterText . $yearText . $searchText . '_' . now()->format('Y-m-d_H-i-s') . '.doc';

        // Generate Word-compatible HTML content
        $wordData = $this->generateWordHTML($users);

        // Flash success message
        session()->flash('message', 'Word document exported successfully! Downloaded ' . $users->count() . ' records.');

        return response()->streamDownload(function () use ($wordData) {
            echo $wordData;
        }, $filename, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function generateCSV($users)
    {
        $output = '';

        // Add filter information at the top
        $filters = $this->getFilterInfo();
        if (!empty($filters)) {
            $output .= '"Applied Filters:"' . "\n";
            foreach ($filters as $key => $value) {
                $output .= '"' . $key . ': ' . $value . '"' . "\n";
            }
            $output .= "\n"; // Empty line before data
        }

        if ($this->role === 'student') {
            // Student headers
            $headers = [
                'Student ID',
                'Email',
                'Name',
                'Program',
                'Academic Advisor',
                'Phone',
                'Status',
                'Address',
                'Nationality',
                'Semester',
                'Year'
            ];

            $output .= '"' . implode('","', $headers) . '"' . "\n";

            foreach ($users as $user) {
                $academicAdvisorName = 'Not Assigned';
                if ($user->student->academicAdvisorID) {
                    $academicAdvisorName = $user->student->academicAdvisor->user->name ?? $user->student->academicAdvisorID;
                }

                $row = [
                    $user->student->studentID ?? 'N/A',
                    $user->email,
                    $user->name,
                    $user->student->program ?? 'N/A',
                    $academicAdvisorName,
                    $user->student->phone ?? 'N/A',
                    $user->student->status ?? 'N/A',
                    $user->student->address ?? 'N/A',
                    $user->student->nationality ?? 'N/A',
                    $user->student->semester ?? 'N/A',
                    $user->student->year ?? 'N/A'
                ];

                // Escape quotes and wrap in quotes
                $row = array_map(function ($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row);

                $output .= implode(',', $row) . "\n";
            }
        } else if ($this->role === 'lecturer') {
            // Lecturer headers
            $headers = [
                'Lecturer ID',
                'Email',
                'Name',
                'Staff Grade',
                'Role',
                'Position',
                'State',
                'Research Group',
                'Department',
                'Student Quota',
                'Special Roles',
                'Semester',
                'Year'
            ];

            $output .= '"' . implode('","', $headers) . '"' . "\n";

            foreach ($users as $user) {
                $specialRoles = [];
                if ($user->lecturer->isAcademicAdvisor) $specialRoles[] = 'Academic Advisor';
                if ($user->lecturer->isSupervisorFaculty) $specialRoles[] = 'Supervisor Faculty';
                if ($user->lecturer->isCommittee) $specialRoles[] = 'Committee';
                if ($user->lecturer->isCoordinator) $specialRoles[] = 'Coordinator';
                if ($user->lecturer->isAdmin) $specialRoles[] = 'Admin';

                $row = [
                    $user->lecturer->lecturerID ?? 'N/A',
                    $user->email,
                    $user->name,
                    $user->lecturer->staffGrade ?? 'N/A',
                    $user->lecturer->role ?? 'N/A',
                    $user->lecturer->position ?? 'N/A',
                    $user->lecturer->state ?? 'N/A',
                    $user->lecturer->researchGroup ?? 'N/A',
                    $user->lecturer->department ?? 'N/A',
                    $user->lecturer->supervisor_quota ?? 'N/A',
                    implode(', ', $specialRoles) ?: 'None',
                    $user->lecturer->semester ?? 'N/A',
                    $user->lecturer->year ?? 'N/A'
                ];

                // Escape quotes and wrap in quotes
                $row = array_map(function ($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row);

                $output .= implode(',', $row) . "\n";
            }
        }

        return $output;
    }

    private function getFilteredUsers()
    {
        $query = User::query();

        // Apply role filter
        if ($this->role) {
            $query->where('users.role', $this->role);

            if ($this->role === 'student') {
                $query->with(['student.academicAdvisor.user']);

                if ($this->semester || $this->year || $this->program) {
                    $query->whereHas('student', function ($q) {
                        if ($this->semester) {
                            $q->where('semester', $this->semester);
                        }
                        if ($this->year) {
                            $q->where('year', $this->year);
                        }
                        if ($this->program) {
                            // Convert program code to full name for filtering
                            $programFullName = $this->getProgramFullName($this->program);
                            if ($programFullName) {
                                $q->where('program', $programFullName);
                            }
                        }
                    });
                }
            } else {
                $query->with(['lecturer']);

                if ($this->semester || $this->year || $this->program) {
                    $query->whereHas('lecturer', function ($q) {
                        if ($this->semester) {
                            $q->where('semester', $this->semester);
                        }
                        if ($this->year) {
                            $q->where('year', $this->year);
                        }
                        if ($this->program) {
                            // Convert program code to full name for filtering
                            $programFullName = $this->getProgramFullName($this->program);
                            if ($programFullName) {
                                $q->where('program', $programFullName);
                            }
                        }
                    });
                }
            }
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');

                if ($this->role === 'student') {
                    $q->orWhereHas('student', function ($subQ) {
                        $subQ->where('studentID', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%')
                            ->orWhere('address', 'like', '%' . $this->search . '%')
                            ->orWhere('nationality', 'like', '%' . $this->search . '%')
                            ->orWhere('program', 'like', '%' . $this->search . '%');
                    });
                } elseif ($this->role === 'lecturer') {
                    $q->orWhereHas('lecturer', function ($subQ) {
                        $subQ->where('lecturerID', 'like', '%' . $this->search . '%')
                            ->orWhere('staffGrade', 'like', '%' . $this->search . '%')
                            ->orWhere('position', 'like', '%' . $this->search . '%')
                            ->orWhere('department', 'like', '%' . $this->search . '%')
                            ->orWhere('researchGroup', 'like', '%' . $this->search . '%')
                            ->orWhere('program', 'like', '%' . $this->search . '%');
                    });
                }
            });
        }

        // Apply sorting
        if ($this->sortField) {
            if (in_array($this->sortField, ['name', 'email', 'created_at'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            } elseif ($this->role === 'student' && in_array($this->sortField, ['studentID', 'phone', 'program', 'status'])) {
                $query->join('students', 'users.id', '=', 'students.user_id')
                    ->orderBy('students.' . $this->sortField, $this->sortDirection)
                    ->select('users.*');
            } elseif ($this->role === 'lecturer' && in_array($this->sortField, ['lecturerID', 'supervisor_quota'])) {
                $query->join('lecturers', 'users.id', '=', 'lecturers.user_id')
                    ->orderBy('lecturers.' . $this->sortField, $this->sortDirection)
                    ->select('users.*');
            }
        }

        return $query;
    }

    public function render()
    {
        $users = $this->getFilteredUsers()->paginate($this->perPage);

        $totalCount = $this->getFilteredUsers()->count();

        // Get total counts for statistics
        $totalStudents = Student::count();
        $totalLecturers = Lecturer::count();

        return view('livewire.lecturer.userDirectoryTable', [
            'users' => $users,
            'totalCount' => $totalCount,
            'totalStudents' => $totalStudents,
            'totalLecturers' => $totalLecturers,
        ]);
    }

    private function generateHTML($users)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Directory Export</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .print-only { page-break-before: always; }
    </style>
</head>
<body>
    <h1>User Directory - ' . ucfirst($this->role ?: 'All') . ' Users</h1>
    <p><strong>Export Date:</strong> ' . now()->format('F d, Y H:i:s') . '</p>
    <p><strong>Total Records:</strong> ' . $users->count() . '</p>';

        // Add filter information
        $filters = $this->getFilterInfo();
        if (!empty($filters)) {
            $html .= '<div style="margin: 20px 0; padding: 15px; background-color: #f0f0f0; border-left: 4px solid #4CAF50;">
                <h2 style="margin-top: 0; color: #333;">Applied Filters:</h2>
                <ul style="margin: 10px 0; padding-left: 20px;">';
            foreach ($filters as $key => $value) {
                $html .= '<li><strong>' . $key . ':</strong> ' . htmlspecialchars($value) . '</li>';
            }
            $html .= '</ul>
            </div>';
        }

        $html .= '
    <table>';

        if ($this->role === 'student') {
            $html .= '<thead>
                <tr>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Academic Advisor</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Address</th>
                    <th>Nationality</th>
                    <th>Semester</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($users as $user) {
                $academicAdvisorName = 'Not Assigned';
                if ($user->student->academicAdvisorID && isset($user->student->academicAdvisor)) {
                    $academicAdvisorName = $user->student->academicAdvisor->user->name ?? $user->student->academicAdvisorID;
                }

                $html .= '<tr>
                    <td>' . ($user->student->studentID ?? 'N/A') . '</td>
                    <td>' . $user->email . '</td>
                    <td>' . $user->name . '</td>
                    <td>' . ($user->student->program ?? 'N/A') . '</td>
                    <td>' . $academicAdvisorName . '</td>
                    <td>' . ($user->student->phone ?? 'N/A') . '</td>
                    <td>' . ($user->student->status ?? 'N/A') . '</td>
                    <td>' . ($user->student->address ?? 'N/A') . '</td>
                    <td>' . ($user->student->nationality ?? 'N/A') . '</td>
                    <td>' . ($user->student->semester ?? 'N/A') . '</td>
                    <td>' . ($user->student->year ?? 'N/A') . '</td>
                </tr>';
            }
        } else if ($this->role === 'lecturer') {
            $html .= '<thead>
                <tr>
                    <th>Lecturer ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Staff Grade</th>
                    <th>Role</th>
                    <th>Position</th>
                    <th>State</th>
                    <th>Research Group</th>
                    <th>Department</th>
                    <th>Student Quota</th>
                    <th>Special Roles</th>
                    <th>Semester</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($users as $user) {
                $specialRoles = [];
                if ($user->lecturer->isAcademicAdvisor) $specialRoles[] = 'Academic Advisor';
                if ($user->lecturer->isSupervisorFaculty) $specialRoles[] = 'Supervisor Faculty';
                if ($user->lecturer->isCommittee) $specialRoles[] = 'Committee';
                if ($user->lecturer->isCoordinator) $specialRoles[] = 'Coordinator';
                if ($user->lecturer->isAdmin) $specialRoles[] = 'Admin';

                $html .= '<tr>
                    <td>' . ($user->lecturer->lecturerID ?? 'N/A') . '</td>
                    <td>' . $user->email . '</td>
                    <td>' . $user->name . '</td>
                    <td>' . ($user->lecturer->staffGrade ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->role ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->position ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->state ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->researchGroup ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->department ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->supervisor_quota ?? 'N/A') . '</td>
                    <td>' . (implode(', ', $specialRoles) ?: 'None') . '</td>
                    <td>' . ($user->lecturer->semester ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->year ?? 'N/A') . '</td>
                </tr>';
            }
        }

        $html .= '</tbody>
    </table>
</body>
</html>';

        return $html;
    }

    private function generateWordHTML($users)
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotPromptForConvert/>
            <w:DoNotShowInsertionsAndDeletions/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page { margin: 1in; }
        body { font-family: "Times New Roman", serif; font-size: 12pt; }
        h1 { font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 20pt; }
        table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        th, td { border: 1pt solid black; padding: 3pt; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        p { margin: 6pt 0; }
    </style>
</head>
<body>
    <h1>User Directory - ' . ucfirst($this->role ?: 'All') . ' Users</h1>
    <p><strong>Export Date:</strong> ' . now()->format('F d, Y H:i:s') . '</p>
    <p><strong>Total Records:</strong> ' . $users->count() . '</p>';

        // Add filter information
        $filters = $this->getFilterInfo();
        if (!empty($filters)) {
            $html .= '<div style="margin: 20pt 0; padding: 12pt; background-color: #f0f0f0; border-left: 4pt solid #4CAF50;">
                <h2 style="margin-top: 0; color: #333; font-size: 14pt;">Applied Filters:</h2>
                <ul style="margin: 6pt 0; padding-left: 20pt;">';
            foreach ($filters as $key => $value) {
                $html .= '<li style="margin: 3pt 0;"><strong>' . $key . ':</strong> ' . htmlspecialchars($value) . '</li>';
            }
            $html .= '</ul>
            </div>';
        }

        $html .= '
    <table>';

        if ($this->role === 'student') {
            $html .= '<thead>
                <tr>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Academic Advisor</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Address</th>
                    <th>Nationality</th>
                    <th>Semester</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($users as $user) {
                $html .= '<tr>
                    <td>' . ($user->student->studentID ?? 'N/A') . '</td>
                    <td>' . $user->email . '</td>
                    <td>' . $user->name . '</td>
                    <td>' . ($user->student->program ?? 'N/A') . '</td>
                    <td>' . (($user->student->academicAdvisorID && isset($user->student->academicAdvisor)) ? ($user->student->academicAdvisor->user->name ?? $user->student->academicAdvisorID) : 'Not Assigned') . '</td>
                    <td>' . ($user->student->phone ?? 'N/A') . '</td>
                    <td>' . ($user->student->status ?? 'N/A') . '</td>
                    <td>' . ($user->student->address ?? 'N/A') . '</td>
                    <td>' . ($user->student->nationality ?? 'N/A') . '</td>
                    <td>' . ($user->student->semester ?? 'N/A') . '</td>
                    <td>' . ($user->student->year ?? 'N/A') . '</td>
                </tr>';
            }
        } else if ($this->role === 'lecturer') {
            $html .= '<thead>
                <tr>
                    <th>Lecturer ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Staff Grade</th>
                    <th>Role</th>
                    <th>Position</th>
                    <th>State</th>
                    <th>Research Group</th>
                    <th>Department</th>
                    <th>Student Quota</th>
                    <th>Special Roles</th>
                    <th>Semester</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($users as $user) {
                $specialRoles = [];
                if ($user->lecturer->isAcademicAdvisor) $specialRoles[] = 'Academic Advisor';
                if ($user->lecturer->isSupervisorFaculty) $specialRoles[] = 'Supervisor Faculty';
                if ($user->lecturer->isCommittee) $specialRoles[] = 'Committee';
                if ($user->lecturer->isCoordinator) $specialRoles[] = 'Coordinator';
                if ($user->lecturer->isAdmin) $specialRoles[] = 'Admin';

                $html .= '<tr>
                    <td>' . ($user->lecturer->lecturerID ?? 'N/A') . '</td>
                    <td>' . $user->email . '</td>
                    <td>' . $user->name . '</td>
                    <td>' . ($user->lecturer->staffGrade ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->role ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->position ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->state ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->researchGroup ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->department ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->supervisor_quota ?? 'N/A') . '</td>
                    <td>' . (implode(', ', $specialRoles) ?: 'None') . '</td>
                    <td>' . ($user->lecturer->semester ?? 'N/A') . '</td>
                    <td>' . ($user->lecturer->year ?? 'N/A') . '</td>
                </tr>';
            }
        }

        $html .= '</tbody>
    </table>
</body>
</html>';

        return $html;
    }

    // Modal toggle methods
    public function toggleBulkRegistration()
    {
        $this->showBulkRegistration = !$this->showBulkRegistration;
    }

    public function toggleStudentRegistration()
    {
        $this->showStudentRegistration = !$this->showStudentRegistration;
        $this->resetStudentForm();
    }

    public function toggleLecturerRegistration()
    {
        $this->showLecturerRegistration = !$this->showLecturerRegistration;
        $this->resetLecturerForm();
    }

    // Form reset methods
    public function resetStudentForm()
    {
        $this->studentName = '';
        $this->studentEmail = '';
        $this->studentID = '';
        $this->studentPhone = '';
        $this->studentAddress = '';
        $this->studentCity = '';
        $this->studentPostcode = '';
        $this->studentState = '';
        $this->studentCountry = '';
        $this->studentLatitude = null;
        $this->studentLongitude = null;
        $this->studentNationality = '';
        $this->studentProgram = '';
        $this->studentSemester = '';
        $this->studentYear = date('Y');
        $this->studentAcademicAdvisorID = '';
    }

    public function resetLecturerForm()
    {
        $this->lecturerName = '';
        $this->lecturerEmail = '';
        $this->lecturerID = '';
        $this->lecturerStaffGrade = '';
        $this->lecturerRole = '';
        $this->lecturerPosition = '';
        $this->lecturerAddress = '';
        $this->lecturerCity = '';
        $this->lecturerPostcode = '';
        $this->lecturerState = '';
        $this->lecturerCountry = '';
        $this->lecturerLatitude = null;
        $this->lecturerLongitude = null;
        $this->lecturerResearchGroup = '';
        $this->lecturerDepartment = '';
        $this->lecturerProgram = '';
        $this->lecturerSemester = '';
        $this->lecturerYear = date('Y');
        $this->lecturerSupervisorQuota = 0;
        $this->lecturerIsAcademicAdvisor = false;
        $this->lecturerIsSupervisorFaculty = false;
        $this->lecturerIsCommittee = false;
        $this->lecturerIsCoordinator = false;
        $this->lecturerIsAdmin = false;
    }

    // Bulk registration from CSV
    public function registerUsersFromCSV()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
            'bulkSemester' => 'required|in:1,2',
            'bulkYear' => 'required|integer|min:2020|max:2040',
        ]);

        try {
            $csvData = array_map('str_getcsv', file($this->csvFile->getRealPath()));

            if (empty($csvData)) {
                session()->flash('error', 'CSV file is empty or could not be read.');
                return;
            }

            $header = array_shift($csvData);
            $header = array_map('trim', $header);

            // Auto-detect file type by header
            if (in_array('studentID', $header)) {
                $roleType = 'student';
            } elseif (in_array('lecturerID', $header)) {
                $roleType = 'lecturer';
            } else {
                session()->flash('error', "CSV must contain either 'studentID' or 'lecturerID' column.");
                return;
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $createdUsers = [];

            foreach ($csvData as $index => $row) {
                try {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = array_combine($header, $row);
                    $data = array_map('trim', $data);

                    if ($roleType === 'student') {
                        $result = $this->createStudentFromCSV($data, $index + 2);
                    } else {
                        $result = $this->createLecturerFromCSV($data, $index + 2);
                    }

                    if ($result['success']) {
                        $successCount++;
                        $createdUsers[] = $result;
                    } else {
                        $errorCount++;
                        $errors[] = $result['error'];
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            // Send email notifications
            foreach ($createdUsers as $userData) {
                try {
                    if (isset($userData['user']) && $userData['user']) {
                        $userData['user']->notify(new UserRegistrationNotification($userData['password'], $userData['role']));
                    }
                } catch (\Exception $e) {
                    $email = isset($userData['user']) ? $userData['user']->email : 'unknown';
                    Log::error('Failed to send email to ' . $email . ': ' . $e->getMessage());
                }
            }

            $message = "Registration completed! {$successCount} users created successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }

            session()->flash('message', $message);
            if (!empty($errors)) {
                session()->flash('errors', $errors);
            }

            $this->showBulkRegistration = false;
            $this->csvFile = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Error processing CSV file: ' . $e->getMessage());
        }
    }

    private function createStudentFromCSV($data, $rowNumber)
    {
        try {
            DB::beginTransaction();

            $defaultPassword = uniqid();
            $user = User::create([
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'password' => Hash::make($defaultPassword),
                'role' => 'student',
            ]);

            // Try to geocode the address if coordinates are not provided
            $latitude = !empty($data['latitude']) ? (float)$data['latitude'] : null;
            $longitude = !empty($data['longitude']) ? (float)$data['longitude'] : null;

            if (is_null($latitude) || is_null($longitude)) {
                $geocodeResult = $this->geocodingService->geocodeStructuredAddress([
                    'street' => $data['address'] ?? '',
                    'city' => $data['city'] ?? '',
                    'postcode' => $data['postcode'] ?? '',
                    'state' => $data['state'] ?? '',
                    'country' => $data['country'] ?? ''
                ]);

                if ($geocodeResult) {
                    $latitude = $geocodeResult['latitude'];
                    $longitude = $geocodeResult['longitude'];
                }
            }

            // Get academic advisor ID from CSV data or bulk assignment
            $academicAdvisorID = null;
            if (!empty($data['academicAdvisorID'])) {
                $result = $this->findAcademicAdvisor($data['academicAdvisorID'], $rowNumber);
                if (!$result['success']) {
                    DB::rollback();
                    return [
                        'success' => false,
                        'error' => $result['error']
                    ];
                }
                $academicAdvisorID = $result['advisorID'];
            }

            Student::create([
                'studentID' => $data['studentID'] ?? '',
                'user_id' => $user->id,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'nationality' => $data['nationality'] ?? null,
                'program' => $this->convertProgramToFullName($data['program'] ?? null),
                'semester' => $this->bulkSemester,
                'year' => $this->bulkYear,
                'academicAdvisorID' => $academicAdvisorID,
                'status' => 'active',
            ]);

            DB::commit();

            return [
                'success' => true,
                'user' => $user,
                'password' => $defaultPassword,
                'role' => 'student'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: " . $e->getMessage()
            ];
        }
    }

    private function createLecturerFromCSV($data, $rowNumber)
    {
        try {
            DB::beginTransaction();

            $defaultPassword = uniqid();
            $user = User::create([
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'password' => Hash::make($defaultPassword),
                'role' => 'lecturer',
            ]);

            // Try to geocode the address if coordinates are not provided
            $latitude = !empty($data['latitude']) ? (float)$data['latitude'] : null;
            $longitude = !empty($data['longitude']) ? (float)$data['longitude'] : null;

            if (is_null($latitude) || is_null($longitude)) {
                $geocodeResult = $this->geocodingService->geocodeStructuredAddress([
                    'street' => $data['address'] ?? '',
                    'city' => $data['city'] ?? '',
                    'postcode' => $data['postcode'] ?? '',
                    'state' => $data['state'] ?? '',
                    'country' => $data['country'] ?? ''
                ]);

                if ($geocodeResult) {
                    $latitude = $geocodeResult['latitude'];
                    $longitude = $geocodeResult['longitude'];
                }
            }

            Lecturer::create([
                'lecturerID' => $data['lecturerID'] ?? '',
                'user_id' => $user->id,
                'staffGrade' => $data['staffGrade'] ?? null,
                'role' => $data['role'] ?? null,
                'position' => $data['position'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'researchGroup' => $data['researchGroup'] ?? null,
                'department' => $data['department'] ?? null,
                'program' => $this->convertProgramToFullName($data['program'] ?? null),
                'travel_preference' => isset($data['travel_preference']) && in_array(strtolower($data['travel_preference']), ['local', 'nationwide'])
                    ? strtolower($data['travel_preference'])
                    : 'local',
                'semester' => $this->bulkSemester,
                'year' => $this->bulkYear,
                'isAcademicAdvisor' => isset($data['isAcademicAdvisor']) && strtolower($data['isAcademicAdvisor']) === 'true',
                'isSupervisorFaculty' => isset($data['isSupervisorFaculty']) && strtolower($data['isSupervisorFaculty']) === 'true',
                'isCommittee' => isset($data['isCommittee']) && strtolower($data['isCommittee']) === 'true',
                'isCoordinator' => isset($data['isCoordinator']) && strtolower($data['isCoordinator']) === 'true',
                'isAdmin' => isset($data['isAdmin']) && strtolower($data['isAdmin']) === 'true',
                'supervisor_quota' => isset($data['supervisor_quota']) ? (int)$data['supervisor_quota'] : (isset($data['studentQuota']) ? (int)$data['studentQuota'] : 0),
                'status' => 'active',
            ]);

            DB::commit();

            return [
                'success' => true,
                'user' => $user,
                'password' => $defaultPassword,
                'role' => 'lecturer'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'error' => "Row {$rowNumber}: " . $e->getMessage()
            ];
        }
    }

    // Individual student registration
    public function registerStudent()
    {
        $this->validate([
            'studentName' => 'required|string|max:255',
            'studentEmail' => 'required|email|unique:users,email',
            'studentID' => 'required|string|unique:students,studentID',
            'studentPhone' => 'nullable|string',
            'studentAddress' => 'nullable|string',
            'studentCity' => 'nullable|string',
            'studentPostcode' => 'nullable|string',
            'studentState' => 'nullable|string',
            'studentCountry' => 'nullable|string',
            'studentNationality' => 'nullable|string',
            'studentProgram' => 'nullable|in:BCS,BCN,BCM,BCY,DRC',
            'studentSemester' => 'required|in:1,2',
            'studentYear' => 'required|integer|min:2020|max:2040',
            'studentAcademicAdvisorID' => 'nullable|string|exists:lecturers,lecturerID',
        ]);

        try {
            DB::beginTransaction();

            $defaultPassword = uniqid();
            $user = User::create([
                'name' => $this->studentName,
                'email' => $this->studentEmail,
                'password' => Hash::make($defaultPassword),
                'role' => 'student',
            ]);

            // Try to geocode the address if coordinates are not provided
            $latitude = $this->studentLatitude;
            $longitude = $this->studentLongitude;

            if (empty($latitude) || empty($longitude)) {
                $geocodeResult = $this->geocodingService->geocodeStructuredAddress([
                    'street' => $this->studentAddress,
                    'city' => $this->studentCity,
                    'postcode' => $this->studentPostcode,
                    'state' => $this->studentState,
                    'country' => $this->studentCountry
                ]);

                if ($geocodeResult) {
                    $latitude = $geocodeResult['latitude'];
                    $longitude = $geocodeResult['longitude'];
                }
            }

            Student::create([
                'studentID' => $this->studentID,
                'user_id' => $user->id,
                'phone' => $this->studentPhone,
                'address' => $this->studentAddress,
                'city' => $this->studentCity,
                'postcode' => $this->studentPostcode,
                'state' => $this->studentState,
                'country' => $this->studentCountry,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'nationality' => $this->studentNationality,
                'program' => $this->convertProgramToFullName($this->studentProgram),
                'semester' => $this->studentSemester,
                'year' => $this->studentYear,
                'academicAdvisorID' => $this->studentAcademicAdvisorID ?: null,
                'status' => 'active',
            ]);

            DB::commit();

            // Send email notification
            try {
                $user->notify(new UserRegistrationNotification($defaultPassword, 'student'));
                $emailMessage = ' Email notification sent to ' . $user->email . '.';
            } catch (\Exception $e) {
                Log::error('Failed to send email to ' . $user->email . ': ' . $e->getMessage());
                $emailMessage = ' Note: Email notification failed to send.';
            }

            session()->flash('message', 'Student registered successfully!' . $emailMessage);
            $this->showStudentRegistration = false;
            $this->resetStudentForm();
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Failed to register student: ' . $e->getMessage());
        }
    }

    // Individual lecturer registration
    public function registerLecturer()
    {
        $this->validate([
            'lecturerName' => 'required|string|max:255',
            'lecturerEmail' => 'required|email|unique:users,email',
            'lecturerID' => 'required|string|unique:lecturers,lecturerID',
            'lecturerStaffGrade' => 'nullable|string',
            'lecturerRole' => 'nullable|string',
            'lecturerPosition' => 'nullable|string',
            'lecturerAddress' => 'nullable|string',
            'lecturerCity' => 'nullable|string',
            'lecturerPostcode' => 'nullable|string',
            'lecturerState' => 'nullable|string',
            'lecturerCountry' => 'nullable|string',
            'lecturerResearchGroup' => 'nullable|string',
            'lecturerDepartment' => 'nullable|string',
            'lecturerProgram' => 'nullable|in:BCS,BCN,BCM,BCY,DRC',
            'lecturerSemester' => 'required|in:1,2',
            'lecturerYear' => 'required|integer|min:2020|max:2040',
            'lecturerSupervisorQuota' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $defaultPassword = uniqid();
            $user = User::create([
                'name' => $this->lecturerName,
                'email' => $this->lecturerEmail,
                'password' => Hash::make($defaultPassword),
                'role' => 'lecturer',
            ]);

            // Try to geocode the address if coordinates are not provided
            $latitude = $this->lecturerLatitude;
            $longitude = $this->lecturerLongitude;

            if (empty($latitude) || empty($longitude)) {
                $geocodeResult = $this->geocodingService->geocodeStructuredAddress([
                    'street' => $this->lecturerAddress,
                    'city' => $this->lecturerCity,
                    'postcode' => $this->lecturerPostcode,
                    'state' => $this->lecturerState,
                    'country' => $this->lecturerCountry
                ]);

                if ($geocodeResult) {
                    $latitude = $geocodeResult['latitude'];
                    $longitude = $geocodeResult['longitude'];
                }
            }

            Lecturer::create([
                'lecturerID' => $this->lecturerID,
                'user_id' => $user->id,
                'staffGrade' => $this->lecturerStaffGrade,
                'role' => $this->lecturerRole,
                'position' => $this->lecturerPosition,
                'address' => $this->lecturerAddress,
                'city' => $this->lecturerCity,
                'postcode' => $this->lecturerPostcode,
                'state' => $this->lecturerState,
                'country' => $this->lecturerCountry,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'researchGroup' => $this->lecturerResearchGroup,
                'department' => $this->lecturerDepartment,
                'program' => $this->getProgramFullName($this->lecturerProgram),
                'semester' => $this->lecturerSemester,
                'year' => $this->lecturerYear,
                'supervisor_quota' => $this->lecturerSupervisorQuota ?? 0,
                'isAcademicAdvisor' => $this->lecturerIsAcademicAdvisor,
                'isSupervisorFaculty' => $this->lecturerIsSupervisorFaculty,
                'isCommittee' => $this->lecturerIsCommittee,
                'isCoordinator' => $this->lecturerIsCoordinator,
                'isAdmin' => $this->lecturerIsAdmin,
                'status' => 'active',
            ]);

            DB::commit();

            // Send email notification
            try {
                $user->notify(new UserRegistrationNotification($defaultPassword, 'lecturer'));
                $emailMessage = ' Email notification sent to ' . $user->email . '.';
            } catch (\Exception $e) {
                Log::error('Failed to send email to ' . $user->email . ': ' . $e->getMessage());
                $emailMessage = ' Note: Email notification failed to send.';
            }

            session()->flash('message', 'Lecturer registered successfully!' . $emailMessage);
            $this->showLecturerRegistration = false;
            $this->resetLecturerForm();
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Failed to register lecturer: ' . $e->getMessage());
        }
    }
}
