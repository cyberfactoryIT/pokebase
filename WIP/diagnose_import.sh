#!/bin/bash

# 🔍 DIAGNOSI IMPORT BLOCCATO A 500 CARTE

echo "🔍 DIAGNOSI IMPORT RAPIDAPI"
echo "======================================="
echo ""

# 1. Stato attuale
echo "📊 1. STATO DATABASE:"
php artisan tinker --execute="
\$total = DB::table('rapidapi_cards')->count();
\$episodes = DB::table('rapidapi_cards')->distinct('episode_id')->count('episode_id');
echo 'Carte: ' . \$total . PHP_EOL;
echo 'Episodi: ' . \$episodes . ' / 173' . PHP_EOL;
"
echo ""

# 2. Processi attivi
echo "🔄 2. PROCESSI IN ESECUZIONE:"
ps aux | grep -E "rapidapi|import" | grep -v grep || echo "Nessun processo attivo"
echo ""

# 3. Log recenti
echo "📝 3. ULTIMI LOG:"
if [ -f /tmp/rapidapi_gradual_*.log ]; then
    echo "Log trovati:"
    ls -lh /tmp/rapidapi_gradual_*.log
    echo ""
    echo "Ultimi 10 righe:"
    tail -10 /tmp/rapidapi_gradual_*.log 2>/dev/null
else
    echo "Nessun log trovato in /tmp/"
fi
echo ""

# 4. Test API
echo "🧪 4. TEST CONNESSIONE API:"
echo "Testo 1 pagina per verificare rate limit..."
php artisan rapidapi:test pokemon --pages=1 --save 2>&1 | grep -E "✅|❌|Error|429|quota"
echo ""

echo "======================================="
echo "💡 SOLUZIONI:"
echo ""
echo "Se rate limit (429):"
echo "  → Aspetta e riprova tra 1 minuto"
echo ""
echo "Se quota esaurita:"
echo "  → Controlla https://rapidapi.com/dashboard"
echo "  → Aspetta reset quota (00:00 UTC)"
echo ""
echo "Se tutto OK:"
echo "  → Riavvia import: ./import_gradual.sh"
echo ""
