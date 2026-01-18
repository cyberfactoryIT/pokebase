<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $stripeInvoice;

    public function __construct($stripeInvoice)
    {
        $this->stripeInvoice = $stripeInvoice;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->stripeInvoice->amount_due / 100, 2);
        $currency = strtoupper($this->stripeInvoice->currency);

        return (new MailMessage)
            ->error()
            ->subject(__('emails.payment_failed.subject'))
            ->greeting(__('emails.payment_failed.greeting', ['name' => $notifiable->name]))
            ->line(__('emails.payment_failed.intro'))
            ->line(__('emails.payment_failed.amount', [
                'amount' => $amount,
                'currency' => $currency
            ]))
            ->line(__('emails.payment_failed.action_required'))
            ->action(__('emails.payment_failed.update_payment'), route('billing.index'))
            ->line(__('emails.payment_failed.warning'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->stripeInvoice->id,
            'amount_due' => $this->stripeInvoice->amount_due,
        ];
    }
}
