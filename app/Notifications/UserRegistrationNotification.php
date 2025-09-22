<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $password;
    public $role;
    public $loginUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($password, $role)
    {
        $this->password = $password;
        $this->role = $role;
        $this->loginUrl = route('login');
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
            ->subject('Welcome to Internlink - Your Account Details')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your account has been successfully created in the Internlink system.')
            ->line('Here are your login credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->line('')
            ->line('Please keep these credentials safe and change your password after your first login.')
            ->action('Login to Your Account', $this->loginUrl)
            ->line('If you have any questions, please contact your system administrator.')
            ->line('Thank you for using Internlink!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'email' => $notifiable->email,
            'role' => $this->role,
            'password_sent' => true,
        ];
    }
}
