#!/bin/bash

# 🎯 IMPORT GRADUALE POKEMON CON CONTROLLI PERIODICI
# Configurazione: batch piccoli, pause lunghe, controlli frequenti

BATCH_SIZE=25          # Pagine per batch (25 pagine = ~500 carte)
PAUSE_SECONDS=300      # Pausa tra batch (5 minuti)
MAX_BATCHES=10         # Numero massimo di batch (25*10 = 250 pagine)

LOG_FILE="/tmp/rapidapi_gradual_$(date +%Y%m%d_%H%M%S).log"

echo "🚀 IMPORT GRADUALE POKEMON" | tee -a "$LOG_FILE"
echo "=====================================" | tee -a "$LOG_FILE"
echo "Configurazione:" | tee -a "$LOG_FILE"
echo "  - Batch size: $BATCH_SIZE pagine (~$((BATCH_SIZE * 20)) carte)" | tee -a "$LOG_FILE"
echo "  - Pausa: $((PAUSE_SECONDS / 60)) minuti" | tee -a "$LOG_FILE"
echo "  - Max batch: $MAX_BATCHES" | tee -a "$LOG_FILE"
echo "  - Log: $LOG_FILE" | tee -a "$LOG_FILE"
echo "=====================================" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

for i in $(seq 1 $MAX_BATCHES); do
    echo "🔄 BATCH $i/$MAX_BATCHES - $(date '+%Y-%m-%d %H:%M:%S')" | tee -a "$LOG_FILE"
    echo "-----------------------------------" | tee -a "$LOG_FILE"
    
    # Calcola da quale pagina partire
    CURRENT_CARDS=$(php artisan tinker --execute="echo DB::table('rapidapi_cards')->count();" 2>/dev/null | tail -1)
    START_PAGE=$(( (CURRENT_CARDS / 20) + 1 ))
    
    echo "   Pagina di partenza: $START_PAGE (già importate: $CURRENT_CARDS carte)" | tee -a "$LOG_FILE"
    
    # Esegui import dalla pagina corretta
    php artisan rapidapi:test pokemon --pages=$BATCH_SIZE --start-page=$START_PAGE --save 2>&1 | tee -a "$LOG_FILE" | grep -E "✅|❌|Error|Saved"
    
    # Controlla risultato
    RESULT=$(php artisan tinker --execute="
        \$total = DB::table('rapidapi_cards')->count();
        \$episodes = DB::table('rapidapi_cards')->distinct('episode_id')->count('episode_id');
        \$progress = round((\$episodes / 173) * 100, 1);
        \$withPrices = DB::table('rapidapi_cards')->whereRaw(\"JSON_EXTRACT(raw_data, '$.prices.cardmarket.lowest_near_mint') > 0\")->count();
        echo \$total . '|' . \$episodes . '|' . \$progress . '|' . \$withPrices;
    " 2>/dev/null | tail -1)
    
    IFS='|' read -r CARDS EPISODES PROGRESS PRICES <<< "$RESULT"
    
    echo "📊 Stato attuale:" | tee -a "$LOG_FILE"
    echo "   🎴 Carte: $CARDS" | tee -a "$LOG_FILE"
    echo "   📺 Episodi: $EPISODES / 173 ($PROGRESS%)" | tee -a "$LOG_FILE"
    echo "   💰 Con prezzi EUR: $PRICES" | tee -a "$LOG_FILE"
    echo "" | tee -a "$LOG_FILE"
    
    # Controlla se abbiamo finito
    if [ "$EPISODES" -ge 171 ]; then
        echo "✅ IMPORT COMPLETATO! ($PROGRESS%)" | tee -a "$LOG_FILE"
        break
    fi
    
    # Pausa prima del prossimo batch
    if [ $i -lt $MAX_BATCHES ]; then
        echo "⏸️  Pausa $((PAUSE_SECONDS / 60)) minuti prima del prossimo batch..." | tee -a "$LOG_FILE"
        echo "" | tee -a "$LOG_FILE"
        sleep $PAUSE_SECONDS
    fi
done

echo "" | tee -a "$LOG_FILE"
echo "=====================================" | tee -a "$LOG_FILE"
echo "🏁 IMPORT TERMINATO - $(date '+%Y-%m-%d %H:%M:%S')" | tee -a "$LOG_FILE"
echo "=====================================" | tee -a "$LOG_FILE"

# Statistiche finali
php artisan tinker --execute="
echo PHP_EOL . '📊 STATISTICHE FINALI:' . PHP_EOL . PHP_EOL;
\$total = DB::table('rapidapi_cards')->count();
\$episodes = DB::table('rapidapi_cards')->distinct('episode_id')->count('episode_id');
\$withPrices = DB::table('rapidapi_cards')->whereRaw(\"JSON_EXTRACT(raw_data, '$.prices.cardmarket.lowest_near_mint') > 0\")->count();
\$pricePercent = round((\$withPrices / \$total) * 100, 1);
echo '🎴 Carte totali: ' . number_format(\$total) . PHP_EOL;
echo '📺 Episodi: ' . \$episodes . ' / 173 (' . round((\$episodes / 173) * 100, 1) . '%)' . PHP_EOL;
echo '💰 Con prezzi Cardmarket: ' . number_format(\$withPrices) . ' (' . \$pricePercent . '%)' . PHP_EOL;
echo PHP_EOL . '📄 Log completo: $LOG_FILE' . PHP_EOL;
" | tee -a "$LOG_FILE"
