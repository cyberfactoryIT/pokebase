# 🚀 Production Deployment Checklist

**Data Deploy**: 6 Feb 2026
**Features**: Trial Codes System, Locale Detection, Custom Error Pages

---

## ✅ Pre-Deployment Checks

### 1. Code Quality & Testing
- [x] Tutti i commit pushati su `main`
- [ ] Nessun file `.env` committato
- [ ] Nessun `dd()`, `dump()`, `var_dump()` nel codice
- [ ] Log puliti (nessun `\Log::info()` eccessivo in produzione)
- [ ] Nessun commento TODO critico irrisolto

### 2. Database
- [x] Migration testate in locale
- [ ] Backup database esistente creato
- [ ] Migration reversibili (down() method funzionante)
- [ ] Nessuna migration distruttiva senza conferma

### 3. Configuration
- [ ] `.env` produzione configurato correttamente:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_LOCALE=da`
  - `APP_FALLBACK_LOCALE=da`
  - Stripe keys (live)
  - Database credentials
  - Mail settings
- [ ] `config/app.php` controllato
- [ ] Nessuna key/secret hardcoded

### 4. Security
- [ ] Dependency vulnerabilities controllate (`composer audit`)
- [ ] HTTPS attivo
- [ ] CSRF protection attiva
- [ ] XSS protection attiva
- [ ] SQL injection prevention (eloquent/prepared statements)
- [ ] Rate limiting configurato

### 5. Performance
- [ ] Query ottimizzate (nessun N+1)
- [ ] Cache configurata (Redis/File)
- [ ] Assets compilati (`npm run build`)
- [ ] Immagini ottimizzate

---

## 📦 Deployment Steps

### Step 1: Backup 🔒
```bash
# Sul server - IMPORTANTE: Fare SEMPRE backup prima di deploy
cd /path/to/app

# Backup database
php artisan db:backup
# oppure mysqldump manuale
mysqldump -u USER -p DATABASE > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files (optional)
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz storage/ public/
```

### Step 2: Pull Changes
```bash
# Pull ultimo codice
git pull origin main

# Verifica branch
git branch
git log --oneline -5
```

### Step 3: Dependencies
```bash
# Update composer (se necessario)
composer install --no-dev --optimize-autoloader

# Update npm (se necessario)
npm ci --production
npm run build
```

### Step 4: Database Migrations ⚠️
```bash
# IMPORTANTE: Controllare prima cosa verrà eseguito
php artisan migrate:status

# Dry run (se disponibile) o controllare migration files
cat database/migrations/2026_02_06_*

# Eseguire migration
php artisan migrate --force

# In caso di problemi, rollback rapido
# php artisan migrate:rollback --step=1
```

### Step 5: Clear Caches
```bash
# Clear tutti i cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Storage & Permissions
```bash
# Link storage se non esiste
php artisan storage:link

# Permissions (se necessario)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 7: Queue & Scheduler (se in uso)
```bash
# Restart queue workers se esistono
php artisan queue:restart

# Verifica crontab
crontab -l | grep schedule
```

### Step 8: Optimize
```bash
# Ottimizzazione finale
php artisan optimize
```

---

## 🧪 Post-Deployment Verification

### Critical Tests (Eseguire SUBITO dopo deploy)

#### 1. Homepage & Navigation
- [ ] Homepage carica: https://app.basecard.dk/
- [ ] Navigazione funzionante
- [ ] Language selector funzionante (da/en/it)
- [ ] Logo visibile

#### 2. Authentication
- [ ] **Login** funzionante
- [ ] **Logout** funzionante
- [ ] **Registration** con trial code funzionante
  - Test: Registra utente con codice `WELCOME30`
  - Verifica: Trial attivo, banner visibile
- [ ] Password reset funzionante

#### 3. Trial System
- [ ] Pagina riscatto trial: https://app.basecard.dk/trial/redeem
- [ ] Riscatto codice funzionante
- [ ] Banner trial visibile su dashboard
- [ ] Giorni arrotondati correttamente
- [ ] Link "Contact Support" funzionante

#### 4. Error Pages
- [ ] 404: https://app.basecard.dk/pagina-inesistente
- [ ] 419: Forzare CSRF error
- [ ] 500: (speriamo di no! 🙏)
- [ ] 503: Verificare in maintenance mode

#### 5. Locale Detection
- [ ] Nuovo utente → lingua da sessione
- [ ] Utente esistente → lingua da DB
- [ ] Fallback a `da` se non impostato

#### 6. Core Features
- [ ] Dashboard carica
- [ ] Collection carica
- [ ] Deck builder funzionante
- [ ] Pricing page: https://app.basecard.dk/pricing
- [ ] Contact page: https://app.basecard.dk/contact

#### 7. Payments (CRITICO)
- [ ] Stripe checkout funzionante
- [ ] Upgrade durante trial funzionante
- [ ] Webhook Stripe risponde (check logs)

---

## 📊 Monitoring (Prime 24h)

### Logs da Monitorare
```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx/Apache error logs
tail -f /var/log/nginx/error.log

