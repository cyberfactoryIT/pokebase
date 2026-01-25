# TODO - Visible Lookup Key

## Problema Attuale

Il formato della `visible_lookup_key` non è corretto per entrambe le tabelle.

### Formato Richiesto
**`ABBREVIAZIONE NNN/TTT`**

Dove:
- `ABBREVIAZIONE` = abbreviazione del set in MAIUSCOLO
- Spazio
- `NNN` = numero carta con padding a 3 cifre (001, 002, 003, etc.)
- `/`
- `TTT` = numero totale carte nel set (con padding a 3 cifre)

**Esempi corretti:**
- `SV04 053/262`
- `151 001/151`
- `BASE 001/102`

---

## Stato Attuale

### TCGCSV Products
❌ **Problema:** 
- Genera `" 001"` invece di `" 001/012"`
- Genera `" 001/012"` (corretto ma solo quando il card_number contiene già il formato completo)
- Manca il totale carte quando `card_number` è solo "001"

**Esempi attuali (SBAGLIATI):**
```
" 001"           ❌ dovrebbe essere " 001/???"
" 001/012"       ✅ OK
"151 001"        ❌ dovrebbe essere "151 001/151"
```

### RapidAPI Cards
❌ **Problema:**
- Genera `"151 001"` invece di `"151 001/151"`
- Manca sempre il totale carte nel set (parte `/TTT`)

**Esempi attuali (SBAGLIATI):**
```
"151 001"        ❌ dovrebbe essere "151 001/151"
"151 002"        ❌ dovrebbe essere "151 002/151"
```

---

## Soluzioni da Implementare

### 1. Per TCGCSV Products
- **Source dati:** `card_number` dal campo della tabella
- **Set code:** `abbreviation` dal gruppo collegato
- **Total cards:** NON disponibile direttamente ❌

**Opzioni:**
1. Calcolare il massimo card_number per gruppo (query)
2. Aggiungere campo `total_cards` a `tcgcsv_groups`
3. Estrarre da `raw_data` se disponibile

### 2. Per RapidAPI Cards
- **Source dati:** `card_number` dal campo (es: "1", "2", "53")
- **Set code:** `episode_slug` (es: "151", "sv04")
- **Total cards:** Disponibile in `episode['printedTotal']` o `episode['total']` dal JSON ✅

**Soluzione:**
- Estrarre `printedTotal` o `total` dal campo JSON `episode`
- Modificare la logica in `RapidapiCard::refreshVisibleLookupKey()` e `backfillVisibleLookupKeys()`

---

## Task List

- [ ] **RapidAPI Cards** - Estrarre total cards da `episode` JSON
- [ ] **TCGCSV Products** - Determinare come ottenere total cards per set
- [ ] Aggiornare `VisibleCardKey::make()` per richiedere parametro `$totalCards`
- [ ] Modificare `TcgcsvProduct::refreshVisibleLookupKey()` 
- [ ] Modificare `TcgcsvProduct::backfillVisibleLookupKeys()`
- [ ] Modificare `RapidapiCard::refreshVisibleLookupKey()`
- [ ] Modificare `RapidapiCard::backfillVisibleLookupKeys()`
- [ ] Reset e re-run backfill per entrambe le tabelle
- [ ] Verificare formato finale

---

## Note

- Abbreviazioni vuote (`""`) vengono convertite in spazio (`" "`) - OK
- Prodotti sealed senza `card_number` non hanno lookup key - OK (intenzionale)
- Attualmente: 26.493 tcgcsv_products + 13.417 rapidapi_cards con lookup key
