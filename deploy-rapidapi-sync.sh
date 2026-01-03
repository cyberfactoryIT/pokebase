#!/bin/bash

################################################################################
# RapidAPI Sync Deploy Script
# 
# This script safely imports RapidAPI Pokemon cards with:
# - Automatic retry on failure
# - Progress tracking
# - Error logging
# - Automatic mapping and enrichment
################################################################################

set -e  # Exit on error

BATCH_SIZE=50
MAX_RETRIES=3
LOG_DIR="/tmp/rapidapi_sync"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create log directory
mkdir -p $LOG_DIR

echo "🚀 Starting RapidAPI sync - $TIMESTAMP"
echo "📁 Logs: $LOG_DIR/sync_${TIMESTAMP}.log"

# Function to run command with retry
run_with_retry() {
    local cmd=$1
    local description=$2
    local retries=0
    
    echo "▶️  $description"
    
    while [ $retries -lt $MAX_RETRIES ]; do
        if eval "$cmd" >> "$LOG_DIR/sync_${TIMESTAMP}.log" 2>&1; then
            echo "   ✅ Success"
            return 0
        else
            retries=$((retries + 1))
            echo "   ⚠️  Attempt $retries failed, retrying..."
            sleep 5
        fi
    done
    
    echo "   ❌ Failed after $MAX_RETRIES attempts"
    return 1
}

# Check current status
echo ""
echo "📊 Current Status:"
php artisan tinker --execute="
\$total = DB::table('rapidapi_cards')->count();
\$mapped = DB::table('card_mappings')->count();
echo 'Cards: ' . number_format(\$total) . PHP_EOL;
echo 'Mapped: ' . number_format(\$mapped) . PHP_EOL;
"

# Import new cards
echo ""
echo "📥 Importing RapidAPI cards (batch size: $BATCH_SIZE)..."
if run_with_retry "php artisan rapidapi:test pokemon --pages=$BATCH_SIZE --save" "Import batch"; then
    IMPORT_SUCCESS=true
else
    IMPORT_SUCCESS=false
    echo "⚠️  Import failed, continuing with mapping..."
fi

# Map cards
echo ""
echo "🔗 Mapping cards to TCGCSV..."
if run_with_retry "php artisan cards:map" "Card mapping"; then
    MAPPING_SUCCESS=true
else
    MAPPING_SUCCESS=false
fi

# Map TCGdex sets (logos)
echo ""
echo "🎨 Mapping TCGdex sets and logos..."
if run_with_retry "php artisan tcgdex:map-to-tcgcsv" "TCGdex mapping"; then
    TCGDEX_SUCCESS=true
else
    TCGDEX_SUCCESS=false
fi

# Enrich data
echo ""
echo "✨ Enriching TCGCSV data..."
if run_with_retry "php artisan tcgcsv:enrich --all" "Data enrichment"; then
    ENRICH_SUCCESS=true
else
    ENRICH_SUCCESS=false
fi

# Final status
echo ""
echo "📊 Final Status:"
php artisan tinker --execute="
\$total = DB::table('rapidapi_cards')->count();
\$mapped = DB::table('card_mappings')->count();
\$enriched = DB::table('tcgcsv_products')->whereNotNull('hd_image_url')->count();
\$withPrices = DB::table('tcgcsv_products')->whereNotNull('cardmarket_price_eur')->count();

echo '🎴 RapidAPI Cards: ' . number_format(\$total) . PHP_EOL;
echo '🔗 Mapped: ' . number_format(\$mapped) . ' (' . round((\$mapped/\$total)*100, 1) . '%)' . PHP_EOL;
echo '🖼️  HD Images: ' . number_format(\$enriched) . PHP_EOL;
echo '💰 EUR Prices: ' . number_format(\$withPrices) . PHP_EOL;
"

# Summary
echo ""
echo "📋 Summary:"
echo "   Import: $([ "$IMPORT_SUCCESS" = true ] && echo "✅" || echo "❌")"
echo "   Mapping: $([ "$MAPPING_SUCCESS" = true ] && echo "✅" || echo "❌")"
echo "   Enrichment: $([ "$ENRICH_SUCCESS" = true ] && echo "✅" || echo "❌")"
echo ""
echo "📁 Full log: $LOG_DIR/sync_${TIMESTAMP}.log"
echo "✅ Done!"
