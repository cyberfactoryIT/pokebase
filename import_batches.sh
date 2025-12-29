#!/bin/bash

echo "🚀 Starting RapidAPI Pokemon Import - $(date)"
echo "==========================================="
echo ""

for i in {1..10}; do
    echo "📦 Batch $i/10 - $(date +%H:%M:%S)"
    php artisan rapidapi:test pokemon --pages=100 --save
    
    if [ $? -eq 0 ]; then
        echo "   ✅ Batch $i completed successfully"
    else
        echo "   ❌ Batch $i failed"
        break
    fi
    
    # Quick count
    count=$(php artisan tinker --execute="echo DB::table('rapidapi_cards')->count();" 2>/dev/null | grep -o '[0-9]*' | tail -1)
    echo "   📊 Total cards: $count"
    echo ""
    
    # Small pause between batches
    sleep 3
done

echo ""
echo "✅ Import completed - $(date)"
echo "==========================================="
