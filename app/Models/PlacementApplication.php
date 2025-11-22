<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PlacementApplication extends Model
{
    protected $primaryKey = 'applicationID';

    protected $fillable = [
        'companyName',
        'companyAddressLine',
        'companyCity',
        'companyPostcode',
        'companyState',
        'companyCountry',
        'companyLatitude',
        'companyLongitude',
        'companyEmail',
        'companyNumber',
        'industrySupervisorName',
        'industrySupervisorContact',
        'industrySupervisorEmail',
        'allowance',
        'position',
        'jobscope',
        'methodOfWork',
        'startDate',
        'endDate',
        'applicationDate',
        'remarks',
        'studentID',
        'committeeID',
        'coordinatorID',
        'committeeStatus',
        'coordinatorStatus',
        'studentAcceptance',
        'applyCount',
    ];

    protected $casts = [
        'startDate' => 'date',
        'endDate' => 'date',
        'applicationDate' => 'date',
        'allowance' => 'decimal:2',
        'companyLatitude' => 'decimal:8',
        'companyLongitude' => 'decimal:8',
    ];

    /**
     * Get the student that owns the placement application.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    /**
     * Get the committee lecturer assigned to this application.
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'committeeID', 'lecturerID');
    }

    /**
     * Get the coordinator lecturer assigned to this application.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'coordinatorID', 'lecturerID');
    }

    /**
     * Get all files for this placement application.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Get all change request justifications for this application.
     */
    public function changeRequests()
    {
        return $this->hasMany(RequestJustification::class, 'applicationID', 'applicationID');
    }

    /**
     * Get overall status based on committee and coordinator approval.
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
     * Check if student can accept this application.
     */
    public function getCanAcceptAttribute(): bool
    {
        return $this->overall_status === 'Approved' && $this->studentAcceptance === null;
    }

    /**
     * Get the display text for method of work.
     */
    public function getMethodOfWorkDisplayAttribute(): string
    {
        $methods = [
            'WFO' => 'Work From Office',
            'WOS' => 'Work On Site',
            'WOC' => 'Work On Campus',
            'WFH' => 'Work From Home',
            'WFO & WFH' => 'Hybrid (Office & Home)',
        ];

        return $methods[$this->methodOfWork] ?? $this->methodOfWork;
    }

    /**
     * Scope to get applications for a specific student.
     */
    public function scopeForStudent($query, $studentID)
    {
        return $query->where('studentID', $studentID);
    }

    /**
     * Scope to get pending applications.
     */
    public function scopePending($query)
    {
        return $query->where(function ($q) {
            $q->where('committeeStatus', 'Pending')
                ->orWhere('coordinatorStatus', 'Pending');
        });
    }

    /**
     * Scope to get approved applications.
     */
    public function scopeApproved($query)
    {
        return $query->where('committeeStatus', 'Approved')
            ->where('coordinatorStatus', 'Approved');
    }

    /**
     * Get the full company address as a formatted string
     */
    public function getCompanyFullAddressAttribute(): string
    {
        $addressParts = array_filter([
            $this->companyAddressLine,
            $this->companyCity,
            $this->companyPostcode,
            $this->companyState,
            $this->companyCountry
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Check if the company has geocoding coordinates
     */
    public function getHasGeocodingAttribute(): bool
    {
        return !is_null($this->companyLatitude) && !is_null($this->companyLongitude);
    }

    /**
     * Get the company coordinates as an array
     */
    public function getCompanyCoordinatesAttribute(): ?array
    {
        if ($this->has_geocoding) {
            return [
                'latitude' => $this->companyLatitude,
                'longitude' => $this->companyLongitude
            ];
        }

        return null;
    }
}
