# TODO - Basecard Development

*Last Updated: 31 January 2026*

---

## 🔄 Price Caching System (Future Implementation)

### Problema
Attualmente ogni query su collezioni/deck richiede join multipli con tabelle prezzi (tcgcsv_prices, cardmarket_prices, tcgdx_cards), causando:
- Query complesse con 3-4 join
- Performance degradation con migliaia di carte utente
- Overhead su dashboard e aggregazioni

### Soluzione Proposta: Denormalizzazione Prezzi
Aggiungere colonne cache alle tabelle `user_collection` e `deck_cards`:
```sql
cached_price DECIMAL(10,2) NULLABLE
cached_price_currency VARCHAR(3) DEFAULT 'USD'  
cached_price_updated_at TIMESTAMP NULLABLE
```

### Architettura
1. **ETL Prezzi** (esistente): Aggiorna staging tables (tcgcsv_prices, cardmarket_prices, tcgdx_cards)
2. **NEW: Price Cache Refresh Command**: `php artisan prices:refresh-cache`
   - Legge solo carte effettivamente in uso (in collezioni/deck attivi)
   - Aggiorna cached_price da source appropriato (TCGCSV/TCGDEX/Cardmarket)
   - Rispetta preferenza USD/EUR per utente
   - Timestamp ultimo update
   - Update incrementale (skip se < 12 ore)
3. **Schedule**: Laravel scheduler 1-2 volte/giorno dopo ETL
   ```php
   $schedule->command('prices:refresh-cache')->daily()->after('etl:update-prices');
   ```

### Query Before/After
**BEFORE** (complessa):
```sql
SELECT SUM(uc.quantity * COALESCE(cp.market, cm.avg_price))
FROM user_collection uc
LEFT JOIN tcgcsv_products p ON uc.product_id = p.product_id
LEFT JOIN tcgcsv_prices cp ON p.product_id = cp.product_id
LEFT JOIN cardmarket_products cm ON ...
WHERE uc.user_id = ?
```

**AFTER** (semplice):
```sql
SELECT SUM(quantity * cached_price)
FROM user_collection
WHERE user_id = ?
```

### Implementazione Steps
- [ ] Migration: aggiungere colonne cached_price a user_collection e deck_cards
- [ ] Command: `app/Console/Commands/RefreshPriceCache.php`
- [ ] Logic: Dual backend support (TCGCSV product_id + TCGDEX tcgdex_card_id)
- [ ] Fallback: Se cached_price NULL, fallback a query real-time (backward compatibility)
- [ ] Update Controllers: CollectionController, DeckController per usare cached prices
- [ ] Schedule: Registrare command in Kernel.php
- [ ] Monitoring: Log update count e failure rate
- [ ] Testing: Verificare performance improvement su dataset reale

### Vantaggi
- ✅ Performance: Query 10-100x più veloci
- ✅ Snapshot pricing: Storico valore al momento aggiunta
- ✅ Scalabilità: Indipendente da volume carte database
- ✅ Affidabilità: Ultimo prezzo noto anche se API fallisce
- ✅ Tracking: Guadagni/perdite basati su prezzo iniziale

### Note
- Aggiornamento 1-2 volte/giorno è sufficiente (prezzi carte non cambiano ogni minuto)
- Solo carte in uso vengono aggiornate (ottimizzazione)
- Rispetta user preferred_currency per USD/EUR

---

## 📝 Other TODOs
(Aggiungere qui altri task futuri)
