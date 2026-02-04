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

# Detect PHP command (php84 on server, php on local)
PHP_CMD="php"
if command -v php84 &> /dev/null; then
    PHP_CMD="php84"
fi

# Step 1: Import Lorcana cards from RapidAPI (CMAPI)
log "Step 1: Importing Lorcana card data from RapidAPI..."
if $PHP_CMD artisan cmapi:import --game=lorcana >> "$LOG_FILE" 2>&1; then
    log "✅ RapidAPI import complete"
else
    log "❌ RapidAPI import failed"
    exit 1
fi

# Step 2: CardMarket ETL Pipeline (download + import to cardmarket_products_lorcana)
log ""
log "Step 2: Running CardMarket ETL pipeline for Lorcana..."
if $PHP_CMD artisan cardmarket:etl-lorcana >> "$LOG_FILE" 2>&1; then
    log "✅ CardMarket ETL complete"
else
    log "❌ CardMarket ETL failed"
    exit 1
fi

# Step 3: Match Lorcana cards (cmapi_cards → cardmarket_products_lorcana)
log ""
log "Step 3: Matching Lorcana cards with CardMarket products..."
if $PHP_CMD artisan cardmarket:match-lorcana >> "$LOG_FILE" 2>&1; then
    log "✅ Card matching complete"
else
    log "⚠️  Card matching failed (non-critical)"
fi

# Step 4: Sync prices from cardmarket_price_quotes_lorcana to cmapi_cards
log ""
log "Step 4: Syncing prices to cmapi_cards..."
if $PHP_CMD artisan cardmarket:sync-lorcana-prices >> "$LOG_FILE" 2>&1; then
    log "✅ Price sync complete"
else
    log "⚠️  Price sync failed (non-critical)"
fi

# Step 5: Generate daily statistics
log ""
log "Step 5: Generating statistics..."
CARD_COUNT=$($PHP_CMD artisan tinker --execute="echo App\Models\Cmapi\CmapiCard::where('game', 'lorcana')->count();" 2>/dev/null | tail -1)
PRICE_HISTORY_COUNT=$($PHP_CMD artisan tinker --execute="echo DB::table('cmapi_price_history')->whereIn('cmapi_card_id', App\Models\Cmapi\CmapiCard::where('game', 'lorcana')->pluck('id'))->count();" 2>/dev/null | tail -1)

log "📊 Statistics:"
log "   Total Lorcana cards: ${CARD_COUNT}"
log "   Price history records: ${PRICE_HISTORY_COUNT}"

# Step 5: Refresh cached prices for user collections and decks
log ""
log "Step 5: Refreshing cached prices for users..."
if $PHP_CMD artisan prices:refresh-cache --force >> "$LOG_FILE" 2>&1; then
    log "✅ Price cache refreshed"
else
    log "⚠️  Price cache refresh failed (non-critical)"
fi

log ""
log "========================================="
log "Daily Lorcana Sync Complete!"
log "========================================="
log "Full log: $LOG_FILE"

# Optional: Send notification (uncomment if you have notification setup)
# php artisan app:send-admin-notification "Lorcana daily sync completed: ${CARD_COUNT} cards, ${PRICE_HISTORY_COUNT} price records"

exit 0
