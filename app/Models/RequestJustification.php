<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RequestJustification extends Model
{
    protected $primaryKey = 'justificationID';

    protected $fillable = [
        'applicationID',
        'reason',
        'requestDate',
        'committeeID',
        'coordinatorID',
        'committeeStatus',
        'coordinatorStatus',
        'remarks',
        'decisionDate',
    ];

    protected $casts = [
        'requestDate' => 'date',
        'decisionDate' => 'date',
    ];

    /**
     * Get the placement application this justification belongs to.
     */
    public function placementApplication(): BelongsTo
    {
        return $this->belongsTo(PlacementApplication::class, 'applicationID', 'applicationID');
    }

    /**
     * Get the committee lecturer assigned to this justification.
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'committeeID', 'lecturerID');
    }

    /**
     * Get the coordinator lecturer assigned to this justification.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'coordinatorID', 'lecturerID');
    }

    /**
     * Get all files for this justification request.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Get the overall status of the justification request.
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
     * Check if the justification request can be processed for new placement application.
     */
    public function getCanSubmitNewApplicationAttribute(): bool
    {
        return $this->overall_status === 'Approved';
    }

    /**
     * Scope to filter by placement application.
     */
    public function scopeForApplication($query, $applicationID)
    {
        return $query->where('applicationID', $applicationID);
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

    /**
     * Get the student through the placement application.
     */
    public function getStudentAttribute()
    {
        return $this->placementApplication->student ?? null;
    }
}
