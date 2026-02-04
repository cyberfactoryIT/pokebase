# Cron Jobs Configuration for Lorcana Sync

## Setup Instructions

1. Open crontab editor:
```bash
crontab -e
```

2. Add the following line (runs daily at 2:00 AM):
```bash
0 2 * * * cd /Users/barbaramanighetti/site/GitHub/pokebase && ./sync-lorcana-daily.sh >> /Users/barbaramanighetti/site/GitHub/pokebase/storage/logs/cron-lorcana.log 2>&1
```

3. Save and exit (`:wq` in vim)

4. Verify cron is scheduled:
```bash
crontab -l
```

## Alternative: Laravel Scheduler (Recommended for production)

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Lorcana daily sync - 2:00 AM
    $schedule->exec('cd ' . base_path() . ' && ./sync-lorcana-daily.sh')
        ->dailyAt('02:00')
        ->appendOutputTo(storage_path('logs/lorcana-scheduler.log'));
}
```

Then add single cron entry to run Laravel scheduler:
```bash
* * * * * cd /Users/barbaramanighetti/site/GitHub/pokebase && php artisan schedule:run >> /dev/null 2>&1
```

## Manual Execution

Test the script manually:
```bash
cd /Users/barbaramanighetti/site/GitHub/pokebase
./sync-lorcana-daily.sh
```

## Log Files

- **Script logs**: `storage/logs/lorcana-sync-YYYYMMDD.log`
- **Cron logs**: `storage/logs/cron-lorcana.log` (if using direct cron)
- **Scheduler logs**: `storage/logs/lorcana-scheduler.log` (if using Laravel scheduler)

## Monitoring

Check last run:
```bash
tail -50 storage/logs/lorcana-sync-$(date +%Y%m%d).log
```

Check cron history:
```bash
grep "Lorcana" storage/logs/cron-lorcana.log | tail -20
```

## Troubleshooting

### Script doesn't run
1. Check cron is active: `ps aux | grep cron`
2. Check script permissions: `ls -l sync-lorcana-daily.sh` (should be `-rwxr-xr-x`)
3. Check crontab syntax: `crontab -l`

### Script fails
1. Run manually to see errors: `./sync-lorcana-daily.sh`
2. Check artisan commands work: `php artisan cmapi:import --game=lorcana --help`
3. Check logs: `tail -100 storage/logs/lorcana-sync-*.log`

## Pipeline Steps

The script executes in order:
1. **RapidAPI Import** (`cmapi:import`) - Downloads card data (names, sets, attributes)
2. **CardMarket S3 Download** (`cardmarket:sync-prices`) - Downloads products + prices to staging
3. **Staging Promotion** (`--promote`) - Validates and moves to production + price history
4. **Staging Cleanup** (`--clean`) - Removes validated data >7 days old
5. **Statistics** - Counts cards and price records

Total runtime: ~2-5 minutes depending on data volume.
