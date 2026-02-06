# 📊 Analytics & Cookie Consent Setup

## Overview
Basecard include un sistema di gestione consenso cookie (GDPR compliant) e supporto per analytics.

---

## 🍪 Cookie Consent Banner

### Funzionalità
- ✅ Banner GDPR compliant con 3 categorie di cookie
- ✅ Personalizzazione preferenze utente
- ✅ Salvataggio localStorage
- ✅ Multilingua (da/en/it)
- ✅ Design matching UI con Alpine.js

### Categorie Cookie
1. **Necessari** (sempre attivi): Session, CSRF, Auth
2. **Statistiche** (opzionali): Google Analytics, Plausible
3. **Marketing** (opzionali): Facebook Pixel, LinkedIn Insight, etc.

### Implementazione
Il componente è già incluso in:
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`

### Personalizzazione
Modifica le traduzioni in:
- `lang/da/cookies.php`
- `lang/en/cookies.php`
- `lang/it/cookies.php`

---

## 📈 Analytics Configuration

### Supported Providers

#### 1. Google Analytics (GA4)
```env
ANALYTICS_TYPE=google
ANALYTICS_ID=G-XXXXXXXXXX
ANALYTICS_ENABLED=true
```

**Setup:**
1. Crea property GA4 su [Google Analytics](https://analytics.google.com/)
2. Ottieni Measurement ID (formato: `G-XXXXXXXXXX`)
3. Aggiungi al `.env`
4. Deploy

**Features:**
- IP anonymization abilitata (GDPR)
- Cookie SameSite=None;Secure
- Caricamento condizionale (solo con consenso)

#### 2. Plausible Analytics (Recommended)
```env
ANALYTICS_TYPE=plausible
ANALYTICS_ID=basecard.dk
ANALYTICS_ENABLED=true
```

**Setup:**
1. Registrati su [Plausible.io](https://plausible.io/)
2. Aggiungi dominio (es: `basecard.dk`)
3. Aggiungi al `.env`
4. Deploy

**Vantaggi:**
- ✅ GDPR compliant by default (no cookies)
- ✅ Leggero (< 1KB script)
- ✅ Open source
- ✅ Privacy-first
- ✅ No cookie banner necessario (ma lo usiamo comunque)

#### 3. No Analytics
```env
ANALYTICS_TYPE=none
ANALYTICS_ENABLED=false
```

---

## 🚀 Production Setup

### Step 1: Configure .env
```bash
# Plausible (recommended)
ANALYTICS_TYPE=plausible
ANALYTICS_ID=basecard.dk
ANALYTICS_ENABLED=true

# OR Google Analytics
# ANALYTICS_TYPE=google
# ANALYTICS_ID=G-XXXXXXXXXX
# ANALYTICS_ENABLED=true
```

### Step 2: Clear Config Cache
```bash
php artisan config:clear
php artisan config:cache
```

### Step 3: Test
1. Visita homepage
2. Cookie banner appare in basso
3. Clicca "Acceptér alle"
4. Apri DevTools > Network
5. Verifica richieste a:
   - Plausible: `plausible.io/api/event`
   - Google: `google-analytics.com/g/collect`

---

## 🧪 Testing Cookie Consent

### Test 1: First Visit
```javascript
// Console Browser
localStorage.clear();
location.reload();
// Dovrebbe apparire il banner
```

### Test 2: Accept All
```javascript
// Dopo Accept All
JSON.parse(localStorage.getItem('cookieConsent'));
// Output: {necessary: true, analytics: true, marketing: true}
```

### Test 3: Reject All
```javascript
// Dopo Reject All
JSON.parse(localStorage.getItem('cookieConsent'));
// Output: {necessary: true, analytics: false, marketing: false}
```

### Test 4: Custom Preferences
1. Clicca "Tilpas"
2. Disabilita "Marketing"
3. Abilita "Statistik"
4. Clicca "Gem indstillinger"
5. Verifica localStorage

---

## 📊 Analytics Events (Future)

### Custom Events with Plausible
```javascript
// In blade template or JS
<button onclick="plausible('Signup', {props: {plan: 'premium'}})">
    Sign Up
</button>
```

### Custom Events with Google Analytics
```javascript
// In blade template or JS
gtag('event', 'signup', {
    'event_category': 'conversion',
    'event_label': 'premium_plan'
});
```

---

## 🔒 GDPR Compliance Checklist

- [x] Cookie banner con opt-in
- [x] 3 categorie (Necessary, Analytics, Marketing)
- [x] Link a Privacy Policy
- [x] Salvataggio preferenze utente
- [x] IP anonymization (GA) o cookieless (Plausible)
- [x] Script caricati solo con consenso
- [ ] Privacy Policy aggiornata con dettagli cookie
- [ ] Cookie Policy page (optional)

---

## 🎨 Customization

### Change Banner Colors
Edit `resources/views/components/cookie-consent.blade.php`:
```html
<!-- Current: Purple/Indigo gradient -->
<div class="bg-gradient-to-r from-purple-900/95 to-indigo-900/95">

<!-- Alternative: Blue gradient -->
<div class="bg-gradient-to-r from-blue-900/95 to-cyan-900/95">
```

### Add Marketing Scripts
Edit `loadMarketing()` in cookie-consent component:
```javascript
loadMarketing() {
    // Facebook Pixel
    !function(f,b,e,v,n,t,s){...}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', 'YOUR_PIXEL_ID');
    
    // LinkedIn Insight Tag
    _linkedin_partner_id = "YOUR_PARTNER_ID";
    ...
}
```

---

## 📝 Privacy Policy Update

Aggiungi alla Privacy Policy (`resources/views/legal/privacy.blade.php`):

```html
<h2>Cookie Policy</h2>
<p>Utilizziamo i seguenti cookie:</p>

<h3>Cookie Necessari (sempre attivi)</h3>
<ul>
    <li><code>XSRF-TOKEN</code> - Protezione CSRF</li>
    <li><code>basecard_dk_db_session</code> - Sessione utente</li>
</ul>

<h3>Cookie Statistici (opzionali)</h3>
<ul>
    <li><strong>Plausible Analytics</strong> - Non utilizza cookie (cookieless)</li>
    <!-- OR -->
    <li><strong>Google Analytics</strong> - <code>_ga</code>, <code>_gid</code> (IP anonimizzato)</li>
</ul>

<h3>Cookie Marketing (opzionali)</h3>
<ul>
    <li>Nessuno al momento</li>
</ul>
```

---

## 🐛 Troubleshooting

### Banner non appare
1. Check localStorage: `localStorage.getItem('cookieConsent')`
2. Se esiste, cancellalo: `localStorage.removeItem('cookieConsent')`
3. Reload page

### Analytics non traccia
1. Verifica `.env`: `ANALYTICS_ENABLED=true`
2. Clear config: `php artisan config:cache`
3. Apri DevTools > Console > Cerca errori
4. Verifica Network tab per richieste analytics

### Script non si caricano
1. Verifica consenso: Accept All cookies
2. Check console per errori JavaScript
3. Verifica adblocker disabilitato (blocca analytics)

---

## 📚 References

- [GDPR Cookie Consent Guidelines](https://gdpr.eu/cookies/)
- [Plausible Analytics Docs](https://plausible.io/docs)
- [Google Analytics 4 Setup](https://support.google.com/analytics/answer/9304153)
- [Alpine.js Docs](https://alpinejs.dev/)

---

**Note:** Il banner usa Alpine.js (già incluso nel progetto) e localStorage per salvare le preferenze.
