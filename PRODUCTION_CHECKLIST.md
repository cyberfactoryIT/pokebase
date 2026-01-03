# 🚀 Checklist Deploy Produzione

## 📋 Pre-Deploy

### 1. Configurazione .env
```bash
# ⚠️ CRITICAL - Cambiare questi valori prima del deploy
```

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://your-domain.com`
- [ ] `APP_KEY=` (generare nuovo: `php artisan key:generate`)

### 2. Database
- [ ] `DB_HOST=` (host produzione)
- [ ] `DB_DATABASE=` (database produzione)
- [ ] `DB_USERNAME=` (user produzione)
- [ ] `DB_PASSWORD=` (password sicura)

### 3. Log & Cache
- [ ] `LOG_CHANNEL=stack`
- [ ] `LOG_STACK=daily`
- [ ] `LOG_DAILY_DAYS=14` (o più se necessario)
- [ ] `LOG_LEVEL=warning` (o `error` per ridurre log)
- [ ] `CACHE_STORE=redis` (se disponibile, altrimenti `database`)
- [ ] `QUEUE_CONNECTION=database` (o `redis` se disponibile)

### 4. Session & Security
- [ ] `SESSION_DRIVER=database` (o `redis`)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_SAME_SITE=lax`

### 5. API Keys (verificare valori produzione)
- [ ] `CARDMARKET_APP_TOKEN=`
- [ ] `CARDMARKET_APP_SECRET=`
- [ ] `CARDMARKET_ACCESS_TOKEN=`
- [ ] `CARDMARKET_ACCESS_TOKEN_SECRET=`
- [ ] `RAPIDAPI_KEY=`
- [ ] `RAPIDAPI_HOST=`

### 6. Mail
- [ ] `MAIL_MAILER=` (smtp, ses, etc. - non `log`)
- [ ] Configurare SMTP o servizio email
- [ ] Testare invio email

---

## 🔧 Deploy Steps

### 1. Backup
```bash
# Backup database produzione
php artisan backup:run

# Backup .env attuale
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
```

### 2. Pull Codice
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 3. Migrazioni & Cache
```bash
# ⚠️ Mettere sito in manutenzione
php artisan down --refresh=15 --secret="your-secret-token"

# Run migrations
php artisan migrate --force

# Clear & optimize
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# ✅ Riattivare sito
php artisan up
```

### 4. Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Restart Services
```bash
# PHP-FPM
sudo systemctl restart php8.2-fpm

# Queue workers (se usi Supervisor)
sudo supervisorctl restart all

# Nginx/Apache
sudo systemctl restart nginx
```

---

## 📊 ETL Pipeline Produzione

### 1. Verifica Schedule
```bash
# Test schedule senza eseguire
php artisan schedule:list

# Verifica timezone
php artisan tinker --execute="echo config('app.timezone');"
```

### 2. Test Singoli Comandi
```bash
# Test ogni comando individualmente
php artisan cardmarket:etl
php artisan tcgcsv:import-pokemon
php artisan rapidapi:import-episodes pokemon
php artisan rapidapi:sync-cards pokemon
php artisan tcgdx:import
php artisan rapidapi:map-episodes
php artisan tcgdex:map
php artisan tcgcsv:enrich --all
```

### 3. Monitoraggio Pipeline
```bash
# Setup cron
crontab -e
# Aggiungere:
# * * * * * cd /path/to/pokebase && php artisan schedule:run >> /dev/null 2>&1

# Monitorare logs
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Verificare pipeline_runs
php artisan tinker --execute="
\App\Models\PipelineRun::latest()->take(10)->get(['source', 'started_at', 'finished_at', 'status', 'duration_seconds'])->each(fn(\$r) => print_r(\$r->toArray()));
"
```

---

## ✅ Post-Deploy Verification

### 1. Applicazione
- [ ] Sito carica correttamente
- [ ] Login funziona
- [ ] Dashboard accessibile
- [ ] API endpoints rispondono

### 2. Database
- [ ] Connessione funzionante
- [ ] Migrations applicate: `php artisan migrate:status`
- [ ] Dati presenti nelle tabelle principali

### 3. ETL Pipeline
- [ ] Schedule attivo: `php artisan schedule:list`
- [ ] Comandi eseguibili singolarmente
- [ ] pipeline_runs registra correttamente

### 4. Logs & Monitoring
- [ ] Log rotazione giornaliera funzionante
- [ ] Spazio disco sufficiente per log (14+ giorni)
- [ ] Monitoring errori attivo

### 5. Performance
- [ ] Cache configurata (config, routes, views)
- [ ] OPcache attivo (se disponibile)
- [ ] Redis/Memcached attivo (se configurato)

---

## 🚨 Rollback Plan

Se qualcosa va storto:

```bash
# 1. Mettere in manutenzione
php artisan down

# 2. Ripristinare .env
cp .env.backup.YYYYMMDD_HHMMSS .env

# 3. Rollback database (se necessario)
php artisan migrate:rollback --step=1

# 4. Ripristinare codice
git reset --hard HEAD~1

# 5. Clear cache
php artisan config:clear && php artisan cache:clear

# 6. Riattivare
php artisan up
```

---

## 📞 Contatti Emergenza

- **Developer**: [nome]
- **DevOps**: [nome]
- **Database Admin**: [nome]

---

## 📝 Note

- Mantenere questa checklist aggiornata ad ogni deploy
- Documentare eventuali problemi incontrati
- Aggiornare versione in `composer.json` / `package.json`
