# 📊 Basecard - Project Status

*Last Updated: 1 February 2026 - 10:30 CET*

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
  - **TCGDEX** (primary catalog for Pokemon - full pricing integration)
  - TCGCSV (pricing data for MTG/YGO)
  - Cardmarket (EU pricing fallback)
  - RapidAPI Pokemon (episodes & cards mapping)

### Multi-Game System
- Sistema con scoping automatico per supportare 5 giochi (3 attivi, 2 in beta)
- **3 Backend Catalog**:
  - **TCGDEX**: Pokemon TCG (primary, production-ready)
  - **TCGCSV**: MTG, YGO (legacy, stable)
  - **CMAPI**: Lorcana, One Piece (beta, partial)
- Ogni utente ha un `default_game_id` che filtra automaticamente i dati
- Cambio gioco tramite dropdown nella navbar
- Database condiviso con campo `game_id` su tutte le tabelle rilevanti
- Backend selection via `games.catalog_backend` column

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

### 3. Catalog System (COMPLETE REWRITE - Jan 28-31, 2026)

#### 🎯 TCGDEX Full Integration (Production Ready)
- ✅ **Primary Backend for Pokemon**: TCGDEX è ora il backend principale per Pokemon
- ✅ **Complete Data Import**: Import automatico di sets e cards con prezzi integrati
- ✅ **Database Tables**: `tcgdx_sets` (200+ sets) e `tcgdx_cards` (30k+ cards)
- ✅ **Automatic Price Extraction**: `price_eur` e `price_usd` estratti durante import da Cardmarket/TCGPlayer
- ✅ **Import Command**: `php artisan tcgdx:import` (con `--cards-only`, `--fresh`, `--set=X`)
- ✅ **Pipeline Integration**: Import schedulato alle 04:45 daily dopo RapidAPI sync
- ✅ **ETL Phase System**: Import in 2 fasi (sets → cards) per robustezza

#### Per-Game Backend Configuration
- ✅ **Database-Driven**: Ogni gioco ha `catalog_backend` in tabella `games`
- ✅ **Pokemon = TCGDEX**: Pokemon usa TCGDEX, altri giochi TCGCSV
- ✅ **Helper Functions**: `catalog_backend()`, `is_tcgdex_catalog()`, `is_tcgcsv_catalog()`
- ✅ **No ENV Config**: Rimosso `CATALOG_BACKEND` da .env
- ✅ **Auto-Detection**: Backend rilevato automaticamente dal gioco corrente

#### Unified Routes & Views
- ✅ **Backend-Agnostic Routes**: `/pokemon/*` nasconde implementazione sottostante
- ✅ **Smart Blade Templates**: Views che si adattano al backend (TCGDEX/TCGCSV)
- ✅ **Route Examples**:
  - `/pokemon/sets` - Lista set (TCGDEX: tcgdx_sets, TCGCSV: tcgcsv_groups)
  - `/pokemon/sets/{id}` - Dettaglio set con cards
  - `/pokemon/cards/{id}` - Dettaglio carta con prezzi e interactions

#### TCGDEX Asset Management
- ✅ **Image URLs**: Gestione automatica `.webp` per logo e card images
- ✅ **Low Quality Thumbnails**: `/low.webp` suffix per performance
- ✅ **High Quality Details**: Full resolution per card detail pages
- ✅ **JSON Localization**: Parsing automatico di `name` field (multilingual JSON)

#### Catalog User Interactions (Complete - Jan 31, 2026)
- ✅ **Like System**: Toggle like su carte con UI feedback immediato
- ✅ **Wishlist**: Gestione wishlist personale per tracking carte desiderate
- ✅ **Watch List**: Monitoraggio prezzi con notifiche
- ✅ **Dual Backend Support**: Tutte le tabelle con `product_id` + `tcgdex_card_id`
- ✅ **AJAX Endpoints**: POST `/pokemon/cards/{id}/like|wishlist|watch`
- ✅ **Real-time Updates**: Aggiornamento stato senza page reload
- ✅ **Visual Feedback**: Rosso=liked, Viola=wishlist, Giallo=watching
- ✅ **State Preloading**: Caricamento efficiente per grid di carte

