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
        'nationality',
        'program',
        'semester',
        'year',
        'profilePhoto',
        'resume',
        'status',
        'academicAdvisorID',
        'industrySupervisorName',
    ];

    protected $casts = [
        'isAcademicAdvisor' => 'boolean',
    ];

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
}
