# 🛠️ Basecard - Operational Commands

*Quick reference per comandi operativi comuni*

---

## 🚀 Deployment

### Deploy completo su produzione
```bash
cd /path/to/pokebase
./deploy.sh
```

Lo script esegue automaticamente:
- Backup database
- Git pull
- Composer install --no-dev --optimize-autoloader
- npm install && npm run build
- php artisan migrate --force
- php artisan db:seed --class=GamesSeeder --force
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache
- php artisan queue:restart

### Deploy manuale step-by-step
```bash
# 1. Backup database
php artisan db:backup

# 2. Update code
git pull origin main

# 3. Dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 4. Migrations
php artisan migrate --force

# 5. Cache rebuild
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Queue restart
php artisan queue:restart
```

---

## 📦 Data Import

### Import TCGCSV (USA Pricing)

**Pokemon (obbligatorio):**
```bash
php artisan tcgcsv:import --game=pokemon --only=all
```

**Magic: The Gathering:**
```bash
php artisan tcgcsv:import --game=mtg --only=all
```

**Yu-Gi-Oh:**
```bash
php artisan tcgcsv:import --game=yugioh --only=all
```

**Solo gruppi (set):**
```bash
php artisan tcgcsv:import --game=pokemon --only=groups
```

**Solo prodotti (carte):**
```bash
php artisan tcgcsv:import --game=pokemon --only=products
```

**Solo prezzi:**
```bash
php artisan tcgcsv:import --game=pokemon --only=prices
```

⏱️ **Tempo stimato**: 10-30 minuti per import completo

### Import Cardmarket (EU Pricing)

**Sync completo:**
```bash
php artisan cardmarket:sync-all --game=pokemon
```

**Sync singolo set:**
```bash
php artisan cardmarket:sync --set-code="SV01"
```

**Background sync (RapidAPI):**
```bash
./deploy-rapidapi-sync.sh
```

---

## 🗄️ Database Management

### Backup
```bash
php artisan db:backup
```

### Reset database (ATTENZIONE!)
```bash
php artisan migrate:fresh --seed
```

### Seed specific data
```bash
php artisan db:seed --class=GamesSeeder
php artisan db:seed --class=PricingPlansSeeder
```

### Check migrations status
```bash
php artisan migrate:status
```

---

## 👤 User Management

### Create superadmin user
```bash
php artisan make:superadmin
```

### List all users
```bash
php artisan tinker
>>> User::all();
```

### Change user role
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->assignRole('admin');
```

### Associate users to games
```bash
# Associa tutti gli utenti esistenti a Pokemon
php artisan tinker
>>> User::whereNull('default_game_id')->update(['default_game_id' => 1]);
```

---

## 🧹 Cache Management

### Clear all cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Rebuild cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear specific cache
```bash
php artisan cache:forget key_name
```

---

## 📧 Email Testing

### Send test email
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

### Check mail configuration
```bash
php artisan tinker
>>> config('mail');
```

---

## 🔍 Debugging

### Check logs
```bash
tail -f storage/logs/laravel.log
```

### Check specific date log
```bash
cat storage/logs/laravel-2026-01-13.log
```

### Clear logs
```bash
> storage/logs/laravel.log
```

### Check queue jobs
```bash
php artisan queue:work --once
```

### Failed jobs
```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## 🧪 Testing

### Run all tests
```bash
php artisan test
```

### Run specific test suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Run specific test file
```bash
php artisan test tests/Feature/PriceVisibilityTest.php
```

### Coverage report
```bash
php artisan test --coverage
```

---

## 🔐 Security

### Generate new APP_KEY
```bash
php artisan key:generate
```

### Clear expired tokens
```bash
php artisan auth:clear-resets
```

---

## 📊 Performance

### Optimize for production
```bash
php artisan optimize
```

### Clear optimization
```bash
php artisan optimize:clear
```

### Check slow queries
```bash
# Add to .env
DB_LOG_QUERIES=true
```

---

## 🌍 Localization

### Generate language files cache
```bash
php artisan lang:publish
```

### Check missing translations
```bash
php artisan tinker
>>> __('messages.nonexistent_key');
```