#### Collection & Deck Management (Dual Backend - Jan 31, 2026)
- ✅ **Dual-Backend Collection**: `user_collection` supporta TCGCSV + TCGDEX
- ✅ **Dual-Backend Decks**: `deck_cards` supporta entrambi i backend
- ✅ **TCGDEX Routes**: 
  - POST `/collection/add/tcgdex` - Aggiungi carta TCGDEX a collezione
  - POST `/decks/{deck}/cards/tcgdex` - Aggiungi carta TCGDEX a deck
- ✅ **Model Relationships**: `tcgdexCard()` in UserCollection e DeckCard
- ✅ **Namespace Corretto**: `App\Models\Tcgdx\TcgdxCard` per relazioni
- ✅ **Frontend Integration**: Modal deck selection, bottoni add collection funzionanti
- ✅ **Card Limits Enforcement**: Rispetta tier subscription anche per TCGDEX
- ✅ **Database Migrations**: Colonne nullable con foreign keys e indexes
- ✅ **Backend Filtering**: Query automatiche filtrano per backend del gioco corrente
- ✅ **Display Support**: `/collection`, `/decks` mostrano correttamente carte TCGDEX
- ✅ **Statistics**: Tutte le stats (rarità, condition, sets) funzionano con TCGDEX
- ✅ **Rarity Filtering**: Click su rarità filtra collezione
- ✅ **Remove Cards**: Eliminazione carte funzionante per entrambi i backend
- ✅ **Search API**: `/api/search/cards?backend=tcgdex` supporta ricerca cross-backend

#### Dashboard Integration (Complete - Jan 31, 2026)
- ✅ **Backend-Aware Dashboard**: Dashboard rileva automaticamente catalog backend
- ✅ **TCGDEX Featured Expansions**: Carousel auto-scroll con ultimi 6 set
- ✅ **Expansions Badge**: Link corretto (pokemon.sets per TCGDEX, tcg.expansions.index per TCGCSV)
- ✅ **Missing Cards Feature**: 
  - API endpoint `/api/missing-cards/tcgdex` con aggregazione prezzi
  - Calcolo valore totale carte mancanti
  - Percentuale completamento set
  - Horizontal scroll UI con performance ottimizzata
- ✅ **Navigation**: Sets link in navbar con routing backend-aware
- ✅ **Footer**: Game selector spostato nel footer per UX migliore
- ✅ **Translations**: Chiavi `missing_cards` e `no_missing_cards` (EN/IT/DA)
- ✅ **Global Search**: Ricerca carte routata correttamente per TCGDEX/TCGCSV
- ✅ **Performance**: Immagini `/low.webp`, carousel scrollbar nascosta

### 3. Deck Building System
- ✅ Creazione deck con search dinamica carte
- ✅ Valutazione automatica prezzo deck
- ✅ Statistiche aggregate (tipo, rarità, energia)
- ✅ Guest Deck Valuation (lead generation senza account)
- ✅ Export/Import deck

### 4. Pricing System (Multi-Source Integration)
- ✅ **TCGDEX Primary**: Prezzi EUR/USD estratti automaticamente da TCGDEX API
- ✅ **Price Fields**: `tcgdx_cards.price_eur` e `price_usd` popolati durante import
- ✅ **Cardmarket Integration**: Trend price da `raw.pricing.cardmarket`
- ✅ **TCGPlayer Integration**: Market/mid/low prices da `raw.pricing.tcgplayer`
- ✅ **Priority Logic**:
  1. `cardmarket_price_quotes.trend` (tabella dedicata)
  2. `tcgdx_cards.price_eur` (estratto da JSON)
  3. RapidAPI Cardmarket data
  4. Conversione USD → EUR come fallback
- ✅ **TCGCSV Fallback**: Per MTG/YGO usa ancora TCGCSV pricing
- ✅ **Cardmarket Direct Links**: Link acquisto diretto usando RapidAPI mapping
- ✅ **Toggle US ↔ EU**: Con persistenza localStorage
- ✅ **Historical Pricing**: 7-day, 30-day averages
- ✅ **Price Gating**: Basato su subscription tier

