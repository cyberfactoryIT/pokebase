# 📊 Basecard - Project Status

*Last Updated: 13 January 2026*

---

## 🎯 Application Overview

**Basecard** è una piattaforma per collezionisti di carte da gioco (Pokemon TCG, Magic: The Gathering, Yu-Gi-Oh!) che permette di:
- Gestire collezioni di carte con prezzi aggiornati
- Creare e valutare mazzi (decks)
- Monitorare l'andamento del valore nel tempo
- Accedere a statistiche e insights sulla propria collezione

---

## 🏗️ Architecture

### Stack Tecnologico
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
- **Database**: MySQL 8.0+
- **Email**: Brevo SMTP
- **Payments**: Stripe (Subscriptions + One-time purchases)
- **APIs**: TCGCSV (pricing data), Cardmarket (EU pricing)

### Multi-Game System
- Sistema con scoping automatico per supportare 3 giochi
- Ogni utente ha un `default_game_id` che filtra automaticamente i dati
- Cambio gioco tramite dropdown nella navbar
- Database condiviso con campo `game_id` su tutte le tabelle rilevanti

---

## ✅ Features Completate

### 1. Authentication & User Management
- ✅ Registrazione con email verification
- ✅ Login con remember me
- ✅ Password reset via email
- ✅ 2FA (Two-Factor Authentication)
- ✅ Auto-assegnazione gioco Pokemon alla registrazione
- ✅ Email template dark-themed matching UI

### 2. Collection Management
- ✅ Aggiunta carte alla collezione
- ✅ Gestione quantità e varianti (foil, 1st edition, etc.)
- ✅ Visualizzazione valore totale collezione
- ✅ Filtri per set, rarità, tipo
- ✅ Gating dei prezzi (solo per Advanced/Premium o con Deck Evaluation attivo)

### 3. Deck Building System
- ✅ Creazione deck con search dinamica carte
- ✅ Valutazione automatica prezzo deck
- ✅ Statistiche aggregate (tipo, rarità, energia)
- ✅ Guest Deck Valuation (lead generation senza account)
- ✅ Export/Import deck

### 4. Pricing System
- ✅ Integrazione TCGCSV per prezzi USA (market, low, mid, high)
- ✅ Integrazione Cardmarket per prezzi EU (avg, low, trend, holo variants)
- ✅ Toggle US ↔ EU con persistenza localStorage
- ✅ Conversione automatica USD → EUR
- ✅ Historical pricing (7-day, 30-day averages)
- ✅ Gating per utenti free vs paid

### 5. Subscription & Monetization
- ✅ 3 tier pricing: Basic (free), Advanced, Premium
- ✅ Stripe integration per pagamenti ricorrenti
- ✅ Deck Evaluation one-time purchase (€9.99, valido 365 giorni)
- ✅ Promotional codes support
- ✅ Invoice generation and history
- ✅ Price visibility gating based on subscription tier

### 6. Multi-Language Support
- ✅ Supporto completo EN, IT, DA (Danese)
- ✅ Tutte le stringhe UI tradotte
- ✅ Email templates tradotte
- ✅ Validation messages tradotti
- ✅ File di traduzioni: messages.php, auth.php, passwords.php, validation.php, legal.php

### 7. Admin Features
- ✅ Superadmin dashboard
- ✅ User management
- ✅ Organization management (disabilitato tramite ORGANIZATIONS_ENABLED=false)
- ✅ Activity logs
- ✅ Pricing plans management
- ✅ Promotions management

### 8. Multi-Game Support
- ✅ Pokemon TCG
- ✅ Magic: The Gathering (MTG)
- ✅ Yu-Gi-Oh! (YGO)
- ✅ Automatic scoping per gioco selezionato
- ✅ Game switcher nella navbar

---

## 🚧 Known Issues & Limitations

### Data Import
- ⚠️ Pokemon TCG API (`pokemontcg.io`) deprecato per instabilità (504 timeouts)
- ✅ Usato TCGCSV come source principale (più stabile, 30k+ carte)
- ⚠️ Immagini TCGCSV di qualità inferiore rispetto a Pokemon TCG API

### Organizations Feature
- ⚠️ Feature multi-organizzazione disabilitata (`ORGANIZATIONS_ENABLED=false`)
- ⚠️ Non toccare mai questo setting (enfatizzato dall'utente)
- ✅ Ogni utente ha una organizzazione di default auto-creata

### Performance
- ⚠️ Import completo TCGCSV può richiedere 10-30 minuti per gioco
- ⚠️ Cardmarket ETL può richiedere tempo per set grandi

---

## 📊 Database Schema Highlights

### Core Tables
- `users` - Utenti con `default_game_id` e `organization_id`
- `organizations` - Organizzazioni (una per utente in modalità corrente)
- `games` - Tabella giochi (pokemon, mtg, yugioh)

### Card Data
- `tcgcsv_groups` - Set/Espansioni (con `game_id`)
- `tcgcsv_products` - Singole carte (con `game_id`)
- `tcgcsv_prices` - Prezzi USA (market, low, mid, high)
- `cardmarket_products` - Dati Cardmarket
- `cardmarket_prices` - Prezzi EU

### User Content
- `user_collection` - Collezione utente (carte possedute)
- `decks` - Mazzi creati (con `game_id`)
- `deck_cards` - Carte nei mazzi
- `guest_decks` - Deck valuation per guest (lead gen)

### Monetization
- `pricing_plans` - Piani di abbonamento (Basic, Advanced, Premium)
- `subscriptions` - Sottoscrizioni attive
- `deck_evaluation_purchases` - Acquisti one-time deck evaluation
- `invoices` - Fatture generate
- `promotions` - Codici promozionali

### Deprecated
- `deprecated_card_catalog` - Vecchia tabella Pokemon TCG API
- `deprecated_pokemon_sets` - Vecchi set Pokemon TCG API

---

## 🔒 Security & Configuration

### Critical Config
- **ORGANIZATIONS_ENABLED**: DEVE essere `false` (mai modificare)
- **APP_ENV**: `production` su server live
- **APP_DEBUG**: `false` in produzione
- **Brevo SMTP**: Configurato per email transazionali
- **Stripe Keys**: Separate per test/production

### Environment Variables Principali
```
APP_URL=https://basecard.dk
ORGANIZATIONS_ENABLED=false
DEFAULT_GAME_ID=1 (Pokemon)
STRIPE_KEY=pk_live_...
BREVO_API_KEY=...
CARDMARKET_APP_TOKEN=...
RAPIDAPI_KEY=...
```

---

## 📈 Metrics & Analytics

### Current Database Size
- ~30,757 Pokemon cards (TCGCSV)
- ~Xk MTG cards
- ~Xk YGO cards

### User Tiers Distribution
- Basic (free): X users
- Advanced: X users
- Premium: X users

---

## 🎨 UI/UX

### Design System
- **Color Palette**: Dark theme (black #000, card bg #161615)
- **Primary Actions**: Blue gradient buttons
- **Borders**: Semi-transparent white (white/15)
- **Logo**: SVG logo_basecard.svg (usato in navbar e auth pages)

### Responsive
- ✅ Mobile-first design
- ✅ Tailwind responsive classes
- ✅ Alpine.js per interattività

---

## 📝 Notes

- Logo aggiornato a `logo_basecard.svg` in tutte le pagine
- Registrazione funzionante dopo fix session/routing
- Tutti i testi hardcoded rimossi e tradotti
- Email templates con tema dark matching UI
