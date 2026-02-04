# 🗑️ Basecard - Deprecation Log

*Tracciamento di features, codice e sistemi deprecati o da rimuovere*

---

## ⚠️ Attualmente Deprecato

### 1. Pokemon TCG API System
**Data deprecazione**: 27 Dicembre 2025  
**Motivo**: Instabilità API (frequenti 504 timeouts), dati incompleti (solo 263 carte vs 30k+ TCGCSV)

**Tabelle deprecate**:
- `deprecated_card_catalog` (ex `card_catalog`)
- `deprecated_pokemon_sets` (ex `pokemon_sets`)

**Codice deprecato da rimuovere**:
```
app/Console/Commands/ImportAllPokemonCards.php
app/Console/Commands/DownloadPokemonCards.php
app/Console/Commands/ImportCardsFromFiles.php
app/Console/Commands/ImportSetsFromFile.php
app/Console/Commands/ListPokemonSets.php
app/Console/Commands/CheckPokemonUpdates.php
app/Console/Commands/SyncPokemonSets.php
app/Models/CardCatalog.php
app/Models/PokemonSet.php (se non utilizzato altrove)
app/Services/PokemonImportService.php
download_pokemon_card.sh
download_pokemon_card_test.sh
```

**Sostituto**: Sistema TCGCSV (`tcgcsv_groups`, `tcgcsv_products`, `tcgcsv_prices`)

**Azione necessaria**: 
- ✅ Tabelle rinominate con prefisso `deprecated_`
- ⏳ Rimuovere completamente dopo verifica nessun codice dipendente
- ⏳ Eliminare file command e model listati

**Deadline**: Entro fine Q1 2026

---

### 2. TCGDex Import System
**Data deprecazione**: [Da definire]  
**Motivo**: Preferito TCGCSV per migliore coverage e affidabilità

**File deprecato**:
- `TCGDEX_IMPORT_SYSTEM.md`
- Eventuali command relativi a TCGDex

**Stato**: 
- ⏳ Da verificare se sistema è ancora in uso
- ⏳ Se non usato, rimuovere documentazione e codice

---

### 3. Organization Multi-Tenant System
**Status**: Feature disabilitata ma NON deprecata  
**Configurazione**: `ORGANIZATIONS_ENABLED=false`

**Motivo**: Scelta progettuale per semplificare MVP

