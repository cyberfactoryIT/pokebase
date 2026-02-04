#!/bin/bash

# ================================================
# 🚀 Pokebase Multi-Game Deployment Script
# ================================================

set -e  # Exit on any error

echo "╔════════════════════════════════════════════════════════╗"
echo "║         Pokebase Multi-Game Deployment                ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
BRANCH="${1:-main}"
BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"

echo -e "${BLUE}[1/9]${NC} Checking environment..."
if [ ! -f ".env" ]; then
    echo -e "${RED}✗ .env file not found!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Environment OK${NC}"

echo ""
echo -e "${BLUE}[2/9]${NC} Creating backup..."
mkdir -p "$BACKUP_DIR"
php artisan backup:database "$BACKUP_DIR/database.sql" 2>/dev/null || mysqldump -u root pokebase > "$BACKUP_DIR/database.sql"
echo -e "${GREEN}✓ Backup created in $BACKUP_DIR${NC}"

echo ""
echo -e "${BLUE}[3/9]${NC} Pulling latest code from ${YELLOW}$BRANCH${NC}..."
git fetch origin
git pull origin "$BRANCH"
echo -e "${GREEN}✓ Code updated${NC}"

echo ""
echo -e "${BLUE}[4/9]${NC} Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"

echo ""
echo -e "${BLUE}[5/9]${NC} Running migrations..."
php artisan migrate --force
echo -e "${GREEN}✓ Migrations completed${NC}"

echo ""
echo -e "${BLUE}[6/9]${NC} Seeding games..."
php artisan db:seed --class=GamesSeeder --force
echo -e "${GREEN}✓ Games seeded${NC}"

echo ""
echo -e "${BLUE}[7/10]${NC} Syncing Cardmarket prices..."
php artisan cardmarket:sync-prices --force
echo -e "${GREEN}✓ Cardmarket prices synced${NC}"

echo ""
echo -e "${BLUE}[8/10]${NC} Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo -e "${GREEN}✓ Caches cleared${NC}"

echo ""
echo -e "${BLUE}[9/10]${NC} Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
echo -e "${GREEN}✓ Caches rebuilt${NC}"

echo ""
echo -e "${BLUE}[10/10]${NC} Verifying deployment..."
GAMES_COUNT=$(php artisan tinker --execute="echo DB::table('games')->count();")
echo "   Games in database: $GAMES_COUNT"

if [ "$GAMES_COUNT" -lt 1 ]; then
    echo -e "${RED}✗ No games found! Something went wrong.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Deployment verified${NC}"

echo ""
echo "╔════════════════════════════════════════════════════════╗"
echo "║          ✨ Deployment Completed Successfully! ✨     ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Import TCGCSV data for Pokemon:"
echo "   ${BLUE}php artisan tcgcsv:import --game=pokemon --only=all${NC}"
echo ""
echo "2. (Optional) Import Magic: The Gathering:"
echo "   ${BLUE}php artisan tcgcsv:import --game=mtg --only=all${NC}"
echo ""
echo "3. (Optional) Import Yu-Gi-Oh:"
echo "   ${BLUE}php artisan tcgcsv:import --game=yugioh --only=all${NC}"
echo ""
echo "4. Associate existing users with games (if needed):"
echo "   ${BLUE}php artisan tinker${NC}"
echo "   ${BLUE}>>> DB::table('game_user')->insert(User::all()->map(fn(\$u) => ['user_id' => \$u->id, 'game_id' => 1, 'is_enabled' => true])->toArray());${NC}"
echo ""
echo -e "${GREEN}Backup location: $BACKUP_DIR${NC}"
