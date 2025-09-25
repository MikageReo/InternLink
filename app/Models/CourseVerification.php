<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CourseVerification extends Model
{
    protected $primaryKey = 'courseVerificationID';

    protected $fillable = [
        'currentCredit',
        'status',
        'applicationDate',
        'lecturerID',
        'studentID',
        'remarks',
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

    /**
     * Get all files for this course verification.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Get the first submitted file (for backward compatibility).
     */
    public function getSubmittedFileAttribute()
    {
        $file = $this->files()->first();
        return $file ? $file->file_path : null;
    }

    /**
     * Get the submitted file URL (for backward compatibility).
     */
    public function getSubmittedFileUrlAttribute()
    {
        $file = $this->files()->first();
        return $file ? $file->url : null;
    }
}
