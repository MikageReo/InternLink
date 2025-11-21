<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Lecturer;
use App\Models\SupervisorAssignment;
use App\Models\PlacementApplication;
use App\Mail\SupervisorAssignmentNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class SupervisorAssignmentService
{
    protected GeocodingService $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Assign a supervisor to a student
     *
     * @param string $studentID
     * @param string $supervisorID
     * @param string|null $assignedBy Coordinator lecturer ID (defaults to current user)
     * @param string|null $notes Assignment notes
     * @param bool $quotaOverride Override quota limit
     * @param string|null $overrideReason Reason for quota override
     * @return SupervisorAssignment
     * @throws \Exception
     */
    public function assignSupervisor(
        string $studentID,
        string $supervisorID,
        ?string $assignedBy = null,
        ?string $notes = null,
        bool $quotaOverride = false,
        ?string $overrideReason = null
    ): SupervisorAssignment {
        // Validate student
        $student = Student::findOrFail($studentID);

        // Check if student has accepted placement
        if (!$student->hasAcceptedPlacement()) {
            throw new \Exception('Student must have an accepted placement application before assigning a supervisor.');
        }

        // Check if student already has an active assignment
        $existingAssignment = SupervisorAssignment::where('studentID', $studentID)
            ->where('status', SupervisorAssignment::STATUS_ASSIGNED)
            ->first();

        if ($existingAssignment) {
            throw new \Exception('Student already has an active supervisor assignment.');
        }

        // Validate supervisor
        $supervisor = Lecturer::findOrFail($supervisorID);

        if (!$supervisor->isSupervisorFaculty) {
            throw new \Exception('Selected lecturer is not a supervisor.');
        }

        if ($supervisor->status !== Lecturer::STATUS_ACTIVE) {
            throw new \Exception('Supervisor is not available (inactive status).');
        }

        // Check if supervisor holds an administrative position
        if ($supervisor->hasAdministrativePosition()) {
            throw new \Exception('Supervisor cannot be assigned as they hold an administrative position (' . $supervisor->position . ').');
        }

        // Check quota unless override
        if (!$quotaOverride && !$supervisor->hasAvailableQuota()) {
            throw new \Exception('Supervisor has reached their quota limit. Use quota override if necessary.');
        }

        // Note: Department restriction removed - supervisors can supervise any student regardless of department

        // Get assigned by (coordinator)
        if (!$assignedBy) {
            $coordinator = Auth::user()->lecturer;
            if (!$coordinator || !$coordinator->isCoordinator) {
                throw new \Exception('Only coordinators can assign supervisors.');
            }
            $assignedBy = $coordinator->lecturerID;
        } else {
            $coordinator = Lecturer::findOrFail($assignedBy);
            if (!$coordinator->isCoordinator) {
                throw new \Exception('Only coordinators can assign supervisors.');
            }
        }

        // Calculate distance
        $distance = null;
        $placement = $student->acceptedPlacementApplication;

        if ($placement && $placement->has_geocoding && $supervisor->has_geocoding) {
            $distance = $this->geocodingService->calculateDistance(
                (float) $placement->companyLatitude,
                (float) $placement->companyLongitude,
                (float) $supervisor->latitude,
                (float) $supervisor->longitude
            );
        } elseif ($student->has_geocoding && $supervisor->has_geocoding) {
            $distance = $this->geocodingService->calculateDistance(
                (float) $student->latitude,
                (float) $student->longitude,
                (float) $supervisor->latitude,
                (float) $supervisor->longitude
            );
        }

        // Create assignment
        DB::beginTransaction();
        try {
            $assignment = SupervisorAssignment::create([
                'studentID' => $studentID,
                'supervisorID' => $supervisorID,
                'assignedBy' => $assignedBy,
                'status' => SupervisorAssignment::STATUS_ASSIGNED,
                'assignment_notes' => $notes,
                'distance_km' => $distance,
                'quota_override' => $quotaOverride,
                'override_reason' => $quotaOverride ? $overrideReason : null,
                'assigned_at' => now(),
            ]);

            // Update supervisor's current assignments count
            $supervisor->increment('current_assignments');

            DB::commit();

            Log::info('Supervisor assigned', [
                'student_id' => $studentID,
                'supervisor_id' => $supervisorID,
                'assigned_by' => $assignedBy,
                'distance_km' => $distance,
                'quota_override' => $quotaOverride,
            ]);

            // Send email notification to student
            try {
                $studentEmail = $student->user->email;
                Mail::to($studentEmail)->send(new SupervisorAssignmentNotification($assignment));
                Log::info('Supervisor assignment notification sent', [
                    'student_email' => $studentEmail,
                    'assignment_id' => $assignment->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send supervisor assignment notification', [
                    'student_email' => $student->user->email ?? 'N/A',
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't throw error to avoid disrupting assignment process
            }

            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign supervisor', [
                'student_id' => $studentID,
                'supervisor_id' => $supervisorID,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Auto-assign the nearest available supervisor to a student
     *
     * @param string $studentID
     * @param string|null $assignedBy
     * @param int $limit Number of supervisors to consider
     * @return SupervisorAssignment|null
     */
    public function autoAssignNearestSupervisor(
        string $studentID,
        ?string $assignedBy = null,
        int $limit = 10
    ): ?SupervisorAssignment {
        $student = Student::findOrFail($studentID);

        // Get nearest supervisors
        $supervisors = $this->geocodingService->findNearestSupervisorsForStudent(
            $student,
            $limit,
            false // Don't include full quota for auto-assignment
        );

        if ($supervisors->isEmpty()) {
            Log::warning('No available supervisors found for auto-assignment', [
                'student_id' => $studentID,
            ]);
            return null;
        }

        // Try to assign the nearest supervisor
        foreach ($supervisors as $supervisor) {
            try {
                return $this->assignSupervisor(
                    $studentID,
                    $supervisor->lecturerID,
                    $assignedBy,
                    'Auto-assigned based on nearest available supervisor.',
                    false,
                    null
                );
            } catch (\Exception $e) {
                // Try next supervisor
                Log::warning('Failed to auto-assign supervisor', [
                    'student_id' => $studentID,
                    'supervisor_id' => $supervisor->lecturerID,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return null;
    }

    /**
     * Get recommended supervisors for a student
     *
     * @param string $studentID
     * @param int $limit
     * @param bool $includeFullQuota
     * @return \Illuminate\Support\Collection
     */
    public function getRecommendedSupervisors(
        string $studentID,
        int $limit = 10,
        bool $includeFullQuota = false
    ): \Illuminate\Support\Collection {
        $student = Student::findOrFail($studentID);

        return $this->geocodingService->findNearestSupervisorsForStudent(
            $student,
            $limit,
            $includeFullQuota
        );
    }

    /**
     * Complete a supervisor assignment
     *
     * @param int $assignmentID
     * @return SupervisorAssignment
     */
    public function completeAssignment(int $assignmentID): SupervisorAssignment
    {
        $assignment = SupervisorAssignment::findOrFail($assignmentID);

        DB::beginTransaction();
        try {
            $assignment->update([
                'status' => SupervisorAssignment::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            // Decrease supervisor's current assignments
            $supervisor = $assignment->supervisor;
            if ($supervisor) {
                $supervisor->decrement('current_assignments');
            }

            DB::commit();

            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel a supervisor assignment
     *
     * @param int $assignmentID
     * @param string|null $reason
     * @return SupervisorAssignment
     */
    public function cancelAssignment(int $assignmentID, ?string $reason = null): SupervisorAssignment
    {
        $assignment = SupervisorAssignment::findOrFail($assignmentID);

        DB::beginTransaction();
        try {
            $assignment->update([
                'status' => SupervisorAssignment::STATUS_CANCELLED,
                'assignment_notes' => $assignment->assignment_notes
                    ? $assignment->assignment_notes . "\n\nCancelled: " . $reason
                    : "Cancelled: " . $reason,
            ]);

            // Decrease supervisor's current assignments
            $supervisor = $assignment->supervisor;
            if ($supervisor) {
                $supervisor->decrement('current_assignments');
            }

            DB::commit();

            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get eligible students (those with accepted placements but no supervisor)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEligibleStudents()
    {
        return Student::whereHas('placementApplications', function ($query) {
            $query->where('studentAcceptance', 'Accepted');
        })
            ->whereDoesntHave('supervisorAssignments', function ($query) {
                $query->where('status', SupervisorAssignment::STATUS_ASSIGNED);
            })
            ->with(['user', 'acceptedPlacementApplication'])
            ->get();
    }
}
