<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lecturer extends Model
{
    protected $primaryKey = 'lecturerID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'lecturerID',
        'user_id',
        'staffGrade',
        'role',
        'position',
        'address',
        'state',
        'city',
        'postcode',
        'country',
        'latitude',
        'longitude',
        'profile_photo',
        'status',
        'researchGroup',
        'department',
        'preferred_coursework',
        'travel_preference',
        'semester',
        'year',
        'studentQuota',
        'isAcademicAdvisor',
        'isSupervisorFaculty',
        'isCommittee',
        'isCoordinator',
        'isAdmin',
        'supervisor_quota',
        'current_assignments',
    ];

    protected $casts = [
        'isAcademicAdvisor'   => 'boolean',
        'isSupervisorFaculty' => 'boolean',
        'isCommittee'         => 'boolean',
        'isCoordinator'       => 'boolean',
        'isAdmin'             => 'boolean',
        'latitude'            => 'decimal:8',
        'longitude'           => 'decimal:8',
    ];

    // Lecturer status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SABBATICAL_LEAVE = 'sabbatical leave';
    public const STATUS_MATERNITY_LEAVE = 'maternity leave';
    public const STATUS_PILGRIMAGE_LEAVE = 'pilgrimage leave';
    public const STATUS_TRANSFERRED = 'transferred';
    public const STATUS_RESIGNED = 'resigned';
    public const STATUS_INACTIVE = 'in-active';
    public const STATUS_PASSED_AWAY = 'passed away';

    /**
     * Get all available lecturer statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SABBATICAL_LEAVE => 'Sabbatical Leave',
            self::STATUS_MATERNITY_LEAVE => 'Maternity Leave',
            self::STATUS_PILGRIMAGE_LEAVE => 'Pilgrimage Leave',
            self::STATUS_TRANSFERRED => 'Transferred',
            self::STATUS_RESIGNED => 'Resigned',
            self::STATUS_INACTIVE => 'In-Active',
            self::STATUS_PASSED_AWAY => 'Passed Away',
        ];
    }

    /**
     * Get the full address as a formatted string
     */
    public function getFullAddressAttribute(): string
    {
        $addressParts = array_filter([
            $this->address,
            $this->city,
            $this->postcode,
            $this->state,
            $this->country
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Check if the lecturer has geocoding coordinates
     */
    public function getHasGeocodingAttribute(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Get the coordinates as an array
     */
    public function getCoordinatesAttribute(): ?array
    {
        if ($this->has_geocoding) {
            return [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ];
        }

        return null;
    }

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Check if lecturer is available (active status)
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get all students advised by this lecturer
     */
    public function advisedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'academicAdvisorID', 'lecturerID');
    }

    /**
     * Lecturer belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the course verifications reviewed by this lecturer
     */
    public function courseVerifications(): HasMany
    {
        return $this->hasMany(CourseVerification::class, 'lecturerID', 'lecturerID');
    }

    /**
     * Get all supervisor assignments for this lecturer
     */
    public function supervisorAssignments(): HasMany
    {
        return $this->hasMany(SupervisorAssignment::class, 'supervisorID', 'lecturerID');
    }

    /**
     * Get active supervisor assignments for this lecturer
     */
    public function activeSupervisorAssignments(): HasMany
    {
        return $this->hasMany(SupervisorAssignment::class, 'supervisorID', 'lecturerID')
            ->where('status', SupervisorAssignment::STATUS_ASSIGNED);
    }

    /**
     * Get students supervised by this lecturer through assignments
     * Returns a query builder for supervised students
     */
    public function supervisedStudents()
    {
        return Student::whereHas('supervisorAssignments', function ($query) {
            $query->where('supervisorID', $this->lecturerID)
                ->where('status', SupervisorAssignment::STATUS_ASSIGNED);
        });
    }

    /**
     * Check if lecturer has available quota
     */
    public function hasAvailableQuota(): bool
    {
        if (!$this->isSupervisorFaculty) {
            return false;
        }

        return ($this->supervisor_quota - $this->current_assignments) > 0;
    }

    /**
     * Get available quota slots
     */
    public function getAvailableQuotaAttribute(): int
    {
        if (!$this->isSupervisorFaculty) {
            return 0;
        }

        return max(0, $this->supervisor_quota - $this->current_assignments);
    }

    /**
     * Check if lecturer can supervise (is supervisor and active)
     */
    public function canSupervise(): bool
    {
        return $this->isSupervisorFaculty
            && $this->status === self::STATUS_ACTIVE;
    }
}
