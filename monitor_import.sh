#!/bin/bash

# 🔍 MONITOR IMPORT IN TEMPO REALE
# Mostra statistiche aggiornate ogni minuto

echo "🔍 MONITOR IMPORT POKEMON"
echo "Aggiornamento ogni 60 secondi (CTRL+C per uscire)"
echo "=================================================="
echo ""

while true; do
    TIMESTAMP=$(date '+%H:%M:%S')
    
    # Ottieni statistiche
    STATS=$(php artisan tinker --execute="
        \$total = DB::table('rapidapi_cards')->count();
        \$episodes = DB::table('rapidapi_cards')->distinct('episode_id')->count('episode_id');
        \$progress = round((\$episodes / 173) * 100, 1);
        \$withPrices = DB::table('rapidapi_cards')->whereRaw(\"JSON_EXTRACT(raw_data, '$.prices.cardmarket.lowest_near_mint') > 0\")->count();
        \$pricePercent = round((\$withPrices / \$total) * 100, 1);
        echo \$total . '|' . \$episodes . '|' . \$progress . '|' . \$withPrices . '|' . \$pricePercent;
    " 2>/dev/null | tail -1)
    
    IFS='|' read -r CARDS EPISODES PROGRESS PRICES PRICE_PERCENT <<< "$STATS"
    
    # Calcola pagine rimanenti
    CURRENT_PAGES=$((CARDS / 20))
    REMAINING_PAGES=$((991 - CURRENT_PAGES))
    EST_TIME=$((REMAINING_PAGES / 25 * 5))  # 25 pagine per batch, 5 min per batch
    
    # Mostra statistiche
    echo "[$TIMESTAMP] 🎴 Carte: $CARDS | 📺 Episodi: $EPISODES/173 ($PROGRESS%) | 💰 Prezzi: $PRICES ($PRICE_PERCENT%) | ⏳ ~${EST_TIME}min rimasti"
    
    # Controlla se finito
    if (( $(echo "$PROGRESS >= 99.0" | bc -l) )); then
        echo ""
        echo "✅ IMPORT COMPLETATO!"
        break
    fi
    
    # Aspetta 60 secondi
    sleep 60
done
