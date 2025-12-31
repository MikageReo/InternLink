<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\CourseVerification;

class CourseVerificationCoordinatorNotification extends Notification
{
    use Queueable;

    public $courseVerification;
    public $studentName;
    public $academicAdvisorName;

    /**
     * Create a new notification instance.
     */
    public function __construct(CourseVerification $courseVerification)
    {
        $this->courseVerification = $courseVerification;
        $this->studentName = $courseVerification->student->user->name ?? 'Student';
        $this->academicAdvisorName = $courseVerification->academicAdvisor->user->name ?? 'Academic Advisor';
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
        return (new MailMessage)
            ->subject('Course Verification Application Ready for Review')
            ->view('emails.course-verification-coordinator', [
                'courseVerification' => $this->courseVerification,
                'studentName' => $this->studentName,
                'academicAdvisorName' => $this->academicAdvisorName,
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
            'student_name' => $this->studentName,
            'academic_advisor_name' => $this->academicAdvisorName,
            'application_date' => $this->courseVerification->applicationDate,
            'current_credit' => $this->courseVerification->currentCredit,
        ];
    }
}

