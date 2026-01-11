#!/bin/bash

# ============================================================================
# ETL Pipeline Simulator
# ============================================================================
# Simula l'intera pipeline di importazione nell'ordine schedulato
# Basato su routes/console.php schedule (02:10 - 06:00)
#
# Usage: ./simulate-etl-pipeline.sh
# ============================================================================

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Timestamp function
timestamp() {
    date '+%Y-%m-%d %H:%M:%S'
}

# Duration calculator
start_time=$(date +%s)

echo -e "${CYAN}"
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║                   ETL PIPELINE SIMULATOR                           ║"
echo "║                                                                    ║"
echo "║  Simula l'intera pipeline schedulata (02:10 - 06:00)              ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""
echo -e "${YELLOW}⚠️  NOTA: Questo eseguirà TUTTI i comandi ETL in sequenza${NC}"
echo -e "${YELLOW}   Durata stimata: 20-30 minuti (principalmente RapidAPI)${NC}"
echo ""
echo -e "${GREEN}🚀 Avvio pipeline...${NC}"
echo ""

# Clean pipeline_runs table for fresh start
echo -e "${BLUE}🧹 Pulizia tabella pipeline_runs per test pulito...${NC}"
php artisan tinker --execute="\DB::table('pipeline_runs')->truncate(); echo '✓ Pipeline runs cleared';"
echo ""

# Step 1: Cardmarket Download & Process (02:10)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 1/11: Cardmarket Download & Process (Schedule: 02:10)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📥 Step 1a: Download Cardmarket catalogue + prices${NC}"
echo ""
php artisan cardmarket:download --products
echo ""
echo -e "${CYAN}📥 Step 1b: Download Cardmarket price quotes${NC}"
echo ""
php artisan cardmarket:download --prices
echo ""
echo -e "${CYAN}📝 Step 1c: Process and import to database${NC}"
echo ""
step1_start=$(date +%s)
php artisan cardmarket:import --products
php artisan cardmarket:import --prices
step1_end=$(date +%s)
step1_duration=$((step1_end - step1_start))
echo ""
echo -e "${GREEN}✅ STEP 1 completato in ${step1_duration}s${NC}"
echo ""
sleep 2

# Step 2: TCGCSV Download & Import Pokemon (02:40)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 2/11: TCGCSV Download & Import Pokemon (Schedule: 02:40)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📥 Download & import Pokemon TCG data from tcgcsv.com (TCGplayer)${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~5-10 minutes${NC}"
echo ""
step2_start=$(date +%s)
php artisan tcgcsv:import-pokemon
step2_end=$(date +%s)
step2_duration=$((step2_end - step2_start))
echo ""
echo -e "${GREEN}✅ STEP 2 completato in ${step2_duration}s${NC}"
echo ""
sleep 2

# Step 3: RapidAPI Import Episodes (03:30)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 5/11: RapidAPI Import Episodes (Schedule: 03:30)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Importing Pokemon episodes list from RapidAPI${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~10-30 seconds${NC}"
echo ""
step5_start=$(date +%s)
php artisan rapidapi:import-episodes pokemon
step5_end=$(date +%s)
step5_duration=$((step5_end - step5_start))
echo ""
echo -e "${GREEN}✅ STEP 5 completato in ${step5_duration}s${NC}"
echo ""
sleep 2

# Step 6: RapidAPI Sync Cards (03:35)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 6/11: RapidAPI Sync Cards (Schedule: 03:35)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Syncing all episode cards with daily price snapshots${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~8-10 minutes (171 episodes × 3s rate limit)${NC}"
echo -e "${YELLOW}⚠️  Rate limit: 300 req/minute${NC}"
echo ""
step6_start=$(date +%s)
php artisan rapidapi:sync-cards pokemon
step6_end=$(date +%s)
step6_duration=$((step6_end - step6_start))
echo ""
echo -e "${GREEN}✅ STEP 6 completato in ${step6_duration}s${NC}"
echo ""
sleep 2

