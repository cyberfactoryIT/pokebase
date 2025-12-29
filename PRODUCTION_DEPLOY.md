# 🚀 Deploy Produzione - RapidAPI Integration

> **Data:** 29 Dicembre 2025  
> **Stato:** Pronto per deploy completo in produzione

---

## 📊 Stato Attuale

### Database
- ✅ **2,000 carte** RapidAPI importate (11/173 episodi = 6.4%)
- ✅ **1,998 carte** mappate a TCGCSV (99.9%)
- ✅ **1,998 carte** arricchite con dati HD
- ✅ **1,996 carte** con prezzi EUR Cardmarket

### Features Implementate
- ✅ Sistema valorizzazione EUR/USD per collezione e deck
- ✅ Toggle persistente valuta (localStorage)
- ✅ HD images da RapidAPI
- ✅ Link esterni (TCGO, TCGPlayer, Cardmarket)
- ✅ Dettagli carta (HP, artist, supertype, rarity)

### Quota API
- 🟡 **Usata:** ~2,700/3,000 (90%)
- 🟢 **Disponibile domani:** 3,000 chiamate fresche
- 📊 **Rimanente da importare:** ~17,818 carte (~891 pagine)

---

## 🔧 Comandi Deploy Produzione

### 1️⃣ Script Automatico (RACCOMANDATO)
```bash
cd /path/to/pokebase
./deploy-rapidapi-sync.sh
```

**Questo script fa tutto automaticamente:**
- Import 50 pagine RapidAPI
- Mapping automatico carte
- Enrichment dati TCGCSV
- Retry su errori (3 tentativi)
- Logging completo in `/tmp/rapidapi_sync/`

---

### 2️⃣ Comandi Manuali Step-by-Step

#### Import RapidAPI Cards
```bash
# Import batch sicuro (50 pagine = ~1000 carte)
php artisan rapidapi:test pokemon --pages=50 --save

# Per import più grande (usa con cautela)
php artisan rapidapi:test pokemon --pages=100 --save
```

#### Mapping Automatico
```bash
# Mappa le nuove carte RapidAPI → TCGCSV
php artisan cards:map
```

#### Enrichment Dati
```bash
# Trasferisce HD images, prezzi EUR, links, dettagli
php artisan tcgcsv:enrich --all
```

#### Verifica Stato
```bash
php artisan tinker --execute="
\$rapidapi = DB::table('rapidapi_cards')->count();
\$mapped = DB::table('card_mappings')->count();
\$enriched = DB::table('tcgcsv_products')->whereNotNull('hd_image_url')->count();
\$prices = DB::table('tcgcsv_products')->whereNotNull('cardmarket_price_eur')->count();

echo '🎴 RapidAPI: ' . number_format(\$rapidapi) . PHP_EOL;
echo '🔗 Mappate: ' . number_format(\$mapped) . PHP_EOL;
echo '🖼️  HD Images: ' . number_format(\$enriched) . PHP_EOL;
echo '💰 Prezzi EUR: ' . number_format(\$prices) . PHP_EOL;
"
```

---

## 📅 Piano Import Completo

### Strategia Giornaliera
Con 3,000 chiamate/giorno disponibili:

```bash
# Giorno 1 (Domani - 30 Dicembre)
./deploy-rapidapi-sync.sh  # 50 pagine
# Ripeti 18 volte per coprire ~900 pagine
# Totale: ~18,000 carte importate

# Oppure usa loop automatico:
for i in {1..18}; do
  echo "=== Batch $i/18 ==="
  php artisan rapidapi:test pokemon --pages=50 --save
  php artisan cards:map
  php artisan tcgcsv:enrich --all
  sleep 10
done
```

### Tempi Stimati
- **Import 50 pagine:** ~2-3 minuti
- **Mapping:** ~30 secondi
- **Enrichment:** ~1 minuto
- **Totale per batch:** ~4 minuti
- **Import completo (18 batch):** ~72 minuti

---

## 🔍 Monitoring e Troubleshooting

