# Procedura Deployment Cardmarket System

## 📋 Prerequisiti

- Accesso SSH al server di test
- Database backup recente
- Codice committato su Git

---

## 🚀 Step-by-Step Deployment

### 1️⃣ Backup Database (IMPORTANTE!)

```bash
# Sul server di test
cd /path/to/production
php artisan backup:run
# O manualmente:
mysqldump -u username -p database_name > backup_pre_cardmarket_$(date +%Y%m%d).sql
```

### 2️⃣ Deploy Codice

```bash
# Pull latest code
git pull origin main

# Install dependencies (se necessario)
composer install --no-dev --optimize-autoloader
```

### 3️⃣ Eseguire Migrations

```bash
# Verifica migrations pending
php artisan migrate:status

# Esegui migrations Cardmarket
php artisan migrate --force

# Migrations applicate:
# - 2025_12_27_120000_create_cardmarket_tables.php (4 tabelle)
# - 2025_12_27_134556_create_cardmarket_expansions_table.php
# - 2025_12_27_143840_add_tcgcsv_product_id_to_cardmarket_products_table.php
# - 2025_12_27_152000_create_tcgcsv_cardmarket_mapping_table.php (pivot)
```

### 4️⃣ Verificare Tabelle Create

```bash
php artisan tinker --execute="
echo 'Verifica tabelle Cardmarket:' . PHP_EOL;
\$tables = ['cardmarket_products', 'cardmarket_price_quotes', 'cardmarket_expansions', 'cardmarket_import_runs', 'tcgcsv_cardmarket_mapping'];
foreach (\$tables as \$table) {
    try {
        DB::table(\$table)->count();
        echo '✅ ' . \$table . PHP_EOL;
    } catch (Exception \$e) {
        echo '❌ ' . \$table . ' - ERROR' . PHP_EOL;
    }
}
"
```

### 5️⃣ Seeder Cardmarket Expansions (500 espansioni)

```bash
php artisan db:seed --class=CardmarketExpansionsSeeder

# Verifica
php artisan tinker --execute="
echo 'Cardmarket Expansions: ' . App\Models\CardmarketExpansion::count() . PHP_EOL;
"
```

### 6️⃣ Download Dati Cardmarket

```bash
# Download JSON files da S3 Cardmarket
php artisan cardmarket:download

# Nota: il comando usa la cache se i file sono già stati scaricati oggi
# Per forzare un nuovo download: php artisan cardmarket:download --force

# Verifica files scaricati
ls -lh storage/app/private/cardmarket/raw/
# Dovresti vedere:
# - pokemon_products_YYYYMMDD_HHMMSS.json (~11MB)
# - pokemon_prices_YYYYMMDD_HHMMSS.json (~13MB)
```

### 7️⃣ Import Cardmarket (Products + Prices)

```bash
# Import completo (può richiedere 10-20 minuti)
php artisan cardmarket:import

# Se vuoi solo products o solo prices:
# php artisan cardmarket:import --only=products
# php artisan cardmarket:import --only=prices

# Verifica import
php artisan tinker --execute="
echo '📦 Cardmarket Products: ' . App\Models\CardmarketProduct::count() . PHP_EOL;
echo '💰 Cardmarket Price Quotes: ' . App\Models\CardmarketPriceQuote::count() . PHP_EOL;
echo '📊 Import Runs: ' . App\Models\CardmarketImportRun::count() . PHP_EOL;
"
# Aspettati: ~62,629 products, ~62,629 price quotes
```

### 8️⃣ Match Cardmarket Expansions → TCGCSV Groups

```bash
# Match espansioni (auto-confirm per ≥90% confidence)
php artisan cardmarket:match-expansions --threshold=80 --auto-confirm

# Risultati attesi: ~134 espansioni matchate (26.8%)
# Verifica
php artisan tinker --execute="
\$mapped = App\Models\CardmarketExpansion::whereNotNull('tcgcsv_group_id')->count();
\$total = App\Models\CardmarketExpansion::count();
echo 'Expansions matched: ' . \$mapped . '/' . \$total . ' (' . round((\$mapped/\$total)*100, 1) . '%)' . PHP_EOL;
"
```

### 9️⃣ Match Cardmarket Metacards → TCGCSV Products (NUOVO!)

