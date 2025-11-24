<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $users;
    protected $role;

    public function __construct($users, $role)
    {
        $this->users = $users;
        $this->role = $role;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        if ($this->role === 'student') {
            return [
                'Student ID',
                'Name',
                'Email',
                'Phone',
                'Address',
                'Nationality',
                'Program',
                'Semester',
                'Year',
                'Status',
                'Academic Advisor ID',
                'Industry Supervisor',
                'Registration Date'
            ];
        } else {
            return [
                'Lecturer ID',
                'Name',
                'Email',
                'Staff Grade',
                'Role',
                'Position',
                'State',
                'Research Group',
                'Department',
                'Supervisor Quota',
                'Semester',
                'Year',
                'Special Roles',
                'Registration Date'
            ];
        }
    }

    public function map($user): array
    {
        if ($this->role === 'student') {
            return [
                $user->student->studentID ?? 'N/A',
                $user->name,
                $user->email,
                $user->student->phone ?? 'N/A',
                $user->student->address ?? 'N/A',
                $user->student->nationality ?? 'N/A',
                $user->student->program ?? 'N/A',
                $user->student->semester ?? 'N/A',
                $user->student->year ?? 'N/A',
                $user->student->status ?? 'N/A',
                $user->student->academicAdvisorID ?? 'Not Assigned',
                $user->student->acceptedPlacementApplication->industrySupervisorName ?? 'Not Assigned',
                $user->created_at->format('Y-m-d H:i:s')
            ];
        } else {
            $specialRoles = [];
            if ($user->lecturer->isAcademicAdvisor) $specialRoles[] = 'Academic Advisor';
            if ($user->lecturer->isSupervisorFaculty) $specialRoles[] = 'Supervisor Faculty';
            if ($user->lecturer->isCommittee) $specialRoles[] = 'Committee';
            if ($user->lecturer->isCoordinator) $specialRoles[] = 'Coordinator';
            if ($user->lecturer->isAdmin) $specialRoles[] = 'Admin';

            return [
                $user->lecturer->lecturerID ?? 'N/A',
                $user->name,
                $user->email,
                $user->lecturer->staffGrade ?? 'N/A',
                $user->lecturer->role ?? 'N/A',
                $user->lecturer->position ?? 'N/A',
                $user->lecturer->state ?? 'N/A',
                $user->lecturer->researchGroup ?? 'N/A',
                $user->lecturer->department ?? 'N/A',
                $user->lecturer->supervisor_quota ?? '0',
                $user->lecturer->semester ?? 'N/A',
                $user->lecturer->year ?? 'N/A',
                empty($specialRoles) ? 'No Special Roles' : implode(', ', $specialRoles),
                $user->created_at->format('Y-m-d H:i:s')
            ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as a header
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '366092'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return ucfirst($this->role) . ' Directory';
    }
}