---

## � Price Caching System

### Overview
Quando un utente aggiunge una carta alla **collezione** o a un **deck**, il prezzo viene copiato dal catalogo e salvato nei campi:
- `cached_price` (decimal)
- `cached_price_currency` (EUR/USD)
- `cached_price_updated_at` (timestamp)

Questo garantisce che i prezzi rimangano stabili e non cambino nel tempo.

### Fonti prezzi per backend

**TCGCSV (USA):**
- EUR: `tcgcsv_prices.market_price` (USD)
- Fonte: TCGPlayer

**TCGDEX (EU + USA):**
- EUR: `tcgdx_cards.price_eur` (preferenza)
- USD: `tcgdx_cards.price_usd` (fallback)

**CMAPI (EU Cardmarket):**
- EUR: `cmapi_cards.price_eur`
- Fonte: Cardmarket API

### Database Tables

**user_collection:**
```sql
cached_price DECIMAL(10,2)
cached_price_currency VARCHAR(3)
cached_price_updated_at TIMESTAMP
```

**deck_cards:**
```sql
cached_price DECIMAL(10,2)
cached_price_currency VARCHAR(3)
cached_price_updated_at TIMESTAMP
```

### Controllers Implementation

**CollectionController:**
- `add()` - TCGCSV cards
- `addTcgdex()` - TCGDEX cards
- `addCmapi()` - CMAPI cards

**DeckController:**
- `addCard()` - TCGCSV cards
- `addCardTcgdex()` - TCGDEX cards
- `addCardCmapi()` - CMAPI cards

Tutti i metodi copiano il prezzo dal catalogo al momento dell'aggiunta.

### Price Display

**Collezione:** 
- Mostra `cached_price` nelle card
- Conversione automatica a valuta preferita utente

**Deck:**
- Mostra `cached_price` nelle card (3 partial backend-specific)
- Conversione automatica a valuta preferita utente
- Totali deck calcolati da `getDeckTopStats()` sommando cached_price

### Verificare cache prezzi

**Check ultimo deck card:**
```bash
php artisan tinker --execute="
\$dc = \App\Models\DeckCard::latest()->first();
echo 'ID: ' . \$dc->id . PHP_EOL;
echo 'Cached Price: ' . \$dc->cached_price . PHP_EOL;
echo 'Currency: ' . \$dc->cached_price_currency . PHP_EOL;
echo 'Updated: ' . \$dc->cached_price_updated_at . PHP_EOL;
"
```

**Check collection items:**
```bash
php artisan tinker --execute="
\$item = \App\Models\UserCollection::latest()->first();
echo 'ID: ' . \$item->id . PHP_EOL;
echo 'Cached Price: ' . \$item->cached_price . PHP_EOL;
echo 'Currency: ' . \$item->cached_price_currency . PHP_EOL;
"
```

---

## �💰 Stripe

### Sync pricing plans
```bash
php artisan stripe:sync-plans
```

### Check webhook endpoint
```bash
curl -X POST https://basecard.dk/stripe/webhook \
  -H "Content-Type: application/json" \
  -d '{"type":"test"}'
```

---

## 🐛 Common Issues

### "Class not found" error
```bash
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

### Routes not working
```bash
php artisan route:clear
php artisan route:cache
```

### Views not updating
```bash
php artisan view:clear
```

### Session issues
```bash
php artisan session:clear
php artisan cache:clear
```

---

## 📱 Frontend

### Build assets
```bash
npm run build
```

### Watch for changes (development)
```bash
npm run dev
```

### Check for npm issues
```bash
npm install
npm audit fix
```

---

## 🔄 Background Jobs

### Start queue worker
```bash
php artisan queue:work --daemon
```

### Process one job
```bash
php artisan queue:work --once
```

### Check queue status
```bash
php artisan queue:work --stop-when-empty
```

---

## 📝 Notes

- Tutti i comandi `php artisan` devono essere eseguiti dalla root del progetto
- Usare `--force` flag per migrations in produzione
- `queue:restart` è necessario dopo ogni deploy per ricaricare il codice nei worker
- Cache rebuild è critico dopo modifiche a routes/config/views
