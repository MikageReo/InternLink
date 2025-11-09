<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorAssignment extends Model
{
    protected $fillable = [
        'studentID',
        'supervisorID',
        'assignedBy',
        'status',
        'assignment_notes',
        'distance_km',
        'quota_override',
        'override_reason',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'distance_km' => 'decimal:2',
        'quota_override' => 'boolean',
    ];

    // Status constants
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REASSIGNED = 'reassigned';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all available assignment statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REASSIGNED => 'Reassigned',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get the student for this assignment
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    /**
     * Get the supervisor (lecturer) for this assignment
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'supervisorID', 'lecturerID');
    }

    /**
     * Get the coordinator who assigned this supervisor
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'assignedBy', 'lecturerID');
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
     * Check if assignment is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }
}
