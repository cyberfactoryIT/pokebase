#!/bin/bash

# Pokemon TCG Data Download Script - TEST VERSION (pages 11-20)
# Downloads all sets and cards from Pokemon TCG API

API_KEY="1fec095e-b12d-4c16-b1a7-17d96a10a75c"
BASE_URL="https://api.pokemontcg.io/v2"
STORAGE_DIR="storage/app"
CARDS_DIR="$STORAGE_DIR/pokemon_cards"

# Create cards directory if it doesn't exist
mkdir -p "$CARDS_DIR"

echo "================================================"
echo "Pokemon TCG Data Download - TEST (pages 11-20)"
echo "Started: $(date)"
echo "================================================"

# Download cards (pages 11-20 only)
echo ""
echo "Downloading Pokemon cards (pages 11-20)..."

success_count=0
fail_count=0

for page in {11..20}; do
    retries=0
    max_retries=5
    downloaded=false
    
    while [ $retries -lt $max_retries ] && [ "$downloaded" = false ]; do
        curl -H "X-Api-Key: $API_KEY" \
             "$BASE_URL/cards?page=$page&pageSize=25" \
             -o "$CARDS_DIR/pokemon_cards_page$page.json" \
             --silent --show-error \
             --max-time 30
        
        # Check file size (should be > 100 bytes for valid data)
        file_size=$(wc -c < "$CARDS_DIR/pokemon_cards_page$page.json" 2>/dev/null || echo "0")
        
        # Check if file contains valid data
        if [ $file_size -lt 100 ]; then
            retries=$((retries + 1))
            echo "⚠ Page $page too small ($file_size bytes) - retry $retries/$max_retries"
            sleep 5
        elif grep -q "error" "$CARDS_DIR/pokemon_cards_page$page.json" 2>/dev/null; then
            retries=$((retries + 1))
            echo "⚠ Page $page contains error - retry $retries/$max_retries"
            cat "$CARDS_DIR/pokemon_cards_page$page.json"
            sleep 5
        elif ! grep -q '"data"' "$CARDS_DIR/pokemon_cards_page$page.json" 2>/dev/null; then
            retries=$((retries + 1))
            echo "⚠ Page $page invalid JSON - retry $retries/$max_retries"
            sleep 5
        else
            downloaded=true
            success_count=$((success_count + 1))
            echo "✓ Page $page downloaded ($file_size bytes) - $success_count/10"
        fi
    done
    
    if [ "$downloaded" = false ]; then
        fail_count=$((fail_count + 1))
        echo "✗ Page $page FAILED after $max_retries retries"
        # Remove failed file
        rm -f "$CARDS_DIR/pokemon_cards_page$page.json"
    fi
    
    # Sleep 5 seconds between requests to avoid rate limiting
    sleep 5
done

echo ""
echo "================================================"
echo "Download Summary"
echo "================================================"
echo "Card pages: $success_count successful, $fail_count failed (out of 10)"
echo "Completed: $(date)"
echo "================================================"
