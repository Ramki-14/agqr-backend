<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    protected $token;
    protected $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('http://localhost:5173/reset-password?token=' . $this->token . '&email=' . urlencode($this->email));

        return (new MailMessage)
        ->subject('Reset Password Notification')
        ->greeting('Hello!')
        ->line('You are receiving this email because we received a password reset request for your account.')
        ->action('Reset Password', $url)
        ->line('If you did not request a password reset, no further action is required.')
        ->view('emails.reset-password', [
            'url' => $url,
            'logo' => asset('http://3.89.31.188:8080/storage/images/Logo.jpeg')
        ]);
    }
}