### 5. Subscription & Monetization
- ✅ 3 tier pricing: Basic (free), Advanced, Premium
- ✅ Stripe integration per pagamenti ricorrenti
- ✅ Deck Evaluation one-time purchase (€9.99, valido 365 giorni)
- ✅ Promotional codes support
- ✅ Invoice generation and history
- ✅ Price visibility gating based on subscription tier

### 6. Multi-Language Support
- ✅ Supporto completo EN, IT, DA (Danese)
- ✅ **Catalog Complete i18n** (Jan 30-31, 2026): Catalog views 100% localizzati
- ✅ **Translation Files**: 
  - `resources/lang/{en,da,it}/catalog.php` (70+ chiavi)
  - `resources/lang/{en,da,it}/dashboard.php` (missing_cards, etc.)
  - Standard files: messages.php, auth.php, validation.php, legal.php
- ✅ **No Hardcoded Text**: Tutti i template usano `__('catalog.key')`
- ✅ Email templates tradotte
- ✅ Validation messages tradotti

### 7. Admin Features
- ✅ Superadmin dashboard
- ✅ User management
- ✅ Organization management (disabilitato tramite ORGANIZATIONS_ENABLED=false)
- ✅ Activity logs
- ✅ Pricing plans management
- ✅ Promotions management

### 8. Multi-Game Support
- ✅ Pokemon TCG (TCGDEX backend)
- ✅ Magic: The Gathering (MTG) (TCGCSV backend)
- ✅ Yu-Gi-Oh! (YGO) (TCGCSV backend)
- 🚧 Disney Lorcana (CMAPI backend - partial implementation)
- 🚧 One Piece Card Game (CMAPI backend - partial implementation)
- ✅ Automatic scoping per gioco selezionato
- ✅ Game switcher nella navbar
- ✅ Database-driven backend configuration (`games.catalog_backend`)

### 9. UX & Navigation Improvements (Feb 1, 2026)
- ✅ **Keyboard Navigation**: Arrow keys (Up/Down), Enter per selezionare, Escape per chiudere
  - Header global search con auto-scroll del dropdown
  - Dashboard "Quick Add Card" con highlight visuale
- ✅ **Card Number Format**: Display "#10/102" invece di "10" in tutte le ricerche
  - CardSearchController separa `card_number` e `set_total`
  - TCGCSV usa `SUBSTRING_INDEX` per estrarre numero/totale
  - TCGDEX usa nativamente `local_id` e `card_count_official`
- ✅ **Deck View Refactoring**: Backend-specific partial views
  - `resources/views/decks/partials/card-grid-tcgcsv.blade.php` (207 righe - logica prezzi completa)
  - `resources/views/decks/partials/card-grid-tcgdex.blade.php` (119 righe - parsing JSON Pokemon)
  - `resources/views/decks/partials/card-grid-cmapi.blade.php` (111 righe - Lorcana/One Piece)
  - Ridotto `show.blade.php` da 1134 a 858 righe (-276 righe di if/else eliminati)
- ✅ **Deck Card Addition Fix**: JavaScript routing corretto per TCGDEX
  - Fixed parameter order: (productId, tcgdexId, name)
  - Fixed URL construction: `/decks/{id}/cards/tcgdex`
  - Fixed CollectionController: return both `product_ids` e `tcgdex_card_ids`

---

## 🚧 Known Issues & Limitations

### Data Import
- ✅ **TCGDEX Now Primary**: Pokemon usa TCGDEX (stabile, 30k+ carte, prezzi integrati)
- ⚠️ Pokemon TCG API deprecato (504 timeouts, instabilità)
- ✅ TCGCSV mantenuto per MTG/YGO
- ✅ Immagini TCGDEX alta qualità (.webp format)
- 🚧 **CMAPI (Lorcana/One Piece)**: Backend code completo, import command mancante
  - ❌ Missing: `php artisan cmapi:import` command
  - ❌ Incomplete: Frontend views per browse sets/cards
  - ❌ Incomplete: Dashboard integration per featured sets
  - ✅ Models, Services, Migrations, Routes presenti

### Organizations Feature
- ⚠️ Feature multi-organizzazione disabilitata (`ORGANIZATIONS_ENABLED=false`)
- ⚠️ **NON TOCCARE MAI QUESTO SETTING** (critico)
- ✅ Ogni utente ha organizzazione default auto-creata

