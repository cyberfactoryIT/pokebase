<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmailNotification extends Notification
{
    use Queueable;
    protected $token;
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
        $t = Lang::get('mail.verification_email', [], $locale);
        $url = route('verification.custom', ['token' => $this->token]);
        return (new MailMessage)
            ->subject($t['subject'] ?? 'Verify your email')
            ->view('emails.verify-email', [
                'subject' => $t['subject'] ?? 'Verify your email',
                'body' => $t['line_1'] ?? 'Please confirm your email address.',
                'actionUrl' => $url,
                'actionText' => $t['button'] ?? 'Verify Email',
            ]);
    }
}
