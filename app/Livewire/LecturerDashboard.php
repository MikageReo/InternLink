<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CourseVerification;
use App\Models\PlacementApplication;
use App\Models\RequestDefer;
use App\Models\RequestJustification;
use App\Models\SupervisorAssignment;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LecturerDashboard extends Component
{
    use WithPagination;

    public $selectedStudent = null;
    public $showStudentModal = false;
    public $activitiesPerPage = 5;
    public $activitiesPage = 1;
    public $tasksPerPage = 5;
    public $tasksPage = 1;
    public $adviseesPerPage = 5;
    public $adviseesPage = 1;
    public $supervisedPerPage = 5;
    public $supervisedPage = 1;

    public function viewStudentDetail($studentId)
    {
        $this->selectedStudent = Student::with([
            'user',
            'academicAdvisor.user',
            'courseVerifications' => function ($q) {
                $q->latest()->limit(5);
            },
            'placementApplications' => function ($q) {
                $q->latest()->limit(5);
            },
            'supervisorAssignments.supervisor.user'
        ])->find($studentId);

        $this->showStudentModal = true;
    }

    public function closeStudentModal()
    {
        $this->showStudentModal = false;
        $this->selectedStudent = null;
    }

    public function render()
    {
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer) {
            return view('livewire.lecturer-dashboard', [
                'stats' => $this->getEmptyStats(),
                'pendingTasks' => collect([]),
                'supervisedStudents' => collect([]),
                'recentActivities' => collect([]),
                'analytics' => [],
            ]);
        }

        // Fetch role-based data
        $stats = $this->getRoleBasedStats($lecturer);
        $pendingTasks = $this->getPendingTasks($lecturer);
        $supervisedStudents = $this->getSupervisedStudents($lecturer);
        $recentActivities = $this->getRecentActivities($lecturer);
        $analytics = $this->getAnalytics($lecturer);

        // Get advisees for academic advisors
        $advisees = $this->getAdvisees($lecturer);

        // Paginate all collections
        $paginatedTasks = $this->paginateCollection($pendingTasks, $this->tasksPage, $this->tasksPerPage, 'tasksPage');
        $paginatedAdvisees = $this->paginateCollection($advisees, $this->adviseesPage, $this->adviseesPerPage, 'adviseesPage');
        $paginatedSupervised = $this->paginateCollection($supervisedStudents, $this->supervisedPage, $this->supervisedPerPage, 'supervisedPage');
        $paginatedActivities = $this->paginateCollection($recentActivities, $this->activitiesPage, $this->activitiesPerPage, 'activitiesPage');

        return view('livewire.lecturer-dashboard', [
            'stats' => $stats,
            'pendingTasks' => $paginatedTasks,
            'supervisedStudents' => $paginatedSupervised,
            'advisees' => $paginatedAdvisees,
            'recentActivities' => $paginatedActivities,
            'analytics' => $analytics,
        ]);
    }

    private function getRoleBasedStats($lecturer): array
    {
        $stats = [];

        // For All Lecturers: Course Verifications
        $courseVerifications = CourseVerification::where('lecturerID', $lecturer->lecturerID)->get();
        $stats['courseVerifications'] = [
            'pending' => $courseVerifications->where('status', 'pending')->count(),
            'total' => $courseVerifications->count(),
            'approved' => $courseVerifications->where('status', 'approved')->count(),
            'rejected' => $courseVerifications->where('status', 'rejected')->count(),
            'approval_rate' => $courseVerifications->count() > 0
                ? round(($courseVerifications->where('status', 'approved')->count() / $courseVerifications->count()) * 100, 1)
                : 0,
        ];

        // For Supervisors: Supervised Students
        if ($lecturer->isSupervisorFaculty) {
            $activeAssignments = SupervisorAssignment::where('supervisorID', $lecturer->lecturerID)
                ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
                ->count();

            $stats['supervisedStudents'] = [
                'current' => $activeAssignments,
                'quota' => $lecturer->supervisor_quota ?? 0,
                'available' => max(0, ($lecturer->supervisor_quota ?? 0) - $activeAssignments),
            ];
        }

        // For Committee Members
        if ($lecturer->isCommittee) {
            $stats['committee'] = [
                'pendingPlacementApplications' => PlacementApplication::where('committeeStatus', 'Pending')
                    ->count(),
                'pendingDeferRequests' => RequestDefer::where('committeeStatus', 'Pending')
                    ->count(),
                'pendingChangeRequests' => RequestJustification::where('committeeStatus', 'Pending')
                    ->count(),
            ];
        }

        // For Coordinators
        if ($lecturer->isCoordinator) {
            $stats['coordinator'] = [
                'pendingPlacementApprovals' => PlacementApplication::where('coordinatorStatus', 'Pending')
                    ->where('committeeStatus', 'Approved')
                    ->count(),
                'pendingDeferApprovals' => RequestDefer::where('coordinatorStatus', 'Pending')
                    ->where('committeeStatus', 'Approved')
                    ->count(),
                'pendingChangeRequests' => RequestJustification::where('coordinatorStatus', 'Pending')
                    ->where('committeeStatus', 'Approved')
                    ->count(),
                'unassignedStudents' => Student::whereHas('placementApplications', function ($q) {
                    $q->where('studentAcceptance', 'Accepted');
                })->whereDoesntHave('supervisorAssignments', function ($q) {
                    $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
                })->count(),
                'totalSupervisorAssignments' => SupervisorAssignment::where('status', SupervisorAssignment::STATUS_ASSIGNED)->count(),
                'availableSupervisors' => Lecturer::where('isSupervisorFaculty', true)
                    ->where('status', Lecturer::STATUS_ACTIVE)
                    ->whereRaw('(supervisor_quota - COALESCE(current_assignments, 0)) > 0')
                    ->count(),
            ];
        }

        // For Academic Advisors
        if ($lecturer->isAcademicAdvisor) {
            $stats['academicAdvisor'] = [
                'adviseeCount' => Student::where('academicAdvisorID', $lecturer->lecturerID)->count(),
                'pendingCourseVerifications' => CourseVerification::whereHas('student', function ($q) use ($lecturer) {
                    $q->where('academicAdvisorID', $lecturer->lecturerID);
                })->whereNull('academicAdvisorStatus')->count(),
            ];
        }

        return $stats;
    }

    private function getPendingTasks($lecturer): Collection
    {
        $tasks = collect([]);

        // Course verifications needing review - for academic advisors
        if ($lecturer->isAcademicAdvisor && !$lecturer->isCoordinator && !$lecturer->isCommittee) {
            $pendingVerifications = CourseVerification::whereHas('student', function ($q) use ($lecturer) {
                $q->where('academicAdvisorID', $lecturer->lecturerID);
            })
            ->whereNull('academicAdvisorStatus')
            ->with(['student.user'])
            ->orderBy('applicationDate', 'asc')
            ->limit(5)
            ->get();

            foreach ($pendingVerifications as $verification) {
                $tasks->push([
                    'type' => 'course_verification',
                    'title' => 'Review Course Verification (Academic Advisor)',
                    'description' => "Student: {$verification->student->user->name} ({$verification->student->studentID})",
                    'date' => $verification->applicationDate,
                    'priority' => 'high',
                    'link' => route('lecturer.courseVerificationManagement'),
                    'id' => $verification->courseVerificationID,
                ]);
            }
        } else {
            // Course verifications needing review - for coordinators/committee
            $pendingVerifications = CourseVerification::where('lecturerID', $lecturer->lecturerID)
                ->where('status', 'pending')
                ->with(['student.user'])
                ->orderBy('applicationDate', 'asc')
                ->limit(5)
                ->get();

            foreach ($pendingVerifications as $verification) {
                $tasks->push([
                    'type' => 'course_verification',
                    'title' => 'Review Course Verification',
                    'description' => "Student: {$verification->student->user->name} ({$verification->student->studentID})",
                    'date' => $verification->applicationDate,
                    'priority' => 'high',
                    'link' => route('lecturer.courseVerificationManagement'),
                    'id' => $verification->courseVerificationID,
                ]);
            }
        }

        // Placement applications to review (if committee)
        // Committee members see ALL pending applications (not just assigned ones)
        if ($lecturer->isCommittee) {
            $pendingPlacements = PlacementApplication::where('committeeStatus', 'Pending')
                ->with(['student.user'])
                ->orderBy('applicationDate', 'asc')
                ->limit(5)
                ->get();

            foreach ($pendingPlacements as $placement) {
                $tasks->push([
                    'type' => 'placement_application',
                    'title' => "Review Placement Application (Committee)",
                    'description' => "Student: {$placement->student->user->name} - Company: {$placement->companyName}",
                    'date' => $placement->applicationDate,
                    'priority' => 'high',
                    'link' => route('lecturer.placementApplications'),
                    'id' => $placement->applicationID,
                ]);
            }
        }

        // Placement applications to review (if coordinator)
        if ($lecturer->isCoordinator) {
            $pendingPlacements = PlacementApplication::where('coordinatorStatus', 'Pending')
                ->where('committeeStatus', 'Approved')
                ->with(['student.user'])
                ->orderBy('applicationDate', 'asc')
                ->limit(5)
                ->get();

            foreach ($pendingPlacements as $placement) {
                $tasks->push([
                    'type' => 'placement_application',
                    'title' => "Review Placement Application (Coordinator)",
                    'description' => "Student: {$placement->student->user->name} - Company: {$placement->companyName}",
                    'date' => $placement->applicationDate,
                    'priority' => 'high',
                    'link' => route('lecturer.placementApplications'),
                    'id' => $placement->applicationID,
                ]);
            }
        }

        // Defer requests to review (if committee)
        // Committee members see ALL pending defer requests (not just assigned ones)
        if ($lecturer->isCommittee) {
            $pendingDefers = RequestDefer::where('committeeStatus', 'Pending')
                ->with(['student.user'])
                ->orderBy('applicationDate', 'asc')
                ->limit(5)
                ->get();

            foreach ($pendingDefers as $defer) {
                $tasks->push([
                    'type' => 'defer_request',
                    'title' => "Review Defer Request (Committee)",
                    'description' => "Student: {$defer->student->user->name}",
                    'date' => $defer->applicationDate,
                    'priority' => 'medium',
                    'link' => route('lecturer.requestDefer'),
                    'id' => $defer->deferID,
                ]);
            }
        }

        // Change requests to review (if committee)
        // Committee members see ALL pending change requests (not just assigned ones)
        if ($lecturer->isCommittee) {
            $pendingChangeRequests = RequestJustification::where('committeeStatus', 'Pending')
                ->with(['placementApplication.student.user'])
                ->orderBy('requestDate', 'asc')
                ->limit(5)
                ->get();

            foreach ($pendingChangeRequests as $changeRequest) {
                $student = $changeRequest->placementApplication->student ?? null;
                if ($student) {
                    $tasks->push([
                        'type' => 'change_request',
                        'title' => "Review Change Request (Committee)",
                        'description' => "Student: {$student->user->name} - Application: {$changeRequest->placementApplication->companyName}",
                        'date' => $changeRequest->requestDate,
                        'priority' => 'high',
                        'link' => route('lecturer.changeRequests'),
                        'id' => $changeRequest->justificationID,
                    ]);
                }
            }
        }

        // Defer requests to review (if coordinator)
        if ($lecturer->isCoordinator) {
            $pendingDefers = RequestDefer::where('coordinatorStatus', 'Pending')
                ->where('committeeStatus', 'Approved')
                ->with(['student.user'])
                ->orderBy('applicationDate', 'asc')
                ->limit(5)
                ->get();

            foreach ($pendingDefers as $defer) {
                $tasks->push([
                    'type' => 'defer_request',
                    'title' => "Review Defer Request (Coordinator)",
                    'description' => "Student: {$defer->student->user->name}",
                    'date' => $defer->applicationDate,
                    'priority' => 'medium',
                    'link' => route('lecturer.requestDefer'),
                    'id' => $defer->deferID,
                ]);
            }
        }

        // Change requests to review (if coordinator)
        if ($lecturer->isCoordinator) {
            $pendingChangeRequests = RequestJustification::where('coordinatorStatus', 'Pending')
                ->where('committeeStatus', 'Approved')
                ->with(['placementApplication.student.user'])
                ->orderBy('requestDate', 'asc')
                ->limit(5)
                ->get();

            foreach ($pendingChangeRequests as $changeRequest) {
                $student = $changeRequest->placementApplication->student ?? null;
                if ($student) {
                    $tasks->push([
                        'type' => 'change_request',
                        'title' => "Review Change Request (Coordinator)",
                        'description' => "Student: {$student->user->name} - Application: {$changeRequest->placementApplication->companyName}",
                        'date' => $changeRequest->requestDate,
                        'priority' => 'high',
                        'link' => route('lecturer.changeRequests'),
                        'id' => $changeRequest->justificationID,
                    ]);
                }
            }
        }

        // Supervisor assignments to make (if coordinator)
        if ($lecturer->isCoordinator) {
            $unassignedStudents = Student::whereHas('placementApplications', function ($q) {
                $q->where('studentAcceptance', 'Accepted');
            })->whereDoesntHave('supervisorAssignments', function ($q) {
                $q->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            })
                ->with(['user', 'placementApplications' => function ($q) {
                    $q->where('studentAcceptance', 'Accepted')->latest()->limit(1);
                }])
                ->limit(5)
                ->get();

            foreach ($unassignedStudents as $student) {
                $tasks->push([
                    'type' => 'supervisor_assignment',
                    'title' => 'Assign Supervisor',
                    'description' => "Student: {$student->user->name} ({$student->studentID})",
                    'date' => $student->placementApplications->first()->applicationDate ?? now(),
                    'priority' => 'high',
                    'link' => route('lecturer.supervisorAssignments'),
                    'id' => $student->studentID,
                ]);
            }
        }

        // Sort by priority (high first) and date
        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        return $tasks->sort(function ($a, $b) use ($priorityOrder) {
            $priorityCompare = ($priorityOrder[$a['priority']] ?? 99) <=> ($priorityOrder[$b['priority']] ?? 99);
            if ($priorityCompare !== 0) {
                return $priorityCompare;
            }
            return $a['date'] <=> $b['date'];
        });
    }

    private function getSupervisedStudents($lecturer): Collection
    {
        if (!$lecturer->isSupervisorFaculty) {
            return collect([]);
        }

        return SupervisorAssignment::where('supervisorID', $lecturer->lecturerID)
            ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
            ->with(['student.user', 'student.placementApplications' => function ($q) {
                $q->where('studentAcceptance', 'Accepted')->latest()->limit(1);
            }])
            ->orderBy('assigned_at', 'desc')
            ->get()
            ->map(function ($assignment) {
                $placement = $assignment->student->placementApplications->first();
                return [
                    'assignment' => $assignment,
                    'student' => $assignment->student,
                    'placement' => $placement,
                    'status' => $placement ? 'Active' : 'Pending Placement',
                ];
            });
    }

    private function getAdvisees($lecturer): Collection
    {
        if (!$lecturer->isAcademicAdvisor) {
            return collect([]);
        }

        return Student::where('academicAdvisorID', $lecturer->lecturerID)
            ->with(['user', 'courseVerifications' => function ($q) {
                $q->latest()->limit(1);
            }, 'placementApplications' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('studentID', 'asc')
            ->get()
            ->map(function ($student) {
                $latestVerification = $student->courseVerifications->first();
                $latestPlacement = $student->placementApplications->first();
                return [
                    'student' => $student,
                    'latestVerification' => $latestVerification,
                    'latestPlacement' => $latestPlacement,
                ];
            });
    }

    private function getRecentActivities($lecturer): Collection
    {
        $activities = collect([]);

        // Recent course verification reviews
        $recentVerifications = CourseVerification::where('lecturerID', $lecturer->lecturerID)
            ->whereNotNull('updated_at')
            ->whereColumn('updated_at', '>', 'created_at')
            ->with(['student.user'])
            ->latest('updated_at')
            ->limit(3)
            ->get();

        foreach ($recentVerifications as $verification) {
            $activities->push([
                'type' => 'course_verification',
                'title' => 'Course Verification Reviewed',
                'description' => "Student: {$verification->student->user->name} - Status: " . ucfirst($verification->status),
                'date' => $verification->updated_at,
                'status' => $verification->status,
                'link' => route('lecturer.courseVerificationManagement'),
            ]);
        }

        // Recent placement application reviews (if committee)
        // Show applications where committee has reviewed (committeeID is set and status changed)
        if ($lecturer->isCommittee) {
            $recentPlacements = PlacementApplication::where('committeeID', $lecturer->lecturerID)
                ->whereNotNull('updated_at')
                ->whereColumn('updated_at', '>', 'created_at')
                ->where('committeeStatus', '!=', 'Pending')
                ->with(['student.user'])
                ->latest('updated_at')
                ->limit(3)
                ->get();

            foreach ($recentPlacements as $placement) {
                $activities->push([
                    'type' => 'placement_application',
                    'title' => 'Placement Application Reviewed (Committee)',
                    'description' => "Student: {$placement->student->user->name} - Company: {$placement->companyName}",
                    'date' => $placement->updated_at,
                    'status' => $placement->overall_status,
                    'link' => route('lecturer.placementApplications'),
                ]);
            }
        }

        // Recent placement application reviews (if coordinator)
        if ($lecturer->isCoordinator) {
            $recentPlacements = PlacementApplication::where('coordinatorID', $lecturer->lecturerID)
                ->whereNotNull('updated_at')
                ->whereColumn('updated_at', '>', 'created_at')
                ->with(['student.user'])
                ->latest('updated_at')
                ->limit(3)
                ->get();

            foreach ($recentPlacements as $placement) {
                $activities->push([
                    'type' => 'placement_application',
                    'title' => 'Placement Application Reviewed (Coordinator)',
                    'description' => "Student: {$placement->student->user->name} - Company: {$placement->companyName}",
                    'date' => $placement->updated_at,
                    'status' => $placement->overall_status,
                    'link' => route('lecturer.placementApplications'),
                ]);
            }
        }

        // Recent supervisor assignments (if coordinator)
        if ($lecturer->isCoordinator) {
            $recentAssignments = SupervisorAssignment::whereNotNull('assigned_at')
                ->with(['student.user', 'supervisor.user'])
                ->latest('assigned_at')
                ->limit(3)
                ->get();

            foreach ($recentAssignments as $assignment) {
                $activities->push([
                    'type' => 'supervisor_assignment',
                    'title' => 'Supervisor Assigned',
                    'description' => "Student: {$assignment->student->user->name} → Supervisor: {$assignment->supervisor->user->name}",
                    'date' => $assignment->assigned_at,
                    'status' => 'assigned',
                    'link' => route('lecturer.supervisorAssignments'),
                ]);
            }
        }

        // Sort by date (most recent first)
        return $activities->sortByDesc('date');
    }

    private function paginateCollection(Collection $collection, $currentPage, $perPage, $pageName)
    {
        $items = $collection->forPage($currentPage, $perPage)->values();
        $total = $collection->count();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );

        return $paginator;
    }

    public function goToPage($page, $pageType)
    {
        switch ($pageType) {
            case 'tasks':
                $this->tasksPage = $page;
                break;
            case 'advisees':
                $this->adviseesPage = $page;
                break;
            case 'supervised':
                $this->supervisedPage = $page;
                break;
            case 'activities':
                $this->activitiesPage = $page;
                break;
        }
    }

    private function getAnalytics($lecturer): array
    {
        $analytics = [];

        // Course verification trends (last 6 months)
        $sixMonthsAgo = now()->subMonths(6);
        $courseVerificationTrends = CourseVerification::where('lecturerID', $lecturer->lecturerID)
            ->where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, status, COUNT(*) as count')
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();

        $analytics['courseVerificationTrends'] = $courseVerificationTrends;

        // Placement application statistics (if committee/coordinator)
        if ($lecturer->isCommittee || $lecturer->isCoordinator) {
            $placementStats = PlacementApplication::query()
                ->where(function ($q) use ($lecturer) {
                    $q->where('committeeID', $lecturer->lecturerID)
                        ->orWhere('coordinatorID', $lecturer->lecturerID);
                })
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN committeeStatus = "Pending" OR coordinatorStatus = "Pending" THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN committeeStatus = "Approved" AND coordinatorStatus = "Approved" THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN committeeStatus = "Rejected" OR coordinatorStatus = "Rejected" THEN 1 ELSE 0 END) as rejected
                ')
                ->first();

            $analytics['placementApplications'] = [
                'total' => $placementStats->total ?? 0,
                'pending' => $placementStats->pending ?? 0,
                'approved' => $placementStats->approved ?? 0,
                'rejected' => $placementStats->rejected ?? 0,
            ];
        }

        // Supervisor assignment distribution (if coordinator)
        if ($lecturer->isCoordinator) {
            $assignmentDistribution = SupervisorAssignment::where('status', SupervisorAssignment::STATUS_ASSIGNED)
                ->selectRaw('supervisorID, COUNT(*) as count')
                ->groupBy('supervisorID')
                ->with('supervisor.user')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $analytics['supervisorAssignments'] = $assignmentDistribution;
        }

        // Monthly activity summary (last 6 months)
        $monthlyActivity = DB::table('course_verifications')
            ->where('lecturerID', $lecturer->lecturerID)
            ->where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $analytics['monthlyActivity'] = $monthlyActivity;

        return $analytics;
    }

    private function getEmptyStats(): array
    {
        return [
            'courseVerifications' => ['pending' => 0, 'total' => 0, 'approved' => 0, 'rejected' => 0, 'approval_rate' => 0],
            'supervisedStudents' => ['current' => 0, 'quota' => 0, 'available' => 0],
            'committee' => ['pendingPlacementApplications' => 0, 'pendingDeferRequests' => 0],
            'coordinator' => [
                'pendingPlacementApprovals' => 0,
                'pendingDeferApprovals' => 0,
                'unassignedStudents' => 0,
                'totalSupervisorAssignments' => 0,
                'availableSupervisors' => 0,
            ],
            'academicAdvisor' => ['adviseeCount' => 0, 'pendingCourseVerifications' => 0],
        ];
    }
}