### Performance
- ✅ TCGDEX import ottimizzato (2 fasi: sets → cards)
- ⚠️ Import completo può richiedere 5-10 minuti per 200+ sets
- ✅ Idempotent & resumable (può essere interrotto e ripreso)
- ✅ Command `--cards-only` per re-import solo carte

---

## 📊 Database Schema Highlights

### Core Tables
- `users` - Utenti con `default_game_id` e `organization_id`
- `organizations` - Organizzazioni (una per utente)
- `games` - Tabella giochi (pokemon, mtg, yugioh) con **`catalog_backend`** column

### TCGDEX Card Data (Pokemon Primary)
- `tcgdx_sets` - Set Pokemon (200+ sets con logo, release date, card count)
- `tcgdx_cards` - Carte Pokemon (30k+ cards con **price_eur**, **price_usd** integrati)
- `tcgdx_import_runs` - Log import runs per tracking
- Relazioni: `tcgdx_cards.set_tcgdx_id` → `tcgdx_sets.id`

### TCGCSV Card Data (MTG/YGO)
- `tcgcsv_groups` - Set/Espansioni (con `game_id`)
- `tcgcsv_products` - Singole carte (con `game_id`)
- `tcgcsv_prices` - Prezzi USA (market, low, mid, high)

### CMAPI Card Data (Lorcana/One Piece - Beta)
- `cmapi_sets` - Set/Episodes (200+ per Lorcana, con game_id)
- `cmapi_cards` - Carte con pricing EUR/USD integrato (con game_id)
- `cmapi_import_runs` - Log import runs per tracking
- `cmapi_card_price_snapshots` - Historical pricing snapshots
- Relazioni: `cmapi_cards.set_cmapi_id` → `cmapi_sets.id`

### Pricing Data
- `cardmarket_products` - Prodotti Cardmarket
- `cardmarket_prices` - Prezzi EU storici
- `cardmarket_price_quotes` - Quote giornaliere (trend, avg, low)
- `rapidapi_cards` - Mapping RapidAPI per link Cardmarket

### User Content (Dual Backend Support)
- `user_collection` - **`product_id` + `tcgdex_card_id`** (nullable, indexed)
- `deck_cards` - **`product_id` + `tcgdex_card_id`** (nullable, indexed)
- `user_likes` - **`product_id` + `tcgdex_card_id`** (nullable, indexed)
- `user_wishlist_items` - **`product_id` + `tcgdex_card_id`** (nullable, indexed)
- `user_watch_items` - **`product_id` + `tcgdex_card_id`** (nullable, indexed)
- `decks` - Mazzi con `game_id`
- `guest_decks` - Deck evaluation lead gen

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
```env
APP_URL=https://basecard.dk
ORGANIZATIONS_ENABLED=false
DEFAULT_GAME_ID=1 (Pokemon)
STRIPE_KEY=pk_live_...
BREVO_API_KEY=...
CARDMARKET_APP_TOKEN=...
RAPIDAPI_KEY=...
TCGDX_BASE_URL=https://api.tcgdex.net/v2
```

**REMOVED**: `CATALOG_BACKEND` (ora configurato nel database per-game)

---

## 📈 Metrics & Analytics

### Current Database Size
- **~200 Pokemon sets** (TCGDEX)
- **~30,000 Pokemon cards** (TCGDEX con prezzi EUR/USD)
- ~30k Pokemon cards (TCGCSV - legacy)
- MTG/YGO via TCGCSV

### ETL Pipeline Schedule (Europe/Copenhagen)
- **04:00** - RapidAPI Episodes sync
- **04:45** - **TCGDEX Import** (sets + cards + prices) ← NEW
- **05:30** - RapidAPI Episodes mapping
- **05:50** - TCGDEX → TCGCSV mapping
- **06:00** - TCGCSV enrichment
- **06:30** - Cardmarket price sync

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

## 📝 Recent Major Updates

### February 1, 2026: UX Improvements & Deck Refactoring
**Enhanced user experience with keyboard navigation and cleaner architecture**

#### Implemented:
1. **Keyboard Navigation**
   - Dashboard search: ArrowUp/Down navigation con `highlightedIndex`
   - `scrollIntoView({ block: 'nearest' })` per auto-scroll dropdown
   - Enter per selezionare risultato, Escape per chiudere
   - Stesso pattern applicato a header search

