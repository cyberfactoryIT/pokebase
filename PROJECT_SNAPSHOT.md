# 📸 Project Snapshot - 26 December 2025

## 🎯 Quick Recovery Guide
If anything goes wrong, this file helps you understand the complete state of the project.

## Git Commits
```bash
# Last 10 commits
2c3157b Add STATUS.md - comprehensive project status tracker
f32eb68 korr
bf44d8d korr
33921b2 multigame
36b171a multigioco profilo
```

## 🔑 Critical Files Location

### Core Logic
- **Multi-game middleware**: `app/Http/Middleware/SetCurrentGame.php`
- **Game context service**: `app/Services/CurrentGameContext.php`
- **Current game controller**: `app/Http/Controllers/CurrentGameController.php`
- **Helper functions**: `app/helpers.php`

### Import System
- **Import command**: `app/Console/Commands/TcgcsvImport.php`
- **Import service**: `app/Services/Tcgcsv/TcgcsvImportService.php`
- **TCGCSV client**: `app/Services/Tcgcsv/TcgcsvClient.php`

### Database
- **Migrations**: `database/migrations/2025_12_26_*.php` (4 files)
- **Seeder**: `database/seeders/GamesSeeder.php`
- **Models**: `app/Models/Game.php`, `TcgcsvGroup.php`, `TcgcsvProduct.php`, `TcgcsvPrice.php`

### Views
- **Navigation**: `resources/views/layouts/navigation.blade.php`
- **Dashboard**: `resources/views/dashboard.blade.php`
- **Profile games**: `resources/views/profile/game-management.blade.php`

## 📊 Database State
```sql
-- Games table
SELECT * FROM games;
-- Expected: 3 rows (Pokemon, MTG, Yu-Gi-Oh)

-- Groups by game
SELECT game_id, COUNT(*) FROM tcgcsv_groups GROUP BY game_id;
-- Expected: game_id=1: 212, game_id=2: 440

-- User-game associations
SELECT * FROM game_user;
```

## 🔄 Recovery Commands

### If database is corrupted
```bash
# Rollback all multi-game migrations
php artisan migrate:rollback --step=4

# Re-run migrations
php artisan migrate

# Re-seed games
php artisan db:seed --class=GamesSeeder

# Re-import Pokemon
php artisan tcgcsv:import --game=pokemon --only=all
```

### If code is broken
```bash
# Reset to last working commit
git reset --hard f32eb68

# Or reset to multi-game commit
git reset --hard 33921b2

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### If import data is wrong
```bash
# Clean all TCGCSV data
php artisan tinker
>>> DB::statement('SET FOREIGN_KEY_CHECKS=0');
>>> DB::table('tcgcsv_prices')->truncate();
>>> DB::table('tcgcsv_products')->truncate();
>>> DB::table('tcgcsv_groups')->truncate();
>>> DB::statement('SET FOREIGN_KEY_CHECKS=1');

# Re-import
php artisan tcgcsv:import --game=pokemon --only=all
```

## 📝 Key Configuration

### Games Configuration
| ID | Name | Code | TCGCSV Category |
|----|------|------|-----------------|
| 1 | Pokémon TCG | pokemon | 3 |
| 2 | Magic: The Gathering | mtg | 1 |
| 3 | Yu-Gi-Oh! | yugioh | 2 |

### Middleware Stack
```php
'web' => [
    // ... other middleware
    \App\Http\Middleware\SetCurrentGame::class,
]
```

### Helper Functions
```php
currentGame()      // Get current Game model
currentGameId()    // Get current game ID
availableGames()   // Get user's games
```

## 🚨 Critical Dependencies

### Database Tables
- `games` (main game definitions)
- `game_user` (user-game associations)
- `tcgcsv_groups` (with game_id)
- `tcgcsv_products` (with game_id)
- `tcgcsv_prices` (with game_id)

### Services
- CurrentGameContext
- TcgcsvClient
- TcgcsvImportService

### Routes
```php
POST /current-game → Switch game
```

## 📚 Documentation Files
1. `STATUS.md` - Current state (updated continuously)
2. `DEPLOYMENT.md` - Production deployment
3. `MULTI_GAME_SYSTEM.md` - Architecture
4. `MULTI_GAME_IMPLEMENTATION.md` - Implementation details
5. `MULTI_GAME_TESTING.md` - Testing guide
6. `PROJECT_SNAPSHOT.md` - This file

## 💾 Backup Strategy
```bash
# Create full backup
tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz \
  app/ database/ resources/ routes/ config/ \
  composer.json composer.lock .env

# Database backup
mysqldump -u root pokebase > backup_$(date +%Y%m%d_%H%M%S).sql
```

## ⚡ Emergency Contacts
- Main documentation: `STATUS.md`
- Deployment guide: `DEPLOYMENT.md`
- Git history: `git log --oneline`

---
*Generated: 26 December 2025*
*Branch: main*
*Last Commit: 2c3157b*
