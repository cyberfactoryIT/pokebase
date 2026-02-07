# Logging Configuration Guide

## Overview
Laravel uses Monolog for logging. This guide explains the logging configuration options for Basecard.

## Environment Variables

### LOG_CHANNEL
Defines the primary logging driver.

**Options:**
- `stack` - Uses multiple channels defined in `LOG_STACK`
- `daily` - Daily log rotation (recommended for production)
- `single` - Single log file without rotation
- `slack` - Send logs to Slack webhook
- `syslog` - System log

**Recommended:**
- **Development**: `stack` with `LOG_STACK=single`
- **Production**: `daily` or `stack` with `LOG_STACK=daily`

### LOG_STACK
Comma-separated list of channels when using `LOG_CHANNEL=stack`.

**Examples:**
```bash
# Single file (no rotation)
LOG_STACK=single

# Daily rotation
LOG_STACK=daily

# Daily rotation + Slack notifications
LOG_STACK=daily,slack

# Multiple channels for redundancy
LOG_STACK=daily,slack,syslog
```

### LOG_LEVEL
Minimum log level to record.

**Options (from least to most verbose):**
- `emergency` - System is unusable
- `alert` - Action must be taken immediately
- `critical` - Critical conditions
- `error` - Error conditions (default for production)
- `warning` - Warning conditions
- `notice` - Normal but significant
- `info` - Informational messages
- `debug` - Debug-level messages (default for development)

**Recommended:**
- **Development**: `debug`
- **Production**: `error` or `warning`

### LOG_DAILY_DAYS
Number of days to retain daily log files before automatic deletion.

**Default:** `14` days

**Examples:**
```bash
# Keep 7 days
LOG_DAILY_DAYS=7

# Keep 30 days
LOG_DAILY_DAYS=30

# Keep 90 days (compliance/audit requirements)
LOG_DAILY_DAYS=90
```

### LOG_DEPRECATIONS_CHANNEL
Channel for PHP/Laravel deprecation warnings.

**Options:**
- `null` - Disable deprecation logging
- `stack` - Use main log stack
- `single` - Separate file

**Recommended:** `null` in production, `stack` in development

## Configuration Examples

### Development (.env)
```bash
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=stack
LOG_LEVEL=debug
```

**Result:**
- Single file: `storage/logs/laravel.log`
- All messages logged (debug level)
- Deprecations included

### Production (.env)
```bash
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
LOG_DAILY_DAYS=30
```

**Result:**
- Daily files: `storage/logs/laravel-2026-02-07.log`
- Only errors and above
- 30 days retention
- No deprecation warnings

### Production with Slack Alerts (.env)
```bash
LOG_CHANNEL=stack
LOG_STACK=daily,slack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
LOG_DAILY_DAYS=14
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

**Result:**
- Daily files + Slack notifications for errors
- 14 days retention

## Log Files Location

All logs are stored in:
```
storage/logs/
```

### Single Channel
```
storage/logs/laravel.log
```

### Daily Channel
```
storage/logs/laravel-2026-02-07.log
storage/logs/laravel-2026-02-06.log
storage/logs/laravel-2026-02-05.log
...
```

## Log Rotation

### Daily Rotation
- **Automatic**: Laravel creates a new file each day
- **Cleanup**: Files older than `LOG_DAILY_DAYS` are automatically deleted
- **Naming**: `laravel-YYYY-MM-DD.log`

### Manual Rotation (Single File)
If using `single` channel, you need to manually rotate logs:

```bash
# Rotate laravel.log
cd /path/to/app
mv storage/logs/laravel.log storage/logs/laravel-$(date +%Y-%m-%d).log
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

## Monitoring Log Size

### Check log size
```bash
du -sh storage/logs/
du -sh storage/logs/laravel.log
```

### Find large log files
```bash
find storage/logs/ -type f -size +100M
```

### Clean old logs manually
```bash
# Delete logs older than 30 days
find storage/logs/ -name "laravel-*.log" -mtime +30 -delete
```

## Best Practices

1. **Use daily rotation in production**
   - Prevents single file from growing too large
   - Easier to download/analyze specific date ranges
   - Automatic cleanup

2. **Set appropriate log level**
   - Development: `debug` (see everything)
   - Production: `error` or `warning` (reduce noise)

3. **Monitor disk space**
   - Set `LOG_DAILY_DAYS` based on available disk space
   - Consider archiving old logs to S3 or external storage

4. **Sensitive data**
   - Never log passwords, tokens, or credit card numbers
   - Use `Log::withoutContext()` for sensitive operations

5. **Performance**
   - Avoid excessive logging in tight loops
   - Use appropriate log levels
   - Consider async logging for high-traffic apps

## Troubleshooting

### Logs not rotating
**Problem:** Using `LOG_STACK=single` instead of `daily`

**Solution:**
```bash
# Change in .env
LOG_STACK=daily
```

### Logs too large
**Problem:** Log level too verbose or too many days retained

**Solution:**
```bash
# Reduce verbosity
LOG_LEVEL=error

# Reduce retention
LOG_DAILY_DAYS=7
```

### Permission errors
**Problem:** Web server can't write to `storage/logs/`

**Solution:**
```bash
chmod -R 775 storage/logs/
chown -R www-data:www-data storage/logs/
```

### Missing logs
**Problem:** Wrong channel or level configured

**Solution:**
```bash
# Check current config
php artisan config:show logging

# Clear cache
php artisan config:cache
```

## Related Documentation

- [Laravel Logging Documentation](https://laravel.com/docs/11.x/logging)
- [Monolog Documentation](https://github.com/Seldaek/monolog)
- See also: `config/logging.php`
