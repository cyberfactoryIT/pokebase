#!/bin/bash

# Daily CardMarket Price Sync Pipeline
# Run this via cron: 0 2 * * * /path/to/sync-cardmarket-daily.sh

set -e  # Exit on error

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

LOG_FILE="storage/logs/cardmarket-sync-$(date +%Y%m%d).log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "========================================="
log "Starting CardMarket Daily Price Sync"
log "========================================="

# Step 1: Import Lorcana cards from tcgcsv (if needed)
log "Step 1: Importing Lorcana cards via tcgcsv..."
if php artisan tcgcsv:import --game=lorcana >> "$LOG_FILE" 2>&1; then
    log "✅ Lorcana import complete"
else
    log "⚠️  Lorcana import failed or skipped"
fi

# Step 2: Download from S3 and sync to staging (no API rate limits!)
log ""
log "Step 2: Downloading CardMarket data from S3..."
if php artisan cardmarket:sync-prices --game=lorcana >> "$LOG_FILE" 2>&1; then
    log "✅ S3 download and staging import complete"
else
    log "❌ S3 download failed"
    exit 1
fi

# Step 3: Promote staging to production with history
log ""
log "Step 3: Promoting staging data to production..."
if php artisan cardmarket:sync-prices --game=lorcana --promote >> "$LOG_FILE" 2>&1; then
    log "✅ Promotion to production complete"
else
    log "❌ Promotion failed"
    exit 1
fi

# Step 4: Clean old staging data (keep last 7 days)
log ""
log "Step 4: Cleaning old staging data..."
if php artisan cardmarket:sync-prices --game=lorcana --clean >> "$LOG_FILE" 2>&1; then
    log "✅ Staging cleanup complete"
else
    log "⚠️  Staging cleanup failed"
fi

# Step 5: (Optional) Run for One Piece if needed
# log ""
# log "Step 5: Processing One Piece..."
# php artisan tcgcsv:import --game=onepiece >> "$LOG_FILE" 2>&1
# php artisan cardmarket:sync-prices --game=onepiece --promote --clean >> "$LOG_FILE" 2>&1

log ""
log "========================================="
log "Daily CardMarket Sync Complete!"
log "========================================="
log "Log file: $LOG_FILE"

# Send notification (optional - uncomment if you have notification setup)
# php artisan app:send-admin-notification "CardMarket daily sync completed successfully"

exit 0
