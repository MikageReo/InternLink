<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Builder;
// Removed Excel import temporarily due to missing GD extension
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\UsersExport;

class UserDirectoryTable extends Component
{
    use WithPagination;

    // Filter properties
    public $role = '';
    public $semester = '';
    public $year = '';

    // Search and sort properties
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $exportFormat = 'csv';

    // UI properties
    public $showFilters = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'role' => ['except' => ''],
        'semester' => ['except' => ''],
        'year' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->year = date('Y');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
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
        $this->reset(['search', 'role', 'semester', 'year', 'sortField', 'sortDirection']);
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

        if ($this->role === 'student') {
            // Student headers
            $headers = [
                'Student ID',
                'Email',
                'Name',
                'Program',
                'Academic Advisor',
                'Industry Supervisor',
                'Phone',
                'Status',
                'Address',
                'Nationality',
                'Semester',
                'Year'
            ];

            $output .= '"' . implode('","', $headers) . '"' . "\n";

            foreach ($users as $user) {
                $row = [
                    $user->student->studentID ?? 'N/A',
                    $user->email,
                    $user->name,
                    $user->student->program ?? 'N/A',
                    $user->student->academicAdvisorID ?? 'Not Assigned',
                    $user->student->industrySupervisorName ?? 'Not Assigned',
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
                    $user->lecturer->studentQuota ?? 'N/A',
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
            $query->where('role', $this->role);

            if ($this->role === 'student') {
                $query->with(['student']);

                if ($this->semester || $this->year) {
                    $query->whereHas('student', function ($q) {
                        if ($this->semester) {
                            $q->where('semester', $this->semester);
                        }
                        if ($this->year) {
                            $q->where('year', $this->year);
                        }
                    });
                }
            } else {
                $query->with(['lecturer']);

                if ($this->semester || $this->year) {
                    $query->whereHas('lecturer', function ($q) {
                        if ($this->semester) {
                            $q->where('semester', $this->semester);
                        }
                        if ($this->year) {
                            $q->where('year', $this->year);
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
                            ->orWhere('researchGroup', 'like', '%' . $this->search . '%');
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
            } elseif ($this->role === 'lecturer' && in_array($this->sortField, ['lecturerID', 'staffGrade', 'position', 'department'])) {
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

        return view('livewire.user-directory-table', [
            'users' => $users,
            'totalCount' => $totalCount,
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
    <p><strong>Total Records:</strong> ' . $users->count() . '</p>

    <table>';

        if ($this->role === 'student') {
            $html .= '<thead>
                <tr>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Academic Advisor</th>
                    <th>Industry Supervisor</th>
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
                    <td>' . ($user->student->academicAdvisorID ?? 'Not Assigned') . '</td>
                    <td>' . ($user->student->industrySupervisorName ?? 'Not Assigned') . '</td>
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
                    <td>' . ($user->lecturer->studentQuota ?? 'N/A') . '</td>
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
    <p><strong>Total Records:</strong> ' . $users->count() . '</p>

    <table>';

        if ($this->role === 'student') {
            $html .= '<thead>
                <tr>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Academic Advisor</th>
                    <th>Industry Supervisor</th>
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
                    <td>' . ($user->student->academicAdvisorID ?? 'Not Assigned') . '</td>
                    <td>' . ($user->student->industrySupervisorName ?? 'Not Assigned') . '</td>
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
                    <td>' . ($user->lecturer->studentQuota ?? 'N/A') . '</td>
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
}
