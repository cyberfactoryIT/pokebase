# 📊 Basecard - Project Status

*Last Updated: 31 January 2026 - 15:00 CET*

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
- **Backend**: Laravel 11 (PHP 8.4+)
- **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
- **Database**: MySQL 8.0+
- **Email**: Brevo SMTP
- **Payments**: Stripe (Subscriptions + One-time purchases)
- **APIs**: 
  - TCGCSV (pricing data - current production)
  - TCGDEX (alternative catalog data - experimental)
  - Cardmarket (EU pricing)
  - RapidAPI Pokemon (episodes & cards mapping)

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

### 3. Catalog System (Updated - Jan 31, 2026)
- ✅ **Per-Game Backend Configuration**: Ogni gioco ha il proprio backend configurato nel database
- ✅ **Database Column**: `games.catalog_backend` ('tcgcsv' | 'tcgdex')
- ✅ **No More ENV Config**: Rimosso `CATALOG_BACKEND` da .env - ora è per gioco
- ✅ **Auto-Detection**: Helper `catalog_backend()` legge dal game corrente
- ✅ **Pokemon = TCGDEX**: Pokemon usa TCGDEX, altri giochi usano TCGCSV
- ✅ **Helper Functions**: `is_tcgdex_catalog()`, `is_tcgcsv_catalog()`, `catalog_backend()`
- ✅ **Unified Routes**: `/pokemon/*` nasconde il backend sottostante
- ✅ **Backend-Agnostic Views**: Blade templates che si adattano al backend attivo
- ✅ **TCGDEX Integration**: Lettura da tabelle staging `tcgdx_sets` e `tcgdx_cards`
- ✅ **TCGDEX Asset URLs**: Logo e immagini con estensione `.webp` corretta
- ✅ **User Interactions**: Like/Wishlist/Watch supportano sia TCGCSV che TCGDEX cards
- ✅ **Dual Backend Tables**: `user_likes`, `user_wishlist_items`, `user_watch_items` con `product_id` + `tcgdex_card_id`

#### Catalog Routes
- `/pokemon/sets` - Lista set Pokemon (con like/wishlist/watch buttons)
- `/pokemon/sets/{setId}` - Dettaglio set con carte e interaction buttons
- `/pokemon/cards/{cardId}` - Dettaglio singola carta con full interactions
- AJAX endpoints per ricerca e paginazione

#### Catalog User Interactions (Updated - Jan 31, 2026)
- ✅ **Like System**: Toggle like su carte (visibile per utenti autenticati)
- ✅ **Wishlist**: Aggiungi carte alla wishlist personale
- ✅ **Watch List**: Monitora carte specifiche per notifiche prezzi
- ✅ **Interaction Routes**: POST `/pokemon/cards/{cardId}/like|wishlist|watch`
- ✅ **Real-time UI Updates**: AJAX calls con aggiornamento immediato stato buttons
- ✅ **Visual Feedback**: Colori dinamici (rosso=liked, viola=wishlist, giallo=watching)
- ✅ **Dual Backend Support**: Database tables supportano sia TCGCSV che TCGDEX cards
- ✅ **State Loading**: Caricamento ottimizzato degli stati per liste di carte

