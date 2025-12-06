<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;
    public $token;
    public function __construct($token)
    {
        $this->token = $token;
    }
    public function via($notifiable)
    {
        return ['mail'];
    }
    public function toMail($notifiable)
    {
        $locale = app()->getLocale();
        $t = Lang::get('mail.password_reset', [], $locale);
        $url = url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));
        return (new MailMessage)
            ->subject($t['subject'] ?? 'Reset Password Notification')
            ->view('emails.password-reset', [
                'subject' => $t['subject'] ?? 'Reset Password Notification',
                'body' => $t['line_1'] ?? 'You are receiving this email because we received a password reset request for your account.',
                'actionUrl' => $url,
                'actionText' => $t['button'] ?? 'Reset Password',
            ]);
    }
}
