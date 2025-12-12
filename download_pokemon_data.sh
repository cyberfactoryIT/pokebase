#!/bin/bash

# Pokemon TCG Data Download Script
# Downloads all sets and cards from Pokemon TCG API

API_KEY="1fec095e-b12d-4c16-b1a7-17d96a10a75c"
BASE_URL="https://api.pokemontcg.io/v2"
STORAGE_DIR="storage/app"
CARDS_DIR="$STORAGE_DIR/pokemon_cards"

# Create cards directory if it doesn't exist
mkdir -p "$CARDS_DIR"

echo "================================================"
echo "Pokemon TCG Data Download"
echo "Started: $(date)"
echo "================================================"

# Download sets
echo ""
echo "Downloading Pokemon sets..."
curl -H "X-Api-Key: $API_KEY" \
     "$BASE_URL/sets" \
     -o "$STORAGE_DIR/pokemon_sets.json" \
     --silent --show-error

if [ $? -eq 0 ]; then
    echo "✓ Sets downloaded successfully"
else
    echo "✗ Failed to download sets"
fi

# Download cards (1000 pages, 25 cards per page)
echo ""
echo "Downloading Pokemon cards (1000 pages)..."

success_count=0
fail_count=0

for page in {1..1000}; do
    retries=0
    max_retries=3
    downloaded=false
    
    while [ $retries -lt $max_retries ] && [ "$downloaded" = false ]; do
        curl -H "X-Api-Key: $API_KEY" \
             "$BASE_URL/cards?page=$page&pageSize=25" \
             -o "$CARDS_DIR/pokemon_cards_page$page.json" \
             --silent --show-error
        
        # Check if file contains error
        if grep -q "error code: 504" "$CARDS_DIR/pokemon_cards_page$page.json" 2>/dev/null || \
           grep -q "error" "$CARDS_DIR/pokemon_cards_page$page.json" 2>/dev/null; then
            retries=$((retries + 1))
            if [ $retries -lt $max_retries ]; then
                echo "⚠ Page $page error (retry $retries/$max_retries)"
                sleep 2
            fi
        else
            downloaded=true
            success_count=$((success_count + 1))
            echo "✓ Page $page downloaded ($success_count/1000)"
        fi
    done
    
    if [ "$downloaded" = false ]; then
        fail_count=$((fail_count + 1))
        echo "✗ Page $page failed after $max_retries retries"
    fi
    
    # Sleep 1 second between requests to be nice to the API
    sleep 1
done

echo ""
echo "================================================"
echo "Download Summary"
echo "================================================"
echo "Sets: $([ -f "$STORAGE_DIR/pokemon_sets.json" ] && echo 'Downloaded' || echo 'Failed')"
echo "Card pages: $success_count successful, $fail_count failed (out of 1000)"
echo "Completed: $(date)"
echo "================================================"
