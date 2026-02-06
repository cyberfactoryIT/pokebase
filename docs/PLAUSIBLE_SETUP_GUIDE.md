# 🎯 Plausible Analytics - Setup Completo per Basecard

## 📋 Prerequisiti
- Accesso al server di produzione
- Accesso al file `.env` di produzione
- 5 minuti di tempo
- Email per registrazione Plausible

---

## 🚀 Step 1: Registrazione Plausible (2 minuti)

### 1.1 Crea Account
1. Vai su **https://plausible.io/register**
2. Inserisci email aziendale (es: `barbara@basecard.dk`)
3. Scegli password sicura
4. Clicca "Create account"

### 1.2 Scegli Piano
- **Trial 30 giorni gratuiti** (no credit card richiesta)
- Dopo il trial:
  - **€9/mese** per 10K pageviews/mese
  - **€19/mese** per 100K pageviews/mese

### 1.3 Aggiungi Dominio
1. Nel wizard di setup, inserisci: `basecard.dk`
2. Seleziona timezone: **Europe/Copenhagen**
3. Clicca "Add site"

✅ **Account creato!**

---

## ⚙️ Step 2: Configurazione Server (3 minuti)

### 2.1 Connettiti al Server
```bash
# SSH nel server di produzione
ssh user@your-server.com

# Naviga nella directory del progetto
cd /path/to/basecard
```

### 2.2 Modifica `.env`
```bash
# Apri il file .env
nano .env

# Oppure con vim
vim .env
```

### 2.3 Aggiungi/Modifica queste righe
```env
# Analytics Configuration
ANALYTICS_TYPE=plausible
ANALYTICS_ID=basecard.dk
ANALYTICS_ENABLED=true
```

**IMPORTANTE:** 
- `ANALYTICS_ID` deve essere **esattamente il dominio** che hai registrato su Plausible
- Se usi subdomain (es: `app.basecard.dk`), metti quello invece

### 2.4 Salva e Esci
```bash
# Con nano: CTRL+O, ENTER, CTRL+X
# Con vim: ESC, :wq, ENTER
```

### 2.5 Clear Cache Laravel
```bash
# Clear config cache
php artisan config:clear

# Rebuild config cache
php artisan config:cache

# Verifica che la config sia caricata
php artisan tinker
>>> config('services.analytics')
# Output: ["type" => "plausible", "id" => "basecard.dk", "enabled" => true]
>>> exit
```

✅ **Server configurato!**

---

## 🧪 Step 3: Test e Verifica (2 minuti)

### 3.1 Test sul Browser

1. **Apri il sito in incognito** (per vedere il banner cookie)
   ```
   https://basecard.dk
   ```

2. **Verifica il banner appare** in basso con:
   - Testo: "Vi bruger cookies 🍪"
   - Bottone "Acceptér alle"

3. **Clicca "Acceptér alle"**

4. **Apri DevTools** (F12 o CMD+Option+I)
   - Tab **Console**: Nessun errore
   - Tab **Network**: 
     - Filtra per `plausible`
     - Dovresti vedere: `https://plausible.io/js/script.js` (status 200)
     - Poi: `https://plausible.io/api/event` (status 202)

5. **Naviga altre pagine** (es: pricing, contact)
   - Ogni pageview dovrebbe creare un `api/event` call

### 3.2 Verifica su Plausible Dashboard

1. **Torna su Plausible** → https://plausible.io/basecard.dk
2. Dovresti vedere:
   - **Current visitors**: 1 (tu!)
   - **Top pages**: La pagina che stai visitando
   - **Devices**: Il tuo device

3. **Se non vedi nulla:**
   - Aspetta 10-20 secondi (real-time ha leggero delay)
   - Ricarica la dashboard
   - Verifica che stai visitando il dominio giusto (no `localhost`)

### 3.3 Test localStorage (Console Browser)

```javascript
// Verifica che il consenso sia salvato
JSON.parse(localStorage.getItem('cookieConsent'))
// Output: {necessary: true, analytics: true, marketing: true}

// Test: rimuovi consenso e ricarica
localStorage.removeItem('cookieConsent')
location.reload()
// Banner dovrebbe riapparire
```

✅ **Tutto funziona!**

---

## 📊 Step 4: Dashboard Plausible Overview

### Metriche Principali (tutto real-time)

1. **Visitors** - Visitatori unici (cookieless tracking via IP+UserAgent hash)
2. **Pageviews** - Pagine viste totali
3. **Bounce rate** - % utenti che lasciano dopo 1 pagina
4. **Visit duration** - Tempo medio sul sito

### Report Disponibili

- **Top Pages** - Pagine più visitate
- **Entry Pages** - Da dove entrano gli utenti
- **Exit Pages** - Dove escono
- **Referrers** - Da dove arrivano (Google, Facebook, direct, etc.)
- **Countries** - Geolocalizzazione utenti
- **Devices** - Desktop/Mobile/Tablet
- **Browsers** - Chrome, Safari, Firefox, etc.
- **Operating Systems** - Windows, macOS, iOS, Android

### Features Avanzate (Optional)

#### 4.1 Goals (Conversioni)
Setup su Plausible → Settings → Goals

Esempi:
- **Pageview Goal**: `/pricing` = Utente visita pricing
- **Custom Event**: `Signup` = Utente si registra
- **Custom Event**: `Upgrade` = Utente fa upgrade a Premium

#### 4.2 Email Reports
Plausible → Settings → Email Reports
- Weekly summary ogni lunedì
- Monthly summary ogni 1° del mese