# Grep per errori
grep -i "error" storage/logs/laravel.log | tail -20
grep -i "exception" storage/logs/laravel.log | tail -20
```

### Key Metrics
- [ ] Response time (< 200ms homepage)
- [ ] Error rate (< 1%)
- [ ] Database connection pool
- [ ] Memory usage server
- [ ] Disk space

### Database Checks
```bash
php artisan tinker
```

```php
// Verifica trial attivi
\App\Models\Organization::whereNotNull('trial_plan_id')
    ->where('trial_expires_at', '>', now())
    ->count();

// Verifica promozioni attive
\App\Models\Promotion::where('type', 'trial')->where('active', true)->count();

// Verifica locale utenti
\App\Models\User::select('locale')->distinct()->pluck('locale');
```

---

## 🚨 Rollback Plan

### Se Qualcosa Va Storto

#### Rollback Rapido (5 minuti)
```bash
# 1. Rollback database (se migration è il problema)
php artisan migrate:rollback --step=1

# 2. Rollback codice
git log --oneline -10
git reset --hard COMMIT_HASH_PRECEDENTE
composer install --no-dev

# 3. Clear cache
php artisan cache:clear && php artisan config:cache

# 4. Ripristina backup DB (caso estremo)
mysql -u USER -p DATABASE < backup_YYYYMMDD_HHMMSS.sql
```

#### Rollback Parziale (Disabilitare feature)
```bash
# Disabilita trial temporaneamente
php artisan tinker
```

```php
// Disabilita tutte le promo trial
\App\Models\Promotion::where('type', 'trial')->update(['active' => false]);
```

---

## 📢 Communication

### Se Deploy con Downtime
**Prima del deploy:**
- [ ] Notifica utenti (email/banner 24h prima)
- [ ] Maintenance page attiva
- [ ] Status page aggiornata

**Dopo il deploy:**
- [ ] Email "New Features" (trial codes)
- [ ] Notifica fine manutenzione
- [ ] Post social media (se applicabile)

### Messaging Template
```
🎉 New Feature: Free Trials!

We're excited to announce our new trial code system!
- Enter promo codes for free premium access
- Beautiful error pages
- Improved language detection

Everything is running smoothly. Enjoy!
```

---

## ✅ Post-Deploy Tasks (Opzionale)

### Settimana 1
- [ ] Monitorare analytics trial redemption
- [ ] Controllare feedback utenti
- [ ] Verificare conversion rate trial → paid
- [ ] Controllare error logs per anomalie

### Query Analytics Utili
```php
// Redemption rate
$totalCodes = \App\Models\Promotion::where('type', 'trial')->count();
$redeemed = \DB::table('organization_promotions')->count();
echo "Redemption rate: " . ($redeemed / $totalCodes * 100) . "%";

// Trial attivi per piano
\DB::table('organizations')
    ->join('pricing_plans', 'organizations.trial_plan_id', '=', 'pricing_plans.id')
    ->whereNotNull('trial_plan_id')
    ->where('trial_expires_at', '>', now())
    ->select('pricing_plans.name', \DB::raw('count(*) as count'))
    ->groupBy('pricing_plans.name')
    ->get();

// Locale distribution
\App\Models\User::select('locale', \DB::raw('count(*) as count'))
    ->groupBy('locale')
    ->get();
```

---

## 🎯 Success Criteria

Deploy considerato **SUCCESS** se:
- ✅ Zero critical errors nelle prime 2h
- ✅ Tutti i test post-deploy passati
- ✅ Nessuna regressione su feature esistenti
- ✅ Response time stabile
- ✅ Almeno 1 trial riscattato con successo nelle prime 24h
- ✅ Nessun rollback necessario

---

## 📞 Emergency Contacts

**In caso di problemi critici:**
- Dev Team: [email/telefono]
- Database Admin: [contatto]
- Hosting Support: Unoeuro support
- Stripe Support: dashboard.stripe.com/support

---

## 📝 Deploy Log Template

```
=== DEPLOYMENT LOG ===
Date: 2026-02-06
Time: [HH:MM]
Deployed by: [Nome]

PRE-DEPLOY CHECKS:
- Backup DB: ✅ backup_20260206_143000.sql
- Code reviewed: ✅
- Staging tested: ✅

DEPLOY STEPS:
1. Git pull: ✅ commit 69ba28c
2. Composer: ✅
3. NPM build: ✅
4. Migrations: ✅ 2 new migrations
5. Cache clear: ✅
6. Permissions: ✅

POST-DEPLOY TESTS:
- Homepage: ✅
- Login: ✅
- Trial system: ✅
- Error pages: ✅
- Payments: ✅

ISSUES:
- None

ROLLBACK:
- Not needed

STATUS: ✅ SUCCESS
```

---

**Remember**: Better safe than sorry. When in doubt, BACKUP! 🔒
