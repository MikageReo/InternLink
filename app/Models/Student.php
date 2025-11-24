<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $primaryKey = 'studentID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'studentID',
        'user_id',
        'phone',
        'address',
        'city',
        'postcode',
        'state',
        'country',
        'latitude',
        'longitude',
        'nationality',
        'program',
        'semester',
        'year',
        'profilePhoto',
        'status',
        'academicAdvisorID',
    ];

    protected $casts = [
        'isAcademicAdvisor' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

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
     * Check if the student has geocoding coordinates
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
     * Get the academic advisor (lecturer) for this student
     */
    public function academicAdvisor(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'academicAdvisorID', 'lecturerID');
    }

    /**
     * Get the user account associated with this student
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the course verifications for this student
     */
    public function courseVerifications(): HasMany
    {
        return $this->hasMany(CourseVerification::class, 'studentID', 'studentID');
    }

    /**
     * Get the placement applications for this student
     */
    public function placementApplications(): HasMany
    {
        return $this->hasMany(PlacementApplication::class, 'studentID', 'studentID');
    }

    /**
     * Get the supervisor assignment for this student
     */
    public function supervisorAssignment(): HasOne
    {
        return $this->hasOne(SupervisorAssignment::class, 'studentID', 'studentID')
            ->where('status', SupervisorAssignment::STATUS_ASSIGNED);
    }

    /**
     * Get all supervisor assignments (including historical)
     */
    public function supervisorAssignments(): HasMany
    {
        return $this->hasMany(SupervisorAssignment::class, 'studentID', 'studentID');
    }

    /**
     * Get supervisor through assignment (helper method)
     * Note: Direct supervisor relationship is not possible due to intermediate table
     * Use $student->supervisorAssignment->supervisor instead
     */
    public function getSupervisorAttribute()
    {
        $assignment = $this->supervisorAssignment;
        return $assignment ? $assignment->supervisor : null;
    }

    /**
     * Check if student has an accepted placement application
     */
    public function hasAcceptedPlacement(): bool
    {
        return $this->placementApplications()
            ->where('studentAcceptance', 'Accepted')
            ->exists();
    }

    /**
     * Get the accepted placement application
     */
    public function acceptedPlacementApplication(): HasOne
    {
        return $this->hasOne(PlacementApplication::class, 'studentID', 'studentID')
            ->where('studentAcceptance', 'Accepted');
    }
}