```bash
# Match prodotti usando idMetacard (1-to-many)
# Inizia con batch piccoli per test
php artisan cardmarket:match-metacards --limit=500 --auto-confirm --threshold=85

# Verifica risultati
php artisan tinker --execute="
\$mappings = App\Models\TcgcsvCardmarketMapping::count();
echo '🎯 TCGCSV products mapped: ' . \$mappings . PHP_EOL;

// Conta varianti accessibili
\$metacards = App\Models\TcgcsvCardmarketMapping::pluck('cardmarket_metacard_id');
\$variants = App\Models\CardmarketProduct::whereIn('id_metacard', \$metacards)->count();
echo '🃏 Cardmarket variants accessible: ' . \$variants . PHP_EOL;
echo '📊 Average variants per card: ' . round(\$variants / \$mappings, 1) . PHP_EOL;
"

# Se i risultati sono buoni, continua con batch più grandi
php artisan cardmarket:match-metacards --limit=2000 --auto-confirm --threshold=85
php artisan cardmarket:match-metacards --limit=5000 --auto-confirm --threshold=85
# ecc...
```

### 🔟 Test Funzionalità

```bash
# Test 1: Verifica carta con varianti Cardmarket
php artisan tinker --execute="
\$card = App\Models\TcgcsvProduct::whereHas('cardmarketMapping')->first();
echo 'TCGCSV Card: ' . \$card->name . PHP_EOL;
echo 'Has mapping: ' . (\$card->cardmarketMapping ? 'YES' : 'NO') . PHP_EOL;
if (\$card->cardmarketMapping) {
    \$variants = \$card->cardmarketVariants;
    echo 'Cardmarket variants: ' . \$variants->count() . PHP_EOL;
    echo 'First variant: ' . \$variants->first()->name . PHP_EOL;
}
"

# Test 2: Verifica prezzi Cardmarket
php artisan tinker --execute="
\$card = App\Models\TcgcsvProduct::whereHas('cardmarketMapping')->first();
\$metacardId = \$card->cardmarketMapping->cardmarket_metacard_id;
\$variant = App\Models\CardmarketProduct::where('id_metacard', \$metacardId)->first();
if (\$variant) {
    \$price = \$variant->latestPriceQuote;
    if (\$price) {
        echo 'Latest price for ' . \$variant->name . ':' . PHP_EOL;
        echo '  Avg: €' . \$price->avg_sell_price . PHP_EOL;
        echo '  Low: €' . \$price->low_price . PHP_EOL;
        echo '  Trend: €' . \$price->trend_price . PHP_EOL;
    }
}
"
```

---

## 📊 Verifica Finale Completa

```bash
php artisan tinker --execute="
echo '=' . str_repeat('=', 60) . PHP_EOL;
echo '  CARDMARKET SYSTEM - STATUS REPORT' . PHP_EOL;
echo '=' . str_repeat('=', 60) . PHP_EOL . PHP_EOL;

echo '📦 PRODUCTS & PRICES:' . PHP_EOL;
echo '  Cardmarket Products: ' . number_format(App\Models\CardmarketProduct::count()) . PHP_EOL;
echo '  Cardmarket Price Quotes: ' . number_format(App\Models\CardmarketPriceQuote::count()) . PHP_EOL;
echo '  Cardmarket Expansions: ' . App\Models\CardmarketExpansion::count() . PHP_EOL;
echo PHP_EOL;

echo '🔗 EXPANSION MATCHING:' . PHP_EOL;
\$expMapped = App\Models\CardmarketExpansion::whereNotNull('tcgcsv_group_id')->count();
\$expTotal = App\Models\CardmarketExpansion::count();
echo '  Mapped: ' . \$expMapped . '/' . \$expTotal . ' (' . round((\$expMapped/\$expTotal)*100, 1) . '%)' . PHP_EOL;
echo PHP_EOL;

echo '🎯 PRODUCT MATCHING (METACARDS):' . PHP_EOL;
\$mappings = App\Models\TcgcsvCardmarketMapping::count();
\$tcgcsvTotal = App\Models\TcgcsvProduct::where('game_id', 1)->count();
echo '  TCGCSV products mapped: ' . number_format(\$mappings) . '/' . number_format(\$tcgcsvTotal) . ' (' . round((\$mappings/\$tcgcsvTotal)*100, 1) . '%)' . PHP_EOL;

\$metacards = App\Models\TcgcsvCardmarketMapping::pluck('cardmarket_metacard_id');
\$variants = App\Models\CardmarketProduct::whereIn('id_metacard', \$metacards)->count();
echo '  Cardmarket variants accessible: ' . number_format(\$variants) . PHP_EOL;
echo '  Average variants per card: ' . round(\$variants / max(\$mappings, 1), 1) . PHP_EOL;
echo PHP_EOL;

echo '💾 DATABASE HEALTH:' . PHP_EOL;
\$tables = [
    'cardmarket_products',
    'cardmarket_price_quotes', 
    'cardmarket_expansions',
    'cardmarket_import_runs',
    'tcgcsv_cardmarket_mapping'
];
foreach (\$tables as \$table) {
    try {
        \$count = DB::table(\$table)->count();
        echo '  ✅ ' . \$table . ': ' . number_format(\$count) . ' records' . PHP_EOL;
    } catch (Exception \$e) {
        echo '  ❌ ' . \$table . ': ERROR' . PHP_EOL;
    }
}
echo PHP_EOL;

echo '=' . str_repeat('=', 60) . PHP_EOL;
"
```