# Step 5: RapidAPI Cards Mapping
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 5/11: RapidAPI Cards Mapping (After Sync)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Mapping RapidAPI cards to TCGCSV products${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~10-30 seconds${NC}"
echo ""
step5_start=$(date +%s)
php artisan cards:map
step5_end=$(date +%s)
step5_duration=$((step5_end - step5_start))
echo ""
echo -e "${GREEN}✅ STEP 5 completato in ${step5_duration}s${NC}"
echo ""
sleep 2

# Step 6: Cardmarket Match Metacards
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 6/11: Cardmarket Match Metacards (Direct + Fuzzy)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Phase 1: Direct mapping from RapidAPI cardmarket_id${NC}"
echo -e "${CYAN}📝 Phase 2: Fuzzy matching for remaining products${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~2-3 minutes (tutti i prodotti, no limit)${NC}"
echo ""
step6_start=$(date +%s)
php artisan cardmarket:match-metacards --auto-confirm
step6_end=$(date +%s)
step6_duration=$((step6_end - step6_start))
echo ""
echo -e "${GREEN}✅ STEP 6 completato in ${step6_duration}s${NC}"
echo ""
sleep 2

# Step 7: TCGdex Download & Import (04:45)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 7/11: TCGdex Download & Import (Schedule: 04:45)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📥 Download & import Pokemon sets and cards from TCGdex API${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~1-2 minutes${NC}"
echo ""
step7_start=$(date +%s)
php artisan tcgdx:import
step7_end=$(date +%s)
step7_duration=$((step7_end - step7_start))
echo ""
echo -e "${GREEN}✅ STEP 7 completato in ${step7_duration}s${NC}"
echo ""
sleep 2

# Step 8: RapidAPI Episodes Mapping (05:30)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 8/11: RapidAPI Episodes Mapping (Schedule: 05:30)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Mapping RapidAPI episodes to TCGCSV groups${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~10-30 seconds${NC}"
echo ""
step8_start=$(date +%s)
php artisan rapidapi:map-episodes
step8_end=$(date +%s)
step8_duration=$((step8_end - step8_start))
echo ""
echo -e "${GREEN}✅ STEP 8 completato in ${step8_duration}s${NC}"
echo ""
sleep 2

# Step 9: TCGdex to TCGCSV Mapping (05:50)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 9/11: TCGdex to TCGCSV Mapping (Schedule: 05:50)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Mapping TCGdex sets/cards to TCGCSV (fuzzy matching + logos)${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~5-15 seconds${NC}"
echo ""
step9_start=$(date +%s)
php artisan tcgdex:map-to-tcgcsv
step9_end=$(date +%s)
step9_duration=$((step9_end - step9_start))
echo ""
echo -e "${GREEN}✅ STEP 9 completato in ${step9_duration}s${NC}"
echo ""
sleep 2

# Step 9b: TCGdex CardMarket Product IDs Mapping
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 9b/11: TCGdex CardMarket Product IDs Mapping${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Extracting CardMarket product IDs from TCGdex JSON${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~5-15 seconds${NC}"
echo ""
step9b_start=$(date +%s)
php artisan tcgdex:map-cardmarket
step9b_end=$(date +%s)
step9b_duration=$((step9b_end - step9b_start))
echo ""
echo -e "${GREEN}✅ STEP 9b completato in ${step9b_duration}s${NC}"
echo ""
sleep 2

# Step 10: TCGCSV Enrichment (06:00)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 10/11: TCGCSV Enrichment (Schedule: 06:00)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Enriching TCGCSV with HD images, prices, links, details${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~2-5 minutes${NC}"
echo ""
step10_start=$(date +%s)
php artisan tcgcsv:enrich --all
step10_end=$(date +%s)
step10_duration=$((step10_end - step10_start))
echo ""
echo -e "${GREEN}✅ STEP 10 completato in ${step10_duration}s${NC}"
echo ""
sleep 2

# Step 11: Cardmarket Sync Prices (06:30)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 11/11: Cardmarket Sync Prices (Schedule: 06:30)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Syncing Cardmarket trend prices to TCGCSV products${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~1-2 minutes${NC}"
echo ""
step11_start=$(date +%s)
php artisan cardmarket:sync-prices --force
step11_end=$(date +%s)
step11_duration=$((step11_end - step11_start))
echo ""
echo -e "${GREEN}✅ STEP 11 completato in ${step11_duration}s${NC}"
echo ""

# Calculate total duration
end_time=$(date +%s)
total_duration=$((end_time - start_time))
total_minutes=$((total_duration / 60))
total_seconds=$((total_duration % 60))

# Final summary
echo ""
echo -e "${CYAN}"
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║                      PIPELINE COMPLETATA! 🎉                       ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""
echo -e "${GREEN}📊 RIEPILOGO DURATE:${NC}"
echo -e "   1️⃣  Cardmarket Download & Process ......... ${step1_duration}s"
echo -e "   2️⃣  TCGCSV Download & Import .............. ${step2_duration}s"
echo -e "   3️⃣  RapidAPI Import Episodes .............. ${step5_duration}s"
echo -e "   4️⃣  RapidAPI Sync Cards ................... ${step6_duration}s"
echo -e "   5️⃣  RapidAPI Cards Mapping ................ ${step5_duration}s"
echo -e "   6️⃣  Cardmarket Match (Direct+Fuzzy) ....... ${step6_duration}s"
echo -e "   7️⃣  TCGdex Download & Import .............. ${step7_duration}s"
echo -e "   8️⃣  RapidAPI Episodes Mapping ............. ${step8_duration}s"
echo -e "   9️⃣  TCGdex Mapping ........................ ${step9_duration}s"
echo -e "   9️⃣🅱️ TCGdex CardMarket IDs ................ ${step9b_duration}s"
echo -e "   🔟  TCGCSV Enrichment ..................... ${step10_duration}s"
echo -e "   1️⃣1️⃣ Cardmarket Sync Prices ................ ${step11_duration}s"
echo ""
echo -e "${CYAN}⏱️  DURATA TOTALE: ${total_minutes}m ${total_seconds}s${NC}"
echo ""

# Show pipeline_runs table
echo -e "${BLUE}📋 PIPELINE RUNS (tracking log):${NC}"
echo ""
php artisan tinker --execute="
\$runs = \App\Models\PipelineRun::orderBy('started_at')->get();
echo str_pad('TASK', 30) . str_pad('STATUS', 12) . str_pad('DURATION', 12) . str_pad('ROWS', 15) . 'ERRORS' . PHP_EOL;
echo str_repeat('─', 90) . PHP_EOL;
foreach (\$runs as \$run) {
    echo str_pad(\$run->task_name, 30) . 
         str_pad(\$run->status, 12) . 
         str_pad(\$run->duration ?? '0s', 12) . 
         str_pad((\$run->rows_processed ?? 0) . ' processed', 15) . 
         (\$run->errors_count ?? 0) . PHP_EOL;
}
echo PHP_EOL . '✅ Total runs: ' . \$runs->count() . PHP_EOL;
"

echo ""
echo -e "${GREEN}✅ Pipeline simulation completata con successo!${NC}"
echo ""
echo -e "${YELLOW}💡 Per vedere i dettagli completi:${NC}"
echo -e "   ${CYAN}php artisan tinker${NC}"
echo -e "   ${CYAN}> \\App\\Models\\PipelineRun::all();${NC}"
echo ""
