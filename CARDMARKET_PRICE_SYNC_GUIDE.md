# CardMarket Price Sync System

Sistema di sincronizzazione prezzi CardMarket con staging area, storicizzazione e automazione giornaliera.

## Architettura

### Fonte Dati: CardMarket S3 Buckets

CardMarket pubblica i dati su S3 pubblico (nessun API key necessario!):

- **Products**: `https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_{gameId}.json`
- **Prices**: `https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_{gameId}.json`

Game IDs:
- Lorcana: 19
- One Piece: 26

**Vantaggi**:
- ✅ Nessun rate limit API
- ✅ File completi (tutti i prodotti/prezzi)
- ✅ Aggiornati giornalmente da CardMarket
- ✅ Gratuito (S3 pubblico)

### Tabelle Database

1. **staging_cmapi_products** - Prodotti fetched da CardMarket (staging)
   - Contiene: cardmarket_id, game, name, set_name, rarity, status (pending/validated/error)
   - Usata per validazione prima di promuovere a produzione

2. **staging_cmapi_prices** - Prezzi da CardMarket (staging)
   - Contiene: prezzi per language, condition, trend, disponibilità
   - Collegata a staging_cmapi_products

3. **cmapi_price_history** - Storico prezzi (production)
   - Contiene: prezzi validati per ogni carta, lingua, condizione, data
   - Constraint unico: (card_id, language, condition, price_date)
   - Usata per grafici trend storici

### Flusso Dati

```
tcgcsv:import (Lorcana)
    ↓
CardMarket S3 download (products + prices)
    ↓
staging_cmapi_products + staging_cmapi_prices
    ↓
Validazione e matching con cmapi_cards
    ↓
cmapi_cards (update price_eur) + cmapi_price_history
```

## Setup

### 1. Nessuna Configurazione API Necessaria!

I file S3 sono pubblici, non serve API key. Rimuovere dalla configurazione:

```php
// NON PIÙ NECESSARIO in config/services.php
// 'cardmarket' => [...]
```

### 2. Eseguire Migrations

```bash
php artisan migrate
```

Crea:
- staging_cmapi_products
- staging_cmapi_prices
- cmapi_price_history

### 3. Setup Cron (Automazione Giornaliera)

Aggiungi in crontab:

```bash
# CardMarket Daily Sync - 02:00 AM ogni giorno
0 2 * * * cd /path/to/pokebase && ./sync-cardmarket-daily.sh >> storage/logs/cron-cardmarket.log 2>&1
```

Oppure in Laravel scheduler (`app/Console/Kernel.php`):

```php
protected function schedule(Schedule $schedule)
{
    // CardMarket daily sync
    $schedule->command('cardmarket:sync-prices', ['--game' => 'lorcana', '--promote', '--clean'])
        ->dailyAt('02:00')
        ->appendOutputTo(storage_path('logs/cardmarket-sync.log'));
}
```

## Comandi Manuali

### Import Lorcana da tcgcsv

```bash
php artisan tcgcsv:import --game=lorcana
```

### Sync Prezzi CardMarket

```bash
# Download da S3 e import in staging
php artisan cardmarket:sync-prices --game=lorcana

# Download + Promote to production
php artisan cardmarket:sync-prices --game=lorcana --promote

# Download + Promote + Clean old staging
php artisan cardmarket:sync-prices --game=lorcana --promote --clean
```

**Nota**: Nessun rate limit! Download completo di ~20-30MB in pochi secondi.

### Script Shell Completo

```bash
./sync-cardmarket-daily.sh
```

Esegue:
1. tcgcsv import Lorcana
2. CardMarket S3 download (products + prices)
3. Promote staging to production
4. Clean old staging data (>7 days)

## Workflow di Sviluppo

### Test Download S3

```bash
# Test download in tinker
php artisan tinker
>>> $service = new App\Services\Cmapi\CardMarketPriceSyncService();
>>> $stats = $service->importFromS3('lorcana');
>>> print_r($stats);
```

### Visualizzare Staging Data

```sql
-- Prodotti in staging
SELECT * FROM staging_cmapi_products WHERE status = 'pending';

-- Prezzi in staging per un prodotto
SELECT sp.*, scp.language, scp.condition, scp.price_eur
FROM staging_cmapi_prices scp
JOIN staging_cmapi_products sp ON scp.staging_product_id = sp.id
WHERE sp.cardmarket_id = '123456';
```

### Visualizzare Storico Prezzi

```sql
-- Trend prezzo per una carta (ultimi 30 giorni)
SELECT price_date, language, condition, price_eur, price_trend_eur
FROM cmapi_price_history
WHERE cmapi_card_id = 1
  AND language = 'en'
  AND condition = 'NM'
  AND price_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY price_date DESC;
```

## Monitoraggio

### Log Files

- `storage/logs/cardmarket-sync-YYYYMMDD.log` - Daily sync logs
- `storage/logs/laravel.log` - General app logs (errors)

### Check Staging Status

```bash
# Contare staging records per status
php artisan tinker
>>> DB::table('staging_cmapi_products')->groupBy('status')->selectRaw('status, count(*) as count')->get();
```

### Check Price History

```bash
# Ultime date con prezzi registrati
php artisan tinker
>>> DB::table('cmapi_price_history')->distinct()->pluck('price_date')->sortDesc()->take(10);
```

## Troubleshooting

### Errore: "Failed to download from S3"

Problema di connessione o URL cambiato.

Soluzione:
1. Testare URL manualmente: `curl https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_19.json | jq '.[0]'`
2. Verificare log: `tail -f storage/logs/laravel.log`
3. Aumentare timeout in service se necessario

### Errore: "Card not found for cardmarket_id"

Matching fallisce tra staging e cmapi_cards.

Soluzione:
1. Verificare che tcgcsv:import abbia importato le carte
2. Controllare matching logic (set_name + number come fallback)
3. Aggiungere cardmarket_id manualmente: `UPDATE cmapi_cards SET cardmarket_id='123' WHERE...`

### File S3 Troppo Grandi

Accumulazione di staging data non pulita.

Soluzione:

```bash
# Pulire manualmente
php artisan tinker
>>> DB::table('staging_cmapi_products')->where('status', 'validated')->where('updated_at', '<', now()->subDays(7))->delete();
```

## Separazione Pokemon vs Lorcana/One Piece

Questa implementazione è **completamente separata** da Pokemon:

- **Pokemon**: Usa `tcgdex_*` tables (TCGDEX API)
- **Lorcana/One Piece**: Usa `cmapi_*` tables (CardMarket API via RapidAPI)

Nessuna interferenza tra i due sistemi.

## Future Enhancements

- [ ] Notifiche admin via email su errori sync
- [ ] Dashboard web per monitoring staging/production
- [ ] API endpoint per chart prezzi storici
- [ ] Support per altre lingue/condizioni oltre NM/EN
- [ ] Alert prezzi: notifica se prezzo cambia >X%
