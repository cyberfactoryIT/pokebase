# Subscription Renewal System

## Features Implemented

### 1. Next Renewal Date Display
- ✅ Shows next renewal date on **checkout success page** after payment
- ✅ Shows next renewal date in **billing page** with active subscription
- ✅ Shows countdown: "Renews in X days", "Renews tomorrow", "Renews today"
- ✅ Translations in English, Italian, and Danish

### 2. Email Reminder System
- ✅ Automatic email reminders sent before subscription renewal
- ✅ Customizable days ahead (default: 7 days)
- ✅ Includes plan details and renewal amount
- ✅ Link to manage subscription
- ✅ Multi-language support

## Usage

### Testing the Renewal Reminder Command

Send reminders for subscriptions renewing in 7 days (default):
```bash
php artisan subscriptions:send-renewal-reminders
```

Send reminders for subscriptions renewing in 3 days:
```bash
php artisan subscriptions:send-renewal-reminders --days=3
```

Send reminders for subscriptions renewing tomorrow:
```bash
php artisan subscriptions:send-renewal-reminders --days=1
```

### Scheduling Automatic Reminders

Add to `app/Console/Kernel.php` in the `schedule()` method:

```php
protected function schedule(Schedule $schedule)
{
    // Send renewal reminder 7 days before renewal
    $schedule->command('subscriptions:send-renewal-reminders --days=7')
             ->dailyAt('09:00')
             ->timezone('Europe/Copenhagen');
    
    // Optional: Send another reminder 3 days before renewal
    $schedule->command('subscriptions:send-renewal-reminders --days=3')
             ->dailyAt('09:00')
             ->timezone('Europe/Copenhagen');
    
    // Optional: Send final reminder 1 day before renewal
    $schedule->command('subscriptions:send-renewal-reminders --days=1')
             ->dailyAt('09:00')
             ->timezone('Europe/Copenhagen');
}
```

### Running the Scheduler

#### Development (local)
```bash
php artisan schedule:work
```

#### Production (server cron job)
Add to your server's crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Email Content

The renewal reminder email includes:
- Friendly greeting with user's name
- Days until renewal and exact renewal date
- Plan name and billing period
- Renewal amount in DKK
- Link to manage/cancel subscription
- Cancellation instructions

## Renewal Date Calculation

When a payment is processed:
- **Monthly**: `renew_date = now() + 1 month`
- **Yearly**: `renew_date = now() + 1 year`

The `renew_date` is automatically set in `CheckoutController@processPayment()`.

## Translation Files

Email translations are in:
- `resources/lang/en/emails.php`
- `resources/lang/it/emails.php`
- `resources/lang/da/emails.php`

Subscription translations are in:
- `resources/lang/en/subscriptions.php` (renews_today, renews_tomorrow, renews_in_days)
- `resources/lang/it/subscriptions.php`
- `resources/lang/da/subscriptions.php`

Checkout translations are in:
- `resources/lang/en/checkout.php` (next_renewal)
- `resources/lang/it/checkout.php`
- `resources/lang/da/checkout.php`

## Testing Tips

### Create a test subscription with near renewal date
```php
php artisan tinker

$org = \App\Models\Organization::find(1);
$org->renew_date = now()->addDays(7); // or 3, or 1
$org->subscription_cancelled = 0;
$org->save();

exit
```

Then run:
```bash
php artisan subscriptions:send-renewal-reminders --days=7
```

## Notifications Queue

The reminder emails are queued for better performance. Make sure your queue worker is running:

```bash
php artisan queue:work
```

Or for development:
```bash
php artisan queue:listen
```

## Monitoring

Check sent emails in:
- Mail logs (if using log driver)
- Mailtrap/Mailhog (development)
- Your email service dashboard (production)

## Customization

### Change reminder days
Edit the command call in the schedule or when running manually.

### Change email template
Edit `app/Notifications/SubscriptionRenewalReminder.php`.

### Change who receives reminders
Edit `app/Console/Commands/SendRenewalReminders.php` line 57-63 to change user selection logic.

## Troubleshooting

**No emails sent?**
- Check queue is running: `php artisan queue:work`
- Check mail configuration in `.env`
- Check renewal dates: `SELECT id, name, renew_date FROM organizations WHERE renew_date IS NOT NULL`

**Wrong renewal date?**
- Check timezone in config/app.php
- Verify date calculation in CheckoutController

**Emails in wrong language?**
- User's locale is determined by `App::getLocale()`
- Check user preferences or browser language detection
