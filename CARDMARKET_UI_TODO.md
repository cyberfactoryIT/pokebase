# TODO: Cardmarket Variants UI Implementation

**Obiettivo**: Mostrare varianti Cardmarket e prezzi europei nelle schede del catalogo TCGCSV

**Tempo stimato**: 2-3 ore  
**Priorità**: Alta  
**Data**: 28 Dicembre 2025

---

## 📋 Tasks

### 1. ✏️ Estendere TcgcsvProduct Model
**File**: `app/Models/TcgcsvProduct.php`

Aggiungere helper methods:
- `hasCardmarketVariants()`: bool - verifica se ha mapping Cardmarket
- `getCardmarketVariantsByType()`: Collection - raggruppa varianti per tipo (Normal, Reverse, 1st Ed)
- `getCardmarketPriceRange()`: array - calcola min/max prezzi tra tutte le varianti
- `getDefaultCardmarketVariant()`: CardmarketProduct|null - variante principale da mostrare

**Note**: Usare relazioni esistenti `cardmarketMapping` e `cardmarketVariants`

---

### 2. 🎨 Creare Componente Blade per Lista Varianti
**File**: `resources/views/components/cardmarket-variants.blade.php`

Componente riutilizzabile che accetta:
- `$product` (TcgcsvProduct)
- `$showPrices` (bool, default true)
- `$compact` (bool, default false)

Deve mostrare:
- Lista varianti con badge tipo
- Prezzi per ogni variante (Avg, Low, Trend)
- Link a Cardmarket per ogni variante
- Icona bandiera UE per prezzi europei

---

### 3. 📊 Aggiungere Sezione Prezzi Cardmarket nella Card Detail View
**File**: `resources/views/tcg/cards/show.blade.php`

Aggiungere dopo la sezione prezzi TCGCSV esistente:
- Titolo "Prezzi Cardmarket (Europa)" con icona 🇪🇺
- Check `@if($card->hasCardmarketVariants())`
- Include componente `<x-cardmarket-variants :product="$card" />`
- Messaggio fallback se no mapping

---

### 4. 🔄 Implementare UI Toggle TCGCSV vs Cardmarket
**File**: `resources/views/tcg/cards/show.blade.php`

Aggiungere tabs/toggle:
- Tab "US Prices" (TCGCSV - dollaro $)
- Tab "EU Prices" (Cardmarket - euro €)
- JavaScript per switch tra le due sezioni
- Salvare preferenza in localStorage

**Librerie**: Usare Alpine.js se disponibile, altrimenti Vanilla JS

---

### 5. 🏷️ Badge per Tipo Variante
**File**: `resources/views/components/variant-badge.blade.php`

Creare component per badge colorati:
- **Normal**: badge blu
- **Reverse Holo**: badge viola con icona ✨
- **1st Edition**: badge oro con "1st"
- **Unlimited**: badge grigio
- **Promo**: badge rosso

Estrae tipo dal campo `name` o `category_name` di CardmarketProduct

---

### 6. 🛡️ Gestire Fallback Graceful
**Files**: Views varie

Gestire casi edge:
- Carta senza mapping Cardmarket → mostrare "No EU prices available"
- Variante senza prezzi → mostrare "Price not available"
- Errore caricamento → mostrare messaggio elegante
- Non bloccare mai il rendering della pagina

**Pattern**: `@if`, `@empty`, `try-catch` dove necessario

---

### 7. 💰 Formattazione Prezzi e Comparazione
**Helper**: `app/helpers.php` o nuovo helper

Aggiungere functions:
- `formatCardmarketPrice($amount)`: string - formatta con € e 2 decimali
- `comparePrice($tcgcsvPrice, $cardmarketPrice)`: array - calcola differenza %
- `convertUsdToEur($amount)`: float - conversione approssimativa per comparazione

**UI**: Mostrare badge "Cheaper in EU" o "Cheaper in US" se differenza > 10%

---

### 8. ✅ Testing Completo
**Test cases**:

1. **Carta con varianti multiple**
   - Test con Pikachu (23 varianti)
   - Verificare tutte le varianti si mostrano
   - Verificare prezzi corretti per ogni variante

2. **Carta senza mapping**
   - Test con carta random non matchata
   - Verificare fallback message
   - Verificare no errori console

3. **Carta con 1 sola variante**
   - Verificare UI ottimizzata
   - Non mostrare "variants" se solo 1

4. **Responsive design**
   - Mobile: lista verticale
   - Desktop: card grid o tabella

5. **Performance**
   - Test con 50+ carte in lista
   - Verificare N+1 query (eager loading)

---

## 🎨 Mockup UI

### Card Detail - Sezione Cardmarket
```
┌─────────────────────────────────────────────┐
│ 💰 Prezzi Cardmarket (Europa) 🇪🇺          │
├─────────────────────────────────────────────┤
│ Normal                              [BADGE] │
│   • Avg: €2.50  Low: €1.80  Trend: €2.30   │
│                                              │
│ Reverse Holo ✨                     [BADGE] │
│   • Avg: €5.20  Low: €4.50  Trend: €5.00   │
│                                              │
│ 1st Edition                         [BADGE] │
│   • Avg: €12.50  Low: €10.00  Trend: €11.80│
│                                              │
│ [View on Cardmarket →]                      │
└─────────────────────────────────────────────┘
```

### Toggle US/EU
```
┌─────────────────────────────────────┐
│  [US Prices $] | EU Prices € [▼]   │
└─────────────────────────────────────┘
```

---

## 📦 Deliverables

- [ ] Model methods implementati e testati
- [ ] Componente variants Blade creato
- [ ] Card detail view aggiornata
- [ ] Toggle US/EU funzionante
- [ ] Badge varianti con stili
- [ ] Fallback gestiti
- [ ] Helper prezzi implementati
- [ ] Testing su 10+ carte diverse
- [ ] Responsive verificato
- [ ] Commit e push

---

## 🔗 File da Modificare

1. `app/Models/TcgcsvProduct.php` - helper methods
2. `resources/views/components/cardmarket-variants.blade.php` - nuovo
3. `resources/views/components/variant-badge.blade.php` - nuovo
4. `resources/views/tcg/cards/show.blade.php` - sezione prezzi
5. `app/helpers.php` - price formatting
6. `resources/css/app.css` - stili badge e toggle (opzionale)
7. `resources/js/app.js` - toggle logic (opzionale)

---

## 💡 Note Tecniche

- **Relazioni già pronte**: `$card->cardmarketVariants` restituisce Collection di CardmarketProduct
- **Eager loading**: Usare `with(['cardmarketMapping', 'cardmarketVariants.latestPriceQuote'])` nei controller
- **Cache**: Considerare cache per prezzi Cardmarket (opzionale)
- **API rate limit**: Non c'è, usiamo dati locali dal DB

---

## 🚀 Quick Start (Domani)

```bash
# 1. Pull latest code
git pull origin main

# 2. Verificare mapping esistenti
php artisan tinker --execute="
\$count = App\Models\TcgcsvCardmarketMapping::count();
echo 'Mappings disponibili: ' . \$count . PHP_EOL;
"

# 3. Iniziare con Task 1 (Model)
# Aprire: app/Models/TcgcsvProduct.php

# 4. Testare live durante sviluppo
php artisan serve
# Visitare carta con varianti per vedere cambiamenti real-time
```

---

**Ready to implement! 🎯**
