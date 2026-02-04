#!/bin/bash

# Daily One Piece Complete Sync Pipeline
# Downloads card data from RapidAPI + prices from CardMarket S3
# Run via cron: 0 4 * * * /path/to/sync-onepiece-daily.sh

set -e  # Exit on error

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Detect PHP command ($PHP_CMD on server, php on local)
PHP_CMD="php"
if command -v $PHP_CMD &> /dev/null; then
    PHP_CMD="$PHP_CMD"
fi

LOG_FILE="storage/logs/onepiece-sync-$(date +%Y%m%d).log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "========================================="
log "Starting One Piece Daily Complete Sync"
log "========================================="

# Step 1: Import One Piece cards from RapidAPI (CMAPI)
log "Step 1: Importing One Piece card data from RapidAPI..."
if $PHP_CMD artisan cmapi:import --game=onepiece >> "$LOG_FILE" 2>&1; then
    log "✅ RapidAPI import complete"
else
    log "❌ RapidAPI import failed"
    exit 1
fi

# Step 2: Download products and prices from CardMarket S3
log ""
log "Step 2: Downloading CardMarket data from S3..."
if $PHP_CMD artisan cardmarket:sync-prices --game=onepiece >> "$LOG_FILE" 2>&1; then
    log "✅ CardMarket S3 download complete"
else
    log "❌ CardMarket S3 download failed"
    exit 1
fi

# Step 3: Promote CardMarket staging to production (update price history)
log ""
log "Step 3: Promoting CardMarket prices to production..."
if $PHP_CMD artisan cardmarket:sync-prices --game=onepiece --promote >> "$LOG_FILE" 2>&1; then
    log "✅ Price promotion complete"
else
    log "❌ Price promotion failed"
    exit 1
fi

# Step 4: Clean old CardMarket staging data (>7 days)
log ""
log "Step 4: Cleaning old staging data..."
if $PHP_CMD artisan cardmarket:sync-prices --game=onepiece --clean >> "$LOG_FILE" 2>&1; then
    log "✅ Staging cleanup complete"
else
    log "⚠️  Staging cleanup failed (non-critical)"
fi

# Step 5: Generate daily statistics
log ""
log "Step 5: Generating statistics..."
CARD_COUNT=$($PHP_CMD artisan tinker --execute="echo App\Models\Cmapi\CmapiCard::where('game', 'onepiece')->count();" 2>/dev/null | tail -1)
PRICE_HISTORY_COUNT=$($PHP_CMD artisan tinker --execute="echo DB::table('cmapi_price_history')->whereIn('cmapi_card_id', App\Models\Cmapi\CmapiCard::where('game', 'onepiece')->pluck('id'))->count();" 2>/dev/null | tail -1)

log "📊 Statistics:"
log "   Total One Piece cards: ${CARD_COUNT}"
log "   Price history records: ${PRICE_HISTORY_COUNT}"

log ""
log "========================================="
log "Daily One Piece Sync Complete!"
log "========================================="
log "Full log: $LOG_FILE"

# Optional: Send notification (uncomment if you have notification setup)
# php artisan app:send-admin-notification "One Piece daily sync completed: ${CARD_COUNT} cards, ${PRICE_HISTORY_COUNT} price records"

exit 0
