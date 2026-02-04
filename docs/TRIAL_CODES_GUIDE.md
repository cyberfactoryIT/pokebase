# Trial Codes System - Complete Guide

## 📖 Overview

Sistema di codici promozionali per offrire trial gratuiti dei piani Premium/Advanced senza passare da Stripe.

### Flusso Utente

1. **Utente riceve codice** (es: `TRIAL30PREMIUM`)
2. **Riscatta codice** → `/trial/redeem`
3. **Ottiene accesso** → Piano Premium/Advanced gratis per X giorni
4. **Durante trial**:
   - Usa tutte le funzionalità premium
   - Può attivare abbonamento Stripe in qualsiasi momento
   - Vede banner con countdown alla scadenza
5. **Scadenza trial**:
   - Se ha attivato Stripe → Continua con abbonamento pagante
   - Se NON ha attivato → Torna a Free + Banner upselling

---

## 🗄️ Database Schema

### Tabella `promotions` (estesa)

```sql
type ENUM('percent', 'fixed', 'trial')  -- Nuovo tipo 'trial'
trial_plan_id → FK pricing_plans        -- Quale piano dare in trial
trial_duration_days INT                 -- Durata trial in giorni (30, 60, 90...)
```

### Tabella `organizations` (estesa)

```sql
trial_plan_id → FK pricing_plans        -- Piano trial attivo
trial_expires_at TIMESTAMP              -- Quando scade il trial
trial_promotion_id → FK promotions      -- Quale promo ha usato
```

---

## 🎯 Come Creare un Codice Trial

### Metodo 1: Tramite Tinker (Veloce)

```bash
php artisan tinker
```

```php
// Crea un trial di 30 giorni per Premium
$premium = \App\Models\PricingPlan::where('code', 'premium')->first();

\App\Models\Promotion::create([
    'name' => 'Trial 30 Giorni Premium',
    'code' => 'TRIAL30PREMIUM',
    'type' => 'trial',
    'value' => 0, // Non usato per trial
    'trial_plan_id' => $premium->id,
    'trial_duration_days' => 30,
    'active' => true,
    'starts_at' => now(),
    'ends_at' => now()->addMonths(3), // Valido per 3 mesi
    'max_redemptions' => 100, // Max 100 persone
    'new_orgs_only' => false,
]);

// Verifica
\App\Models\Promotion::where('code', 'TRIAL30PREMIUM')->first();
```

### Metodo 2: Esempi Comuni

```php
// Trial 7 giorni Advanced (influencer trial)
\App\Models\Promotion::create([
    'name' => 'Influencer Trial 7 Days',
    'code' => 'INFLUENCER7',
    'type' => 'trial',
    'value' => 0,
    'trial_plan_id' => \App\Models\PricingPlan::where('code', 'advanced')->first()->id,
    'trial_duration_days' => 7,
    'active' => true,
    'max_redemptions' => 50,
]);

// Trial 60 giorni Premium (partnership)
\App\Models\Promotion::create([
    'name' => 'Partner Trial 60 Days',
    'code' => 'PARTNER60',
    'type' => 'trial',
    'value' => 0,
    'trial_plan_id' => \App\Models\PricingPlan::where('code', 'premium')->first()->id,
    'trial_duration_days' => 60,
    'active' => true,
    'ends_at' => now()->addYear(),
]);
```

---

## 🔄 Flusso Tecnico

### 1. Utente Riscatta Codice

```
GET /trial/redeem → Form
POST /trial/redeem → TrialCodeController@redeem
    ↓
PromotionEngine::redeemTrialCode()
    - Valida codice
    - Controlla scadenze/limiti
    - Controlla se già usato
    - Attiva trial
    ↓
Organization::activateTrial()
    - Imposta trial_plan_id
    - Imposta trial_expires_at
    - Salva trial_promotion_id
```

### 2. Check Permessi

Il sistema usa `Organization::getEffectivePlan()`:

```php
// Restituisce trial plan se attivo, altrimenti current plan
$effectivePlan = $org->getEffectivePlan();
```

Usa questo metodo in tutto il codice per verificare le feature permissions.

### 3. Scadenza Automatica

```
Cron (00:30 daily) → trials:expire command
    ↓
Trova organizations con trial_expires_at <= now()
    ↓
Per ciascuna:
    - Chiama Organization::endTrial()
    - Azzera trial_plan_id, trial_expires_at, trial_promotion_id
    - Log evento
    - (TODO) Invia email con offerta upgrade
```

### 4. Upgrade Durante Trial

```
User clicca "Upgrade" → Checkout Stripe
    ↓
CheckoutController::processPayment()
    - Crea subscription Stripe
    - Salva stripe_subscription_id
    - Se isOnTrial() → endTrial()
```

---

## 🎨 UI Components

### Banner Upselling

**Posizioni:**
- Dashboard (sempre visibile)
- Billing page
- Collection (se usa features premium)

**Stati:**
- **Active Trial** (> 3 giorni) → Blue/Purple banner informativo
- **Urgent Trial** (≤ 3 giorni) → Orange/Red banner urgente
- **Expired Trial** → Gray/Yellow banner offerta

### Form Riscatto

**URL:** `/trial/redeem`

**Validazioni:**
- Codice valido e attivo
- Non già usato da questa org
- Non già in trial
- Non ha abbonamento Stripe attivo