2. **Card Display Format**
   - Nuovo formato: "#10/102" invece di solo "10"
   - Backend TCGCSV: SQL `SUBSTRING_INDEX(card_number, '/', 1)` per numero, parte dopo '/' per totale
   - Backend TCGDEX: `local_id` e `card_count_official` già separati
   - API response: `{card_number: '10', set_total: '102'}`
   - Applicato a: collection, deck search, catalog search

3. **Deck View Architecture**
   - Eliminati 276 righe di codice monolitico con if/else ripetuti
   - Creati 3 partial views puliti (TCGCSV: 207 righe, TCGDEX: 119 righe, CMAPI: 111 righe)
   - Ogni partial contiene tutta la logica specifica del backend
   - Pattern scalabile per futuri backend
   - Manutenibilità drasticamente migliorata

4. **Bug Fixes**
   - Fixed deck card addition per TCGDEX (JavaScript parameter passing)
   - Fixed CollectionController API endpoint (ritorna entrambi gli array)
   - Fixed DeckController `getDeckTopStats()` per supportare tutti e 3 i backend
   - Rimosso codice duplicato da merge conflicts

#### Files Modified: 15+
- JavaScript: `resources/js/quickAddCard.js` (keyboard nav)
- Controllers: `CardSearchController.php` (card_number/set_total), `CollectionController.php` (dual array), `DeckController.php` (multi-backend stats)
- Views: `decks/show.blade.php` (ridotto 276 righe), 3 nuovi partial (tcgcsv/tcgdex/cmapi)
- Views: `collection/index.blade.php` (formato numero carta)

---

### January 28-31, 2026: TCGDEX Complete Integration
**Complete catalog system rewrite con TCGDEX come backend primario per Pokemon**

#### Implemented:
1. **Import System**
   - Command `php artisan tcgdx:import` con opzioni `--fresh`, `--cards-only`, `--set=X`
   - 2-phase import (sets → cards) per robustezza
   - Automatic price extraction (EUR/USD) da Cardmarket/TCGPlayer
   - Pipeline integration alle 04:45 daily

2. **Database Architecture**
   - Nuove tabelle: `tcgdx_sets`, `tcgdx_cards`, `tcgdx_import_runs`
   - Dual backend support in tutte le user tables (product_id + tcgdex_card_id)
   - Per-game `catalog_backend` configuration in `games` table
   - Foreign keys e indexes ottimizzati

3. **Frontend Features**
   - Backend-aware routing (`/pokemon/*` auto-detect TCGDEX/TCGCSV)
   - Dashboard missing cards con calcolo valore e percentuale completamento
   - Featured expansions carousel auto-scroll
   - Global search con routing intelligente
   - Like/Wishlist/Watch per entrambi i backend

4. **Pricing Integration**
   - Price fields (`price_eur`, `price_usd`) popolati durante import
   - Priority logic: Cardmarket trend → TCGDEX prices → RapidAPI → USD conversion
   - Direct Cardmarket buy links via RapidAPI mapping

5. **Developer Experience**
   - Helper functions: `catalog_backend()`, `is_tcgdex_catalog()`, etc.
   - Blade components backend-agnostic
   - Model relationships con namespace corretto (`App\Models\Tcgdx\TcgdxCard`)
   - Complete i18n (EN/IT/DA) per catalog views

#### Files Modified: 40+
- Controllers: CatalogController, DashboardController, CollectionController, DeckController
- Services: TcgdxClient, TcgdxImportService
- Models: TcgdxCard, TcgdxSet, UserCollection, DeckCard, User Likes/Wishlist/Watch
- Views: 15+ Blade templates (catalog, dashboard, collection, decks)
- Migrations: 5+ new tables and columns
- Routes: web.php, api.php, console.php
- Translations: catalog.php, dashboard.php (EN/IT/DA)

---

## 📝 Notes

- Logo aggiornato a `logo_basecard.svg` in tutte le pagine
- Registrazione funzionante dopo fix session/routing
- Tutti i testi hardcoded rimossi e tradotti
- Email templates con tema dark matching UI