### Check Health
```bash
# Verifica servizi
php artisan queue:work --once  # Test queue
php artisan schedule:test      # Test scheduler

# Log ultimi errori
tail -100 storage/logs/laravel.log

# Log sync RapidAPI
tail -100 /tmp/rapidapi_sync/sync_*.log
```

### Problemi Comuni

#### 1. Errore Duplicate Entry
**Causa:** Carta già esistente nel DB  
**Soluzione:** Normale, il sistema aggiorna invece di inserire

#### 2. Timeout API
**Causa:** Rate limiting RapidAPI  
**Soluzione:** Riduci batch size o aumenta sleep tra batch

#### 3. Memoria Insufficiente
**Causa:** Batch troppo grande  
**Soluzione:** Usa `--pages=50` invece di 100+

#### 4. Quota API Esaurita
**Causa:** Limite 3,000/giorno raggiunto  
**Soluzione:** Attendi reset (00:00 UTC)

---

## 📈 Verifica Funzionamento

### Test Valorizzazione EUR/USD

1. **Vai alla collezione:** `https://your-domain.com/collection`
2. **Verifica card "Collection Value"** con toggle EUR/USD
3. **Testa preferenza persistente** (ricarica pagina)

### Test Deck Valuation

1. **Vai a:** `https://your-domain.com/pokemon/deck-valuation`
2. **Crea un deck** con alcune carte
3. **Verifica step 3** mostra prezzi EUR/USD con toggle

### Test HD Images

1. **Vai a una carta:** `https://your-domain.com/tcg/cards/{id}`
2. **Verifica immagine HD** caricata
3. **Check external links** (TCGO, TCGPlayer, Cardmarket)

---

## 🔐 Sicurezza Produzione

### Environment Variables
```env
# .env produzione
RAPIDAPI_KEY=your_production_key
RAPIDAPI_HOST=pokemon-tcg-card-prices.p.rapidapi.com

# Queue per async processing
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=info
```

### Permissions
```bash
# Assicurati che i log siano scrivibili
chmod -R 775 storage/logs
chmod -R 775 /tmp/rapidapi_sync

# Owner corretto
chown -R www-data:www-data storage
```

---

## 🎯 Checklist Deploy

- [ ] Verifica `.env` con chiavi produzione
- [ ] Test connessione database
- [ ] Backup database pre-import
- [ ] Esegui `./deploy-rapidapi-sync.sh`
- [ ] Verifica log in `/tmp/rapidapi_sync/`
- [ ] Check carte importate con tinker
- [ ] Test valorizzazione EUR/USD in UI
- [ ] Test HD images su carte random
- [ ] Verifica link esterni funzionanti
- [ ] Monitor quota API rimanente

---

## 📞 Comandi Rapidi

```bash
# Status completo
php artisan tinker --execute="echo 'Cards: ' . DB::table('rapidapi_cards')->count() . ' | Mapped: ' . DB::table('card_mappings')->count() . ' | Enriched: ' . DB::table('tcgcsv_products')->whereNotNull('hd_image_url')->count();"

# Import + processo completo
./deploy-rapidapi-sync.sh

# Solo import
php artisan rapidapi:test pokemon --pages=50 --save

# Solo mapping
php artisan cards:map

# Solo enrichment
php artisan tcgcsv:enrich --all

# Clear cache dopo deploy
php artisan optimize:clear
```

---

## 📝 Note Importanti

1. **Prima volta sul server:** Esegui `composer install --no-dev --optimize-autoloader`
2. **Migrations:** Già eseguite in locale, verificare con `php artisan migrate:status`
3. **Assets:** Esegui `npm run build` per compilare JS/CSS con toggle valuta
4. **Cache:** Dopo ogni deploy: `php artisan optimize:clear`

---

## 🎉 Prossimi Passi

1. **Import completo** (domani con quota fresca)
2. **Monitoring prestazioni** in produzione
3. **Setup scheduler** per sync automatico giornaliero
4. **Analytics** utilizzo feature EUR/USD

---

**Creato:** 29/12/2025  
**Testato in:** Locale (macOS)  
**Pronto per:** Deploy Produzione Linux

🚀 Tutto pronto per domani!
