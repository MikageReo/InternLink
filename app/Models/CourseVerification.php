<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseVerification extends Model
{
    protected $primaryKey = 'courseVerificationID';

    protected $fillable = [
        'currentCredit',
        'submittedFile',
        'status',
        'applicationDate',
        'lecturerID',
        'studentID',
    ];

    protected $casts = [
        'applicationDate' => 'date',
    ];

    /**
     * Get the student that owns the course verification.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    /**
     * Get the lecturer assigned to this course verification.
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturerID', 'lecturerID');
    }
}
