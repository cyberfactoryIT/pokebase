#!/bin/bash

# 🎯 RESUME IMPORT - Riprende da dove si è fermato (senza sprecare chiamate API)

echo "🔍 RESUME IMPORT INTELLIGENTE"
echo "======================================="
echo ""

# Calcola stato attuale
CURRENT_STATE=$(php artisan tinker --execute="
\$total = DB::table('rapidapi_cards')->count();
\$episodes = DB::table('rapidapi_cards')->distinct('episode_id')->count('episode_id');
\$progress = round((\$episodes / 173) * 100, 1);

// Calcola da quale pagina riprendere
\$currentPages = ceil(\$total / 20);
\$totalPages = 991;
\$remainingPages = \$totalPages - \$currentPages;

echo \$total . '|' . \$episodes . '|' . \$progress . '|' . \$currentPages . '|' . \$remainingPages;
" 2>/dev/null | tail -1)

IFS='|' read -r CARDS EPISODES PROGRESS CURRENT_PAGES REMAINING_PAGES <<< "$CURRENT_STATE"

echo "📊 STATO ATTUALE:"
echo "   Carte: $CARDS"
echo "   Episodi: $EPISODES / 173 ($PROGRESS%)"
echo "   Pagine già importate: $CURRENT_PAGES"
echo "   Pagine rimanenti: $REMAINING_PAGES"
echo ""

if [ "$REMAINING_PAGES" -le 0 ]; then
    echo "✅ IMPORT GIÀ COMPLETATO!"
    exit 0
fi

# Chiedi quante pagine importare
if [ -z "$1" ]; then
    PAGES_TO_IMPORT=50
    echo "💡 Importo $PAGES_TO_IMPORT pagine (default)"
else
    PAGES_TO_IMPORT=$1
    echo "💡 Importo $PAGES_TO_IMPORT pagine (da parametro)"
fi

# Non superare le pagine rimanenti
if [ "$PAGES_TO_IMPORT" -gt "$REMAINING_PAGES" ]; then
    PAGES_TO_IMPORT=$REMAINING_PAGES
    echo "   ⚠️  Ridotto a $PAGES_TO_IMPORT (tutte le rimanenti)"
fi

CARDS_TO_IMPORT=$((PAGES_TO_IMPORT * 20))

echo ""
echo "🚀 INIZIO IMPORT:"
echo "   Riprendo da pagina: $((CURRENT_PAGES + 1))"
echo "   Scaricherò: $PAGES_TO_IMPORT pagine (~$CARDS_TO_IMPORT carte)"
echo "   Tempo stimato: ~$((PAGES_TO_IMPORT / 25 * 3)) minuti"
echo ""
echo "✅ RISPARMIO: $CURRENT_PAGES chiamate API (già importate)"
echo ""

read -p "Continua? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Annullato"
    exit 0
fi

echo ""
echo "🔄 AVVIO IMPORT..."
echo "======================================="
echo ""

# Esegui import dalla pagina corretta
START_PAGE=$((CURRENT_PAGES + 1))
php artisan rapidapi:test pokemon --pages=$PAGES_TO_IMPORT --start-page=$START_PAGE --save

echo ""
echo "======================================="
echo "✅ IMPORT COMPLETATO"
echo ""

# Mostra stato finale
php artisan tinker --execute="
\$total = DB::table('rapidapi_cards')->count();
\$episodes = DB::table('rapidapi_cards')->distinct('episode_id')->count('episode_id');
\$progress = round((\$episodes / 173) * 100, 1);
echo '📊 STATO FINALE:' . PHP_EOL;
echo '   Carte: ' . number_format(\$total) . PHP_EOL;
echo '   Episodi: ' . \$episodes . ' / 173 (' . \$progress . '%)' . PHP_EOL;
"
