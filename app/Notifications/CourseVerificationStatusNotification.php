<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\CourseVerification;

class CourseVerificationStatusNotification extends Notification
{
    use Queueable;

    public $courseVerification;
    public $status;
    public $studentName;
    public $lecturerName;

    /**
     * Create a new notification instance.
     */
    public function __construct(CourseVerification $courseVerification)
    {
        $this->courseVerification = $courseVerification;
        $this->status = $courseVerification->status;
        $this->studentName = $courseVerification->student->user->name ?? 'Student';
        $this->lecturerName = $courseVerification->lecturer->user->name ?? 'Academic Office';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->status === 'approved'
            ? 'Course Verification Approved - Congratulations!'
            : 'Course Verification Status Update';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.course-verification-status', [
                'courseVerification' => $this->courseVerification,
                'status' => $this->status,
                'studentName' => $this->studentName,
                'lecturerName' => $this->lecturerName,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'course_verification_id' => $this->courseVerification->courseVerificationID,
            'status' => $this->status,
            'student_name' => $this->studentName,
            'lecturer_name' => $this->lecturerName,
            'application_date' => $this->courseVerification->applicationDate,
            'current_credit' => $this->courseVerification->currentCredit,
            'remarks' => $this->courseVerification->remarks,
        ];
    }
}