⚠️ **IMPORTANTE**: Non toccare mai `ORGANIZATIONS_ENABLED` (enfatizzato dall'utente)

**Note**: 
- Sistema rimane nel codice per possibile riattivazione futura
- Ogni utente ha comunque una organization di default auto-creata
- Non rimuovere codice related

---

## 🔄 In Valutazione per Deprecazione

### 4. Vecchi MD Documentation Files
**Data valutazione**: 13 Gennaio 2026  
**Motivo**: Troppi file disorganizzati, molti obsoleti

**File da rimuovere**:
```
CARDMARKET_UI_COMPLETED.md (duplicato info)
ARTICLES_MULTILANGUAGE.md (info specifica feature)
CARDMARKET_ETL_SUMMARY.md (duplicato info)
MULTI_GAME_SYSTEM.md (info specifica feature)
ARCHITECTURE_DIAGRAM.md (da consolidare in STATUS)
SUBSCRIPTION_UI_IMPLEMENTATION.md (info specifica feature)
MULTI_GAME_IMPLEMENTATION.md (duplicato)
CARDMARKET_REDESIGN_SUMMARY.md (duplicato)
DECK_EVALUATION_MONETIZATION.md (info specifica feature)
MAPPING_GUIDE.md (tecnico, può rimanere in docs/)
CARDMARKET_DEPLOYMENT.md (duplicato DEPLOYMENT.md)
TCGCSV_README.md (da consolidare)
DECK_EVALUATION_PATCHES_APPLIED.md (log temporaneo)
COLLECTION_DECK_SYSTEM.md (info in STATUS)
TCGDEX_IMPORT_SYSTEM.md (sistema deprecato)
CURRENCY_CONVERSION_SYSTEM.md (info in STATUS)
ARTICLES_SYSTEM.md (info specifica feature)
PRODUCTION_CHECKLIST.md (da consolidare in OPERATIONS)
PRODUCTION_DEPLOY.md (duplicato DEPLOYMENT)
PROJECT_SNAPSHOT.md (vecchio STATUS)
MULTI_GAME_TESTING.md (log temporaneo)
IMPLEMENTATION_SUMMARY.md (duplicato)
CARDMARKET_INSTALLATION.md (da consolidare)
VERIFICATION_CHECKLIST.md (temporaneo)
TEST_MODE_CLEANUP.md (log temporaneo)
DEFAULT_GAME_FEATURE.md (info in STATUS)
COLLECTION_INSIGHTS_VERIFICATION.md (log temporaneo)
docs/cardmarket-etl.md (duplicato)
docs/cardmarket-etl-json.md (duplicato)
```

**File da mantenere**:
```
README.md (standard Laravel, da aggiornare con info progetto)
PROJECT_STATUS.md (nuovo, status completo applicazione)
OPERATIONS.md (nuovo, comandi operativi)
ROADMAP.md (nuovo, sviluppi futuri)
DEPRECATION.md (questo file)
```

**Azione**: 
- ✅ Creati nuovi file organizzati
- ⏳ Rimuovere vecchi file dopo conferma

---

## 📦 Script Deprecati

### Shell Scripts da Valutare
```
check_card_mapping.php
check_indexes.php
check_superadmin.php
check_unmapped_sets.php
count
diagnose_import.sh
find_mapping_match.php
getMessage
id
import_batches.sh
import_gradual.sh
map_main_sets.php
monitor_import.sh
name
pricing_plan_id
renew_date
resume_import.sh
run-pipeline-background.sh
setup-deck-evaluation.sh
simulate-etl-pipeline.sh
simulate-etl-pipeline_onlypro.sh
test_episode_endpoint.php
test_episodes_list.php
test_rapidapi.php
test_rapidapi_match.php
test_sdk.php
unmapped.php
```

**Stato**: Da verificare quali ancora in uso

**Azione consigliata**:
1. Testare ogni script per capire funzione
2. Se obsoleto → eliminare
3. Se ancora utile → spostare in `scripts/` directory con documentazione
4. Mantenere solo `deploy.sh` e `deploy-rapidapi-sync.sh` in root

---

## 🗂️ Directory Cleanup Suggestions

### Proposta struttura files root
```
/
├── README.md                  # Overview progetto
├── PROJECT_STATUS.md          # Status completo applicazione
├── OPERATIONS.md              # Comandi operativi
├── ROADMAP.md                 # Sviluppi futuri
├── DEPRECATION.md             # Questo file
├── deploy.sh                  # Script deploy principale
├── composer.json
├── package.json
├── artisan
├── phpunit.xml
├── tailwind.config.js
├── vite.config.js
├── postcss.config.js
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── node_modules/
├── docs/                      # Solo docs tecniche dettagliate
│   └── [file tecnici specifici]
└── scripts/                   # Script utility (da creare)
    └── [script PHP/bash utility]
```

---

## ⚙️ Config Deprecato

### Environment Variables non più usate
```
POKEMON_TCG_API_KEY=xxx  # Se usava Pokemon TCG API
TCGDEX_API_URL=xxx       # Se usava TCGDex
```

**Azione**: Rimuovere da `.env.example` se presenti

---

## 🔧 Code Patterns Deprecati

### Pattern da evitare
1. **Direct query senza game scoping**: Sempre usare `->forCurrentGame()` scope
2. **Hardcoded text**: Sempre usare `__('messages.key')`
3. **Inline styles**: Usare Tailwind classes
4. **jQuery**: Usare Alpine.js

### Pattern consigliati
```php
// ✅ Corretto
$cards = Card::forCurrentGame()->get();

// ❌ Deprecato
$cards = Card::where('game_id', 1)->get();
```

```blade
{{-- ✅ Corretto --}}
{{ __('messages.welcome') }}

{{-- ❌ Deprecato --}}
Welcome
```

---

## 📝 Database Migrations da Cleanup

### Migrations molto vecchie
Verificare se ci sono migrations di test/sviluppo che possono essere consolidate

**Azione**: Da valutare in futuro refactoring database

---

## 🚫 Features Non Implementate da Rimuovere

### Codice per features mai completate
- [ ] Verificare presenza di controller/model per feature non attive
- [ ] Verificare routes non utilizzate
- [ ] Verificare views non linkate da nessuna parte

**Tool suggerito**: 
```bash
# Find unused routes
php artisan route:list --unused

# Find unused views
find resources/views -name "*.blade.php" -exec grep -l "view('{}'" {} \;
```

---

## 📊 Deprecation Timeline

| Item | Deprecato il | Rimozione prevista | Status |
|------|-------------|-------------------|---------|
| Pokemon TCG API | 27 Dic 2025 | 31 Mar 2026 | ⏳ Pending |
| Vecchi MD files | 13 Gen 2026 | 31 Gen 2026 | ⏳ Pending |
| TCGDex System | TBD | TBD | 🔍 Review |
| Script root folder | 13 Gen 2026 | 31 Gen 2026 | 🔍 Review |

---

## 📋 Checklist Deprecation Process

Quando si depreca qualcosa, seguire questo processo:

1. **Documentare qui** con data e motivo
2. **Rinominare** con prefisso `deprecated_` (database) o `OLD_` (files)
3. **Aggiungere warning** nel codice se ancora referenziato
4. **Comunicare** al team (se multi-developer)
5. **Wait period** (almeno 30 giorni)
6. **Verificare** nessun utilizzo residuo
7. **Rimuovere** definitivamente
8. **Git commit** con messaggio chiaro

---

## 💡 Notes

- Mai rimuovere nulla senza backup preventivo
- Verificare sempre dependencies prima di eliminare
- Documentare sempre decisione di deprecazione
- Preferire rinominare a eliminare immediatamente (safety net)
