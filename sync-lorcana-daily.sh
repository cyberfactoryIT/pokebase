#!/bin/bash

# Daily Lorcana Complete Sync Pipeline
# Downloads card data from RapidAPI + prices from CardMarket S3
# Run via cron: 0 2 * * * /path/to/sync-lorcana-daily.sh

set -e  # Exit on error

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

LOG_FILE="storage/logs/lorcana-sync-$(date +%Y%m%d).log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "========================================="
log "Starting Lorcana Daily Complete Sync"
log "========================================="

# Step 1: Import Lorcana cards from RapidAPI (CMAPI)
log "Step 1: Importing Lorcana card data from RapidAPI..."
if php artisan cmapi:import --game=lorcana >> "$LOG_FILE" 2>&1; then
    log "✅ RapidAPI import complete"
else
    log "❌ RapidAPI import failed"
    exit 1
fi

# Step 2: Download products and prices from CardMarket S3
log ""
log "Step 2: Downloading CardMarket data from S3..."
if php artisan cardmarket:sync-prices --game=lorcana >> "$LOG_FILE" 2>&1; then
    log "✅ CardMarket S3 download complete"
else
    log "❌ CardMarket S3 download failed"
    exit 1
fi

# Step 3: Promote CardMarket staging to production (update price history)
log ""
log "Step 3: Promoting CardMarket prices to production..."
if php artisan cardmarket:sync-prices --game=lorcana --promote >> "$LOG_FILE" 2>&1; then
    log "✅ Price promotion complete"
else
    log "❌ Price promotion failed"
    exit 1
fi

# Step 4: Clean old CardMarket staging data (>7 days)
log ""
log "Step 4: Cleaning old staging data..."
if php artisan cardmarket:sync-prices --game=lorcana --clean >> "$LOG_FILE" 2>&1; then
    log "✅ Staging cleanup complete"
else
    log "⚠️  Staging cleanup failed (non-critical)"
fi

# Step 5: Generate daily statistics
log ""
log "Step 5: Generating statistics..."
CARD_COUNT=$(php artisan tinker --execute="echo App\Models\Cmapi\CmapiCard::where('game', 'lorcana')->count();" 2>/dev/null | tail -1)
PRICE_HISTORY_COUNT=$(php artisan tinker --execute="echo DB::table('cmapi_price_history')->whereIn('cmapi_card_id', App\Models\Cmapi\CmapiCard::where('game', 'lorcana')->pluck('id'))->count();" 2>/dev/null | tail -1)

log "📊 Statistics:"
log "   Total Lorcana cards: ${CARD_COUNT}"
log "   Price history records: ${PRICE_HISTORY_COUNT}"

log ""
log "========================================="
log "Daily Lorcana Sync Complete!"
log "========================================="
log "Full log: $LOG_FILE"

# Optional: Send notification (uncomment if you have notification setup)
# php artisan app:send-admin-notification "Lorcana daily sync completed: ${CARD_COUNT} cards, ${PRICE_HISTORY_COUNT} price records"

exit 0
