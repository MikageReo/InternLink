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
        'state',
        'researchGroup',
        'department',
        'semester',
        'year',
        'studentQuota',
        'isAcademicAdvisor',
        'isSupervisorFaculty',
        'isCommittee',
        'isCoordinator',
        'isAdmin',
    ];

    protected $casts = [
        'isAcademicAdvisor'   => 'boolean',
        'isSupervisorFaculty' => 'boolean',
        'isCommittee'         => 'boolean',
        'isCoordinator'       => 'boolean',
        'isAdmin'             => 'boolean',
    ];

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
}