---

## 📊 Reporting & Analytics

### Query Utili

```php
// Trial attivi
\App\Models\Organization::whereNotNull('trial_plan_id')
    ->where('trial_expires_at', '>', now())
    ->count();

// Trial scaduti oggi
\App\Models\Organization::whereNotNull('trial_plan_id')
    ->whereDate('trial_expires_at', today())
    ->get();

// Tasso conversione per codice
$promo = \App\Models\Promotion::where('code', 'TRIAL30PREMIUM')->first();
$totalRedemptions = $promo->organizations()->count();
$converted = $promo->organizations()
    ->whereNotNull('stripe_subscription_id')
    ->count();
$conversionRate = $converted / $totalRedemptions * 100;
```

---

## ⚙️ Comandi Artisan

```bash
# Vedere trial in scadenza (dry run)
php artisan trials:expire --dry-run

# Far scadere i trial manualmente
php artisan trials:expire

# Vedere stato trial di un'organizzazione
php artisan tinker
$org = Organization::find(1);
$org->isOnTrial();
$org->getEffectivePlan();
```

---

## 🔐 Security & Limits

### Validazioni PromotionEngine

- ✅ Codice case-insensitive
- ✅ Check date validità (starts_at, ends_at)
- ✅ Max redemptions globale
- ✅ Per-org limit
- ✅ Solo nuove org (se new_orgs_only)
- ✅ Non cumula con altri trial
- ✅ Non disponibile se già subscription attiva

### Rate Limiting

Considera di aggiungere rate limiting su `/trial/redeem` per prevenire brute force:

```php
Route::post('/trial/redeem', [TrialCodeController::class, 'redeem'])
    ->middleware(['auth', 'throttle:5,1']); // Max 5 tentativi al minuto
```

---

## 📧 Email Notifications (TODO)

**Da implementare:**

1. **Trial attivato** → Email di benvenuto con features
2. **3 giorni prima scadenza** → Reminder + CTA upgrade
3. **Trial scaduto** → Offerta speciale per convertire
4. **Upgrade completato** → Conferma e ringraziamento

---

## 🎯 Best Practices

### Codici Efficaci

- ✅ **Chiari**: `TRIAL30PREMIUM` (durata + piano)
- ✅ **Tracciabili**: `INFLUENCER_MARIO` (source tracking)
- ✅ **Memorabili**: `WELCOME2024`
- ❌ Evita: `X7Y2Z9` (troppo difficile)

### Durate Consigliate

- **7 giorni** → Quick taste, influencer campaigns
- **30 giorni** → Standard trial, optimal conversion
- **60+ giorni** → Partnership, special campaigns

### Limiti

- Imposta `max_redemptions` per controllare i costi
- Usa `ends_at` per campagne a tempo
- Monitora conversione rate e ajusta

---

## 🚀 Quick Start

### 1. Esegui Migration

```bash
php artisan migrate
```

### 2. Crea Primo Codice Trial

```bash
php artisan tinker
```

```php
$premium = \App\Models\PricingPlan::where('code', 'premium')->first();
\App\Models\Promotion::create([
    'name' => 'Trial 30 Giorni Premium',
    'code' => 'WELCOME30',
    'type' => 'trial',
    'value' => 0,
    'trial_plan_id' => $premium->id,
    'trial_duration_days' => 30,
    'active' => true,
    'max_redemptions' => 1000,
]);
```

### 3. Testa

1. Vai su `/trial/redeem`
2. Inserisci `WELCOME30`
3. Verifica su dashboard il banner trial attivo

### 4. Verifica Cron

```bash
# Aggiungi al crontab (già schedulato in console.php)
# * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📈 Analytics & Optimization

### KPIs da Monitorare

1. **Trial Activation Rate** - Quanti codici vengono usati
2. **Conversion Rate** - Quanti trial convertono in paganti
3. **Average Days to Convert** - Quanto tempo serve per convertire
4. **Feature Usage** - Quali features usano di più in trial

### Query Analytics

```php
// Trial attivi per piano
DB::table('organizations')
    ->join('pricing_plans', 'organizations.trial_plan_id', '=', 'pricing_plans.id')
    ->whereNotNull('trial_plan_id')
    ->where('trial_expires_at', '>', now())
    ->groupBy('pricing_plans.name')
    ->selectRaw('pricing_plans.name, COUNT(*) as count')
    ->get();
```

---

## 🐛 Troubleshooting

### Problema: "Invalid trial code"

- Verifica che il codice esista: `Promotion::where('code', 'XXX')->first()`
- Verifica che sia attivo: `active = true`
- Verifica date validità

### Problema: Trial non scade

- Verifica cron: `php artisan schedule:list`
- Esegui manualmente: `php artisan trials:expire`
- Controlla log

### Problema: Banner non compare

- Verifica `isOnTrial()` return true
- Verifica component è incluso in view
- Clear cache: `php artisan view:clear`

---

## ✅ Checklist Deploy

- [ ] Esegui migration: `php artisan migrate`
- [ ] Crea codici trial iniziali
- [ ] Testa riscatto codice
- [ ] Verifica banner su dashboard
- [ ] Testa scadenza trial (cambia manualmente data)
- [ ] Verifica cron è attivo: `crontab -l`
- [ ] Monitora log per primi giorni

---

**Sistema pronto per produzione!** 🎉
