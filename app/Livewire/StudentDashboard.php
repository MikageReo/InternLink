<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CourseVerification;
use App\Models\PlacementApplication;
use App\Models\RequestDefer;
use App\Models\RequestJustification;
use App\Models\SupervisorAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class StudentDashboard extends Component
{
    public function render()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return view('livewire.student-dashboard', [
                'stats' => $this->getEmptyStats(),
                'latestCourseVerification' => null,
                'placementApplicationStats' => [],
                'deferRequestStats' => [],
                'changeRequestStats' => [],
                'supervisorAssignment' => null,
                'academicAdvisor' => null,
                'recentActivities' => collect([]),
                'latestPlacementApplication' => null,
            ]);
        }

        // Fetch data
        $stats = $this->getStats($student);
        $latestCourseVerification = $this->getLatestCourseVerification($student);
        $placementApplicationStats = $this->getPlacementApplicationStats($student);
        $deferRequestStats = $this->getDeferRequestStats($student);
        $changeRequestStats = $this->getChangeRequestStats($student);
        $supervisorAssignment = $this->getSupervisorAssignment($student);
        $academicAdvisor = $student->academicAdvisor;
        $recentActivities = $this->getRecentActivities($student);
        $latestPlacementApplication = $this->getLatestPlacementApplication($student);

        return view('livewire.student-dashboard', [
            'stats' => $stats,
            'latestCourseVerification' => $latestCourseVerification,
            'placementApplicationStats' => $placementApplicationStats,
            'deferRequestStats' => $deferRequestStats,
            'changeRequestStats' => $changeRequestStats,
            'supervisorAssignment' => $supervisorAssignment,
            'academicAdvisor' => $academicAdvisor,
            'recentActivities' => $recentActivities,
            'latestPlacementApplication' => $latestPlacementApplication,
        ]);
    }

    private function getStats($student): array
    {
        // Course Verification Stats
        $courseVerifications = CourseVerification::where('studentID', $student->studentID)->get();
        $courseVerificationStats = [
            'pending' => $courseVerifications->where('status', 'pending')->count(),
            'approved' => $courseVerifications->where('status', 'approved')->count(),
            'rejected' => $courseVerifications->where('status', 'rejected')->count(),
        ];

        // Placement Application Stats
        $placementApplications = PlacementApplication::where('studentID', $student->studentID)->get();
        $placementStats = [
            'total' => $placementApplications->count(),
            'pending' => $placementApplications->filter(function ($app) {
                return $app->overall_status === 'Pending';
            })->count(),
            'accepted' => $placementApplications->where('studentAcceptance', 'Accepted')->count(),
            'rejected' => $placementApplications->filter(function ($app) {
                return $app->overall_status === 'Rejected';
            })->count(),
        ];

        // Defer Request Stats
        $deferRequests = RequestDefer::where('studentID', $student->studentID)->get();
        $deferStats = [
            'pending' => $deferRequests->filter(function ($req) {
                return $req->overall_status === 'Pending';
            })->count(),
            'approved' => $deferRequests->filter(function ($req) {
                return $req->overall_status === 'Approved';
            })->count(),
        ];

        // Supervisor Assignment Status
        $hasSupervisor = $student->supervisorAssignment !== null;

        return [
            'courseVerification' => $courseVerificationStats,
            'placementApplications' => $placementStats,
            'deferRequests' => $deferStats,
            'hasSupervisor' => $hasSupervisor,
        ];
    }

    private function getLatestCourseVerification($student)
    {
        return CourseVerification::where('studentID', $student->studentID)
            ->with(['lecturer'])
            ->latest('applicationDate')
            ->latest('courseVerificationID')
            ->first();
    }

    private function getPlacementApplicationStats($student): array
    {
        $applications = PlacementApplication::where('studentID', $student->studentID)
            ->orderBy('applicationDate', 'desc')
            ->get();

        return [
            'total' => $applications->count(),
            'pending' => $applications->filter(fn($app) => $app->overall_status === 'Pending')->count(),
            'approved' => $applications->filter(fn($app) => $app->overall_status === 'Approved')->count(),
            'rejected' => $applications->filter(fn($app) => $app->overall_status === 'Rejected')->count(),
            'accepted' => $applications->where('studentAcceptance', 'Accepted')->count(),
        ];
    }

    private function getDeferRequestStats($student): array
    {
        $requests = RequestDefer::where('studentID', $student->studentID)->get();

        return [
            'total' => $requests->count(),
            'pending' => $requests->filter(fn($req) => $req->overall_status === 'Pending')->count(),
            'approved' => $requests->filter(fn($req) => $req->overall_status === 'Approved')->count(),
            'rejected' => $requests->filter(fn($req) => $req->overall_status === 'Rejected')->count(),
        ];
    }

    private function getChangeRequestStats($student): array
    {
        $changeRequests = RequestJustification::whereHas('placementApplication', function ($query) use ($student) {
            $query->where('studentID', $student->studentID);
        })->get();

        return [
            'total' => $changeRequests->count(),
            'pending' => $changeRequests->filter(fn($req) => $req->overall_status === 'Pending')->count(),
            'approved' => $changeRequests->filter(fn($req) => $req->overall_status === 'Approved')->count(),
            'rejected' => $changeRequests->filter(fn($req) => $req->overall_status === 'Rejected')->count(),
        ];
    }

    private function getSupervisorAssignment($student)
    {
        return SupervisorAssignment::where('studentID', $student->studentID)
            ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
            ->with(['supervisor.user', 'assignedBy.user'])
            ->first();
    }

    private function getRecentActivities($student): Collection
    {
        $activities = collect([]);

        // Latest course verification update
        $latestVerification = CourseVerification::where('studentID', $student->studentID)
            ->latest('updated_at')
            ->first();

        if ($latestVerification) {
            $activities->push([
                'type' => 'course_verification',
                'title' => 'Course Verification Updated',
                'description' => "Status changed to " . ucfirst($latestVerification->status),
                'date' => $latestVerification->updated_at,
                'status' => $latestVerification->status,
                'link' => route('student.courseVerification'),
            ]);
        }

        // Latest placement application update
        $latestApplication = PlacementApplication::where('studentID', $student->studentID)
            ->latest('updated_at')
            ->first();

        if ($latestApplication) {
            $activities->push([
                'type' => 'placement_application',
                'title' => 'Placement Application Updated',
                'description' => "Application for {$latestApplication->companyName} - Status: {$latestApplication->overall_status}",
                'date' => $latestApplication->updated_at,
                'status' => $latestApplication->overall_status,
                'link' => route('student.placementApplications'),
            ]);
        }

        // Latest defer request update
        $latestDefer = RequestDefer::where('studentID', $student->studentID)
            ->latest('updated_at')
            ->first();

        if ($latestDefer) {
            $activities->push([
                'type' => 'defer_request',
                'title' => 'Defer Request Updated',
                'description' => "Status changed to " . ucfirst($latestDefer->overall_status),
                'date' => $latestDefer->updated_at,
                'status' => $latestDefer->overall_status,
                'link' => route('student.requestDefer'),
            ]);
        }

        // Supervisor assignment notification
        $supervisorAssignment = $this->getSupervisorAssignment($student);
        if ($supervisorAssignment && $supervisorAssignment->assigned_at) {
            $activities->push([
                'type' => 'supervisor_assignment',
                'title' => 'Supervisor Assigned',
                'description' => "Supervisor: {$supervisorAssignment->supervisor->user->name}",
                'date' => $supervisorAssignment->assigned_at,
                'status' => 'assigned',
                'link' => null,
            ]);
        }

        // Sort by date (most recent first) and take top 5
        return $activities->sortByDesc('date')->take(5);
    }

    private function getLatestPlacementApplication($student)
    {
        return PlacementApplication::where('studentID', $student->studentID)
            ->with(['committee.user', 'coordinator.user'])
            ->latest('applicationDate')
            ->first();
    }

    private function getEmptyStats(): array
    {
        return [
            'courseVerification' => ['pending' => 0, 'approved' => 0, 'rejected' => 0],
            'placementApplications' => ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0],
            'deferRequests' => ['pending' => 0, 'approved' => 0],
            'changeRequests' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0],
            'hasSupervisor' => false,
        ];
    }
}