#### 4.3 Shared Dashboard (Optional)
Plausible → Settings → Visibility → Public
- Rendi stats pubbliche: `https://plausible.io/basecard.dk`
- Utile per trasparenza/marketing

---

## 🔧 Configurazioni Avanzate

### Custom Events (per trackare conversioni)

#### Nel Blade Template
```html
<!-- Button Signup -->
<button onclick="plausible('Signup', {props: {plan: 'trial'}})">
    Start Trial
</button>

<!-- Button Upgrade -->
<button onclick="plausible('Upgrade', {props: {plan: 'premium', revenue: 19}})">
    Upgrade to Premium
</button>
```

#### Nel JavaScript
```javascript
// Track custom event
window.plausible('Conversion', {
    props: {
        type: 'subscription',
        plan: 'advanced',
        value: 29
    }
});
```

#### Nel Controller Laravel (via JS inject)
```php
// DeckController.php - dopo creazione deck
return view('deck.created')->with([
    'trackEvent' => [
        'name' => 'Deck Created',
        'props' => ['game' => $deck->game->name]
    ]
]);
```

```blade
{{-- deck/created.blade.php --}}
@if(isset($trackEvent))
<script>
    plausible('{{ $trackEvent["name"] }}', {props: @json($trackEvent['props'])});
</script>
@endif
```

### Exclude Pages da Tracking

Nel file `cookie-consent.blade.php`, modifica lo script load:

```javascript
const script = document.createElement('script');
script.defer = true;
script.setAttribute('data-domain', '{{ config("services.analytics.id") }}');
script.setAttribute('data-exclude', '/admin/*,/superadmin/*'); // Escludi admin pages
script.src = 'https://plausible.io/js/script.js';
```

---

## 🐛 Troubleshooting

### Problema 1: Script non si carica

**Sintomi:**
- Network tab: nessuna richiesta a `plausible.io`
- Console: no errori

**Soluzione:**
```bash
# 1. Verifica .env
cat .env | grep ANALYTICS

# 2. Clear cache
php artisan config:clear
php artisan config:cache

# 3. Verifica config in tinker
php artisan tinker
>>> config('services.analytics.enabled')
```

### Problema 2: Script bloccato da adblocker

**Sintomi:**
- Console: `Failed to load resource: net::ERR_BLOCKED_BY_CLIENT`

**Soluzione:**
- Disabilita adblocker per test
- Oppure usa Plausible proxy (avanzato): https://plausible.io/docs/proxy/introduction

### Problema 3: Nessun dato su dashboard

**Sintomi:**
- DevTools mostra richieste ok
- Dashboard Plausible vuota

**Checklist:**
1. ✅ Stai visitando il dominio di produzione (non localhost)
2. ✅ ANALYTICS_ID corrisponde al dominio registrato
3. ✅ Aspetta 30 secondi e ricarica dashboard
4. ✅ Verifica timezone corretto

### Problema 4: Banner non appare

**Sintomi:**
- Sito carica, ma no banner

**Soluzione:**
```javascript
// Console browser
localStorage.removeItem('cookieConsent')
location.reload()
```

---

## 💰 Billing e Costi

### Durante Trial (30 giorni)
- ✅ Tutto gratuito
- ✅ No credit card richiesta
- ✅ Tutte le feature disponibili

### Dopo Trial
- Piano automaticamente downgrade a "Free trial ended"
- Per continuare, aggiungi carta e scegli piano

### Piani Disponibili (Feb 2026)
| Pageviews/mese | Costo/mese | Raccomandato per |
|----------------|------------|------------------|
| 10,000 | €9 | MVP, early stage |
| 100,000 | €19 | Growth stage |
| 200,000 | €29 | Scale-up |
| Custom | Custom | Enterprise |

**Calcolo pageviews stimati per Basecard:**
- 1000 utenti attivi/mese
- 10 pageviews/utente/sessione
- = ~10K pageviews/mese → Piano €9/mese

---

## 📈 Best Practices

### 1. Setup Goals da Subito
Traccia conversioni chiave:
- Signup
- Upgrade to Premium
- Deck Created
- Collection Added

### 2. Weekly Reviews
Ogni lunedì, controlla:
- Referrer trends (da dove arrivano utenti?)
- Top landing pages (cosa funziona?)
- Exit pages (dove perdi utenti?)

### 3. A/B Testing Manuale
Usa UTM parameters:
```
https://basecard.dk/pricing?utm_source=email&utm_campaign=trial_reminder
```

Plausible mostrerà source/campaign breakdown.

---

## 🎓 Risorse Utili

- **Docs ufficiali**: https://plausible.io/docs
- **API Docs**: https://plausible.io/docs/stats-api
- **Community**: https://github.com/plausible/analytics/discussions
- **Status**: https://status.plausible.io/

---

## ✅ Checklist Finale

Prima di considerare setup completo:

- [ ] Account Plausible creato
- [ ] Dominio `basecard.dk` aggiunto
- [ ] `.env` configurato con `ANALYTICS_ENABLED=true`
- [ ] Config cache cleared
- [ ] Banner cookie testato (appare e salva preferenze)
- [ ] DevTools mostra richieste a `plausible.io/api/event`
- [ ] Dashboard Plausible mostra visitatori real-time
- [ ] Email report setuppati (weekly)
- [ ] Goals configurati (Signup, Upgrade)

---

**🎉 Congratulazioni! Plausible è attivo!**

Ora hai analytics privacy-first, GDPR compliant, e zero impatto su performance.

**Domande?** Check docs o contatta support@plausible.io (rispondono in <24h)
