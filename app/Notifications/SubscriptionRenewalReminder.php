<?php

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $organization;
    protected $daysUntilRenewal;

    /**
     * Create a new notification instance.
     */
    public function __construct(Organization $organization, int $daysUntilRenewal)
    {
        $this->organization = $organization;
        $this->daysUntilRenewal = $daysUntilRenewal;
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
        $renewalDate = $this->organization->renew_date->format('F d, Y');
        $planName = $this->organization->pricingPlan->name ?? 'Your plan';
        $amount = $this->organization->billing_period === 'yearly'
            ? number_format($this->organization->pricingPlan->yearly_price_cents / 100, 2)
            : number_format($this->organization->pricingPlan->monthly_price_cents / 100, 2);
        
        $currency = 'DKK';

        return (new MailMessage)
            ->subject(__('emails.renewal_reminder.subject', ['days' => $this->daysUntilRenewal]))
            ->greeting(__('emails.renewal_reminder.greeting', ['name' => $notifiable->name]))
            ->line(__('emails.renewal_reminder.intro', [
                'days' => $this->daysUntilRenewal,
                'date' => $renewalDate
            ]))
            ->line(__('emails.renewal_reminder.plan_details', [
                'plan' => $planName,
                'period' => ucfirst($this->organization->billing_period ?? 'monthly')
            ]))
            ->line(__('emails.renewal_reminder.amount', [
                'amount' => $amount,
                'currency' => $currency
            ]))
            ->action(__('emails.renewal_reminder.manage_subscription'), route('billing.index'))
            ->line(__('emails.renewal_reminder.cancel_info'))
            ->line(__('emails.renewal_reminder.thank_you'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'organization_id' => $this->organization->id,
            'days_until_renewal' => $this->daysUntilRenewal,
            'renewal_date' => $this->organization->renew_date,
        ];
    }
}
