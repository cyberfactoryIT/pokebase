#!/bin/bash

# Pokemon TCG Data Download Script
# Downloads all sets and cards from Pokemon TCG API

API_KEY="1fec095e-b12d-4c16-b1a7-17d96a10a75c"
BASE_URL="https://api.pokemontcg.io/v2"
STORAGE_DIR="storage/app"

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

# Download cards (100 pages, 250 cards per page)
echo ""
echo "Downloading Pokemon cards (100 pages)..."

success_count=0
fail_count=0

for page in {101..200}; do
    curl -H "X-Api-Key: $API_KEY" \
         "$BASE_URL/cards?page=$page&pageSize=250" \
         -o "$STORAGE_DIR/pokemon_cards_page$page.json" \
         --silent --show-error
    
    if [ $? -eq 0 ]; then
        success_count=$((success_count + 1))
        echo "✓ Page $page downloaded ($success_count/100)"
    else
        fail_count=$((fail_count + 1))
        echo "✗ Page $page failed"
    fi
    
    # Sleep 1 second between requests to be nice to the API
    sleep 1
done

echo ""
echo "================================================"
echo "Download Summary"
echo "================================================"
echo "Sets: $([ -f "$STORAGE_DIR/pokemon_sets.json" ] && echo 'Downloaded' || echo 'Failed')"
echo "Card pages: $success_count successful, $fail_count failed"
echo "Completed: $(date)"
echo "================================================"
