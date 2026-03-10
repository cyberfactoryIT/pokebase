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

# PHP Command - Auto-detect php84 or fall back to php
if command -v php84 &> /dev/null; then
    PHP_CMD="php84"
else
    PHP_CMD="php"
fi

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Get project root (parent of scripts directory)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

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
$PHP_CMD artisan tinker --execute="\DB::table('pipeline_runs')->truncate(); echo '✓ Pipeline runs cleared';"
echo ""

# Step 1: Cardmarket Download & Process (02:10)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 1/12: Cardmarket Download & Process (Schedule: 02:10)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📥 Step 1a: Download Cardmarket catalogue + prices (Pokemon, Lorcana, One Piece)${NC}"
echo ""
$PHP_CMD artisan cardmarket:download pokemon --products
$PHP_CMD artisan cardmarket:download pokemon --prices
echo ""
$PHP_CMD artisan cardmarket:download lorcana --products
$PHP_CMD artisan cardmarket:download lorcana --prices
echo ""
$PHP_CMD artisan cardmarket:download onepiece --products
$PHP_CMD artisan cardmarket:download onepiece --prices
echo ""
echo -e "${CYAN}📝 Step 1b: Process and import to database (Pokemon, Lorcana, One Piece)${NC}"
echo ""
step1_start=$(date +%s)
$PHP_CMD artisan cardmarket:import pokemon --products
$PHP_CMD artisan cardmarket:import pokemon --prices
echo ""
$PHP_CMD artisan cardmarket:import lorcana --products
$PHP_CMD artisan cardmarket:import lorcana --prices
echo ""
$PHP_CMD artisan cardmarket:import onepiece --products
$PHP_CMD artisan cardmarket:import onepiece --prices
step1_end=$(date +%s)
step1_duration=$((step1_end - step1_start))
echo ""
echo -e "${GREEN}✅ STEP 1 completato in ${step1_duration}s${NC}"
echo ""
sleep 2

# Step 2: TCGCSV Download & Import Pokemon (02:40)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 2/12: TCGCSV Download & Import Pokemon (Schedule: 02:40)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📥 Download & import Pokemon TCG data from tcgcsv.com (TCGplayer)${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~5-10 minutes${NC}"
echo ""
step2_start=$(date +%s)
$PHP_CMD artisan tcgcsv:import-pokemon
step2_end=$(date +%s)
step2_duration=$((step2_end - step2_start))
echo ""
echo -e "${GREEN}✅ STEP 2 completato in ${step2_duration}s${NC}"
echo ""
sleep 2

# Step 3: CMAPI Import (CardMarket API via RapidAPI) (03:30)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 3/8: CMAPI Import (CardMarket API) (Schedule: 03:30)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Importing sets and cards from CardMarket API via RapidAPI for: pokemon, lorcana, onepiece, riftbound${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~5-15 minutes (all games)${NC}"
echo ""
step5_start=$(date +%s)
#$PHP_CMD artisan cmapi:import --game=pokemon
#$PHP_CMD artisan cmapi:import --game=lorcana
#$PHP_CMD artisan cmapi:import --game=onepiece
#$PHP_CMD artisan cmapi:import --game=riftbound
step5_end=$(date +%s)
step5_duration=$((step5_end - step5_start))
echo ""
echo -e "${GREEN}✅ STEP 3 completato in ${step5_duration}s${NC}"
echo ""
sleep 2

# Step 4: TCGdex Download & Import (04:45)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 4/7: TCGdex Download & Import (Schedule: 04:45)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📥 Download & import Pokemon sets and cards from TCGdex API${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~1-2 minutes${NC}"
echo ""
step7_start=$(date +%s)
$PHP_CMD artisan tcgdx:import
step7_end=$(date +%s)
step7_duration=$((step7_end - step7_start))
echo ""
echo -e "${GREEN}✅ STEP 4 completato in ${step7_duration}s${NC}"
echo ""
sleep 2

# Step 5: Cardmarket Sync Prices (06:30)
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}STEP 5/5: Cardmarket Sync Prices (Schedule: 06:30)${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Syncing Cardmarket trend prices to TCGCSV products${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~1-2 minutes${NC}"
echo ""
step11_start=$(date +%s)
$PHP_CMD artisan cardmarket:sync-prices --force
step11_end=$(date +%s)
step11_duration=$((step11_end - step11_start))
echo ""
echo -e "${GREEN}✅ STEP 5 completato in ${step11_duration}s${NC}"
echo ""
sleep 2

# Optional: Refresh Cached Prices for Users
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}OPTIONAL: Refresh Cached Prices for Users${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}⏰ Started at: $(timestamp)${NC}"
echo -e "${CYAN}📝 Refreshing cached prices for user collections and decks${NC}"
echo -e "${CYAN}⏱️  Estimated duration: ~10-30 seconds${NC}"
echo ""
step12_start=$(date +%s)
$PHP_CMD artisan prices:refresh-cache --force
step12_end=$(date +%s)
step12_duration=$((step12_end - step12_start))
echo ""
echo -e "${GREEN}✅ OPTIONAL STEP completato in ${step12_duration}s${NC}"
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
echo -e "   3️⃣  CMAPI Import (all games) .............. ${step5_duration}s"
echo -e "   4️⃣  TCGdex Download & Import .............. ${step7_duration}s"
echo -e "   5️⃣  Cardmarket Sync Prices ................ ${step11_duration}s"
echo ""
echo -e "${CYAN}⏱️  DURATA TOTALE: ${total_minutes}m ${total_seconds}s${NC}"
echo ""

# Show pipeline_runs table
echo -e "${BLUE}📋 PIPELINE RUNS (tracking log):${NC}"
echo ""
$PHP_CMD artisan tinker --execute="
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