---

## 🔄 Aggiornamento Prezzi Giornaliero (Opzionale)

Per mantenere i prezzi aggiornati, aggiungi al cron:

```bash
# Aggiungi a crontab
crontab -e

# Scarica e importa prezzi ogni giorno alle 3:00 AM
0 3 * * * cd /path/to/production && php artisan cardmarket:download >> /var/log/cardmarket-download.log 2>&1
15 3 * * * cd /path/to/production && php artisan cardmarket:import --only=prices >> /var/log/cardmarket-import.log 2>&1
```

---

## 🚨 Troubleshooting

### Errore Memory Limit durante import
```bash
php -d memory_limit=1G artisan cardmarket:import
```

### Errore "No products imported"
```bash
# Verifica file JSON scaricati
ls -lh storage/app/private/cardmarket/
cat storage/app/private/cardmarket/products_singles_6_*.json | head -100
```

### Match troppo lenti
```bash
# Usa limiti più piccoli
php artisan cardmarket:match-metacards --limit=100 --auto-confirm
```

### Rollback completo
```bash
# Drop tabelle Cardmarket
php artisan migrate:rollback --step=4

# O manualmente
php artisan tinker --execute="
DB::statement('DROP TABLE IF EXISTS tcgcsv_cardmarket_mapping');
DB::statement('DROP TABLE IF EXISTS cardmarket_import_runs');
DB::statement('DROP TABLE IF EXISTS cardmarket_price_quotes');
DB::statement('DROP TABLE IF EXISTS cardmarket_expansions');
DB::statement('DROP TABLE IF EXISTS cardmarket_products');
"

# Restore backup
mysql -u username -p database_name < backup_pre_cardmarket_YYYYMMDD.sql
```

---

## ✅ Checklist Finale

- [ ] Backup database completato
- [ ] Codice deployato
- [ ] 5 migration eseguite
- [ ] 500 Cardmarket expansions seeded
- [ ] ~62,629 Cardmarket products importati
- [ ] ~62,629 Cardmarket price quotes importati
- [ ] ~134 expansions matchate (26.8%)
- [ ] 200+ products matchati con metacards
- [ ] Test funzionalità OK
- [ ] Report status verificato

---

## 📚 Comandi Utili

```bash
# Status completo
php artisan tinker --execute="echo 'Products: ' . App\Models\CardmarketProduct::count() . PHP_EOL;"

# Reimportare solo prezzi
php artisan cardmarket:download
php artisan cardmarket:import --only=prices

# Match incrementale
php artisan cardmarket:match-metacards --limit=1000 --auto-confirm

# Dry-run per test
php artisan cardmarket:match-metacards --dry-run --limit=100
```

---

**Tempo stimato totale: 30-45 minuti**
- Import: 15-20 min
- Matching expansions: 2-3 min
- Matching metacards (batch): 10-20 min
- Verifiche: 5 min
