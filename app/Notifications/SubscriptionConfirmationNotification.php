<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $organization;
    public $invoice;
    public $renewDate;
    public $billingPeriod;

    /**
     * Create a new notification instance.
     */
    public function __construct(Organization $organization, Invoice $invoice, Carbon $renewDate, string $billingPeriod)
    {
        $this->organization = $organization;
        $this->invoice = $invoice;
        $this->renewDate = $renewDate;
        $this->billingPeriod = $billingPeriod;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Conferma Sottoscrizione - ' . $this->organization->name)
            ->greeting('Grazie del tuo pagamento!')
            ->line('La tua sottoscrizione è già attiva.')
            ->line('Prossimo rinnovo: ' . $this->renewDate->format('d/m/Y'))
            ->line('')
            ->line('---')
            ->line('')
            ->line('**Riepilogo Fattura**')
            ->line('')
            ->line('Fattura N.: ' . $this->invoice->number)
            ->line('Data: ' . $this->invoice->issued_at->format('d/m/Y'))
            ->line('')
            ->line('**Dati Emittente:**')
            ->line(config('invoice.biller_name'))
            ->line(config('invoice.biller_address'))
            ->line('P.IVA: ' . config('invoice.biller_vat'))
            ->line('Email: ' . config('invoice.biller_email'))
            ->line('Tel: ' . config('invoice.biller_phone'))
            ->line('')
            ->line('**Dati Cliente:**')
            ->line($this->organization->name)
            ->line($this->organization->company ?? '')
            ->line($this->organization->address_line1)
            ->line($this->organization->city . ', ' . $this->organization->country)
            ->line('P.IVA: ' . ($this->organization->vat_number ?? 'N/A'))
            ->line('')
            ->line('**Dettagli:**')
            ->lineIf($this->invoice->items->count() > 0, 
                $this->invoice->items->first()->description . ' - ' . 
                number_format($this->invoice->items->first()->unit_price_cents / 100, 2) . ' DKK'
            )
            ->line('')
            ->line('Subtotale: ' . number_format($this->invoice->subtotal_cents / 100, 2) . ' DKK')
            ->line('IVA (25%): ' . number_format($this->invoice->tax_cents / 100, 2) . ' DKK')
            ->line('**Totale: ' . number_format($this->invoice->total_cents / 100, 2) . ' DKK**')
            ->line('')
            ->line('Pagamento effettuato con carta di credito.')
            ->line('')
            ->action('Vai al Dashboard', url('/dashboard'))
            ->line('Grazie per aver scelto il nostro servizio!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'organization_id' => $this->organization->id,
            'invoice_id' => $this->invoice->id,
            'renew_date' => $this->renewDate->toDateString(),
        ];
    }
}