#### Collection & Deck Management with TCGDEX (Updated - Jan 31, 2026)
- ✅ **Dual-Backend Collection**: `user_collection` supporta sia `product_id` (TCGCSV) che `tcgdex_card_id` (TCGDEX)
- ✅ **Dual-Backend Decks**: `deck_cards` supporta entrambi i backend
- ✅ **TCGDEX Collection Routes**: POST `/collection/add/tcgdex` con `tcgdex_card_id`
- ✅ **TCGDEX Deck Routes**: POST `/decks/{deck}/cards/tcgdex` con `tcgdex_card_id`
- ✅ **Controller Methods**: `addTcgdex()` in CollectionController e `addCardTcgdex()` in DeckController
- ✅ **Model Relationships**: UserCollection e DeckCard con `tcgdexCard()` relation (namespace: `App\Models\Tcgdx\TcgdxCard`)
- ✅ **Frontend Integration**: Bottoni funzionanti in `card-tcgdex.blade.php` con modal deck selection
- ✅ **Card Limits**: Rispetta i limiti per tier subscription anche per carte TCGDEX
- ✅ **Database Migration**: Colonne `tcgdex_card_id` nullable con foreign keys e indexes
- ✅ **Backend Filtering**: `/collection`, `/decks`, `/likes`, `/wishlist`, `/osservazione` mostrano solo carte coerenti con `catalog_backend` del gioco corrente
- ✅ **Query Optimization**: WHERE product_id IS NOT NULL (TCGCSV) o WHERE tcgdex_card_id IS NOT NULL (TCGDEX)
- ✅ **Collection Display**: Vista `/collection` supporta entrambi i backend con immagini e link corretti
- ✅ **Statistics Support**: Tutte le statistiche (rarità, condition, sets) funzionano con TCGDEX
- ✅ **Rarity Filtering**: Click su rarità filtra collezione per quella specifica rarità
- ✅ **Remove Cards**: Eliminazione carte dalla collezione funzionante per entrambi i backend
- ✅ **Search API**: `/api/search/cards` supporta parametro `backend` (tcgcsv|tcgdex)
- ✅ **API Auto-Detection**: Helper `catalog_backend()` determina backend automaticamente
- ✅ **TCGDEX JSON Fields**: Corretto parsing di `name` e `set.name` (JSON con localizzazioni)
- ✅ **Image URLs**: TCGDEX immagini con suffisso `/low.webp` per thumbnails

#### Catalog Features (Updated Jan 31, 2026)
- ✅ **Cardmarket Buy Links**: Direct purchase links from card detail pages using RapidAPI mapping
- ✅ **Full i18n for Catalog**: All TCGdex views fully localized (EN/DA)
- ✅ **Translation System**: 70+ translation keys for catalog interface (`resources/lang/*/catalog.php`)
- ✅ **No Hardcoded Text**: All UI strings use `__('catalog.key')` helper
- ✅ **Artist Display**: Illustrator info shown on card badges (from TCGdex data)
- ✅ **Price Gating**: Prezzi visibili solo per Advanced/Premium users nelle liste
- ✅ **Grid Interaction Buttons**: Like/Wishlist/Watch buttons su hover nelle card grid

### 3. Deck Building System
- ✅ Creazione deck con search dinamica carte
- ✅ Valutazione automatica prezzo deck
- ✅ Statistiche aggregate (tipo, rarità, energia)
- ✅ Guest Deck Valuation (lead generation senza account)
- ✅ Export/Import deck

### 4. Pricing System
- ✅ Integrazione TCGCSV per prezzi USA (market, low, mid, high)
- ✅ Integrazione Cardmarket per prezzi EU (avg, low, trend, holo variants)
- ✅ **Cardmarket Direct Buy Links** (Jan 30, 2026): Link diretto per acquisto su Cardmarket
- ✅ **RapidAPI Card Mapping**: TCGdex → RapidAPI → Cardmarket URL resolution
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
- ✅ **Catalog Views Localization** (Jan 30, 2026): TCGdex catalog completamente localizzato
- ✅ **Translation Files**: `resources/lang/{en,da}/catalog.php` con 70+ chiavi
- ✅ Email templates tradotte
- ✅ Validation messages tradotti
- ✅ File di traduzioni: messages.php, auth.php, passwords.php, validation.php, legal.php, **catalog.php**

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
- `user_collection` - Collezione utente (carte possedute) - **Dual Backend: `product_id` + `tcgdex_card_id`**
- `decks` - Mazzi creati (con `game_id`)
- `deck_cards` - Carte nei mazzi - **Dual Backend: `product_id` + `tcgdex_card_id`**
- `guest_decks` - Deck valuation per guest (lead gen)
- `user_likes` - Like su carte - **Dual Backend: `product_id` + `tcgdex_card_id`**
- `user_wishlist_items` - Wishlist - **Dual Backend: `product_id` + `tcgdex_card_id`**
- `user_watch_items` - Watch list - **Dual Backend: `product_id` + `tcgdex_card_id`**

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

**NOTE**: `CATALOG_BACKEND` rimosso da .env - ora configurato per-game nel database (`games.catalog_backend`)

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
