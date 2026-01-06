<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RequestDefer extends Model
{
    protected $primaryKey = 'deferID';

    protected $fillable = [
        'reason',
        'applicationDate',
        'remarks',
        'studentID',
        'committeeID',
        'coordinatorID',
        'committeeStatus',
        'coordinatorStatus',
    ];

    protected $casts = [
        'applicationDate' => 'date',
    ];

    /**
     * Check if student has an approved defer request
     */
    public static function hasApprovedDeferRequest($studentID): bool
    {
        return self::where('studentID', $studentID)
            ->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved')
            ->exists();
    }

    /**
     * Get the student who submitted this defer request.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    /**
     * Get the committee lecturer assigned to this request.
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'committeeID', 'lecturerID');
    }

    /**
     * Get the coordinator lecturer assigned to this request.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'coordinatorID', 'lecturerID');
    }

    /**
     * Get all files for this defer request.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Get the overall status of the defer request.
     */
    public function getOverallStatusAttribute(): string
    {
        if ($this->committeeStatus === 'Rejected' || $this->coordinatorStatus === 'Rejected') {
            return 'Rejected';
        }

        if ($this->committeeStatus === 'Approved' && $this->coordinatorStatus === 'Approved') {
            return 'Approved';
        }

        return 'Pending';
    }

    /**
     * Check if the defer request can be accepted.
     */
    public function getCanAcceptAttribute(): bool
    {
        return $this->overall_status === 'Approved';
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent($query, $studentID)
    {
        return $query->where('studentID', $studentID);
    }

    /**
     * Scope to filter pending requests.
     */
    public function scopePending($query)
    {
        return $query->where(function ($q) {
            $q->where('committeeStatus', 'Pending')
                ->orWhere('coordinatorStatus', 'Pending');
        });
    }

    /**
     * Scope to filter approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved');
    }
}
