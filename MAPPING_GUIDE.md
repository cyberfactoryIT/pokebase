# Sistema di Mapping delle Estensioni

Questo documento spiega come individuare e gestire le estensioni (set) NON mappate tra i tre sistemi di dati: **TCGCSV**, **RapidAPI** e **TCGdex**.

## 📊 Panoramica del Sistema

Il progetto Pokebase integra dati da tre fonti principali per le carte Pokémon:

1. **TCGCSV** (TCGplayer) - Database principale dei prezzi
2. **RapidAPI** - Dati delle carte con immagini e metadati
3. **TCGdex** - Database alternativo con set e carte

### Relazioni di Mapping

```
TCGCSV Groups (212) ←→ RapidAPI Episodes (172)
       ↓
TCGdex Sets (22)
```

- **TCGCSV Groups** contiene le espansioni/set di TCGplayer
- **RapidAPI Episodes** contiene i set con dati aggiuntivi (logo, slug, ecc.)
- **TCGdex Sets** contiene set con ID univoci (es. "swsh7", "base1")

## 🔍 Come Individuare le Estensioni Non Mappate

### 1. Script Automatico (Consigliato)

Usa lo script dedicato per vedere tutte le estensioni non mappate:

```bash
./check_unmapped_sets.php
```

Questo script mostra:
- ✅ TCGCSV Groups senza RapidAPI Episode mapping
- ✅ RapidAPI Episodes senza TCGCSV Group mapping  
- ✅ TCGCSV Groups senza TCGdex Set mapping
- ✅ Statistiche complete di copertura
- ✅ Suggerimenti per il mapping automatico

### 2. Query SQL Manuali

#### Trovare TCGCSV senza RapidAPI

```sql
SELECT 
    group_id,
    name,
    abbreviation,
    published_on
FROM tcgcsv_groups
WHERE rapidapi_episode_id IS NULL
  AND category_id = 3  -- Pokemon
ORDER BY published_on DESC;
```

#### Trovare RapidAPI senza TCGCSV

```sql
SELECT 
    re.episode_id,
    re.name,
    re.code,
    re.slug
FROM rapidapi_episodes re
LEFT JOIN tcgcsv_groups tg ON re.episode_id = tg.rapidapi_episode_id
WHERE tg.rapidapi_episode_id IS NULL
  AND re.game = 'pokemon'
ORDER BY re.episode_id DESC;
```

#### Trovare TCGCSV senza TCGdex

```sql
SELECT 
    group_id,
    name,
    abbreviation,
    tcgdex_set_id
FROM tcgcsv_groups
WHERE tcgdex_set_id IS NULL
  AND category_id = 3  -- Pokemon
ORDER BY published_on DESC;
```

### 3. Altri Script Utili

#### Vedere TCGdex non mappati

```bash
./unmapped.php
```

Mostra i set TCGdex che NON hanno un mapping verso TCGCSV (escludendo i set Pocket).

#### Testare match specifici

```bash
./test_rapidapi_match.php
```

Testa il matching per alcuni set TCGdex specifici verso RapidAPI.

## ⚙️ Come Mappare le Estensioni

### 1. Mapping Automatico

#### Mappare RapidAPI → TCGCSV

```bash
# Test (dry-run)
php artisan rapidapi:map-episodes --dry-run

# Esecuzione reale
php artisan rapidapi:map-episodes

# Forza update anche per set già mappati
php artisan rapidapi:map-episodes --force
```

**Logica di matching:**
- Match esatto per codice abbreviazione (`abbreviation = code`)
- Match parziale per nome (`name LIKE %episode_name%`)
- Popola automaticamente `logo_url` e `rapidapi_episode_id`

#### Mappare TCGdex → TCGCSV

```bash
# Test (dry-run)
php artisan tcgdex:map-to-tcgcsv --dry-run

# Solo set (senza carte)
php artisan tcgdex:map-to-tcgcsv --sets-only

# Esecuzione completa (set + carte)
php artisan tcgdex:map-to-tcgcsv
```

**Logica di matching:**
- Match esatto per nome (`name_en = group.name`)
- Pattern matching per serie moderne (SWSH##, SV##, SM##, XY##)
- Pattern matching per McDonald's collections
- Pattern matching per Black Star Promos
- Pattern matching per Trainer Kits
- Fuzzy matching (similarità > 85%)

### 2. Mapping Manuale

Se il mapping automatico non riesce, puoi mappare manualmente via SQL:

#### Mappare TCGCSV → RapidAPI

```sql
UPDATE tcgcsv_groups 
SET rapidapi_episode_id = 347,  -- ID dell'episodio RapidAPI
    logo_url = 'https://...'     -- URL del logo (opzionale)
WHERE group_id = 3170;           -- ID del gruppo TCGCSV
```

#### Mappare TCGCSV → TCGdex

```sql
UPDATE tcgcsv_groups 
SET tcgdex_set_id = 'swsh12'    -- ID del set TCGdex
WHERE group_id = 3170;           -- ID del gruppo TCGCSV
```

## 📈 Monitoraggio delle Statistiche

### Vedere le statistiche di mapping correnti

```bash
./check_unmapped_sets.php
```

L'output include:
```
TCGCSV → RapidAPI:     109/212 (51.4%)
TCGCSV → TCGdex:       17/212 (8%)
Completamente mappati: 15/212 (7.1%)
```

### Verificare set specifici

```sql
SELECT 
    tg.group_id,
    tg.name AS tcgcsv_name,
    tg.abbreviation,
    re.episode_id AS rapidapi_id,
    re.name AS rapidapi_name,
    tg.tcgdex_set_id,
    ts.name_en AS tcgdex_name
FROM tcgcsv_groups tg
LEFT JOIN rapidapi_episodes re ON tg.rapidapi_episode_id = re.episode_id
LEFT JOIN tcgdx_sets ts ON tg.tcgdex_set_id = ts.tcgdex_id
WHERE tg.category_id = 3
ORDER BY tg.published_on DESC
LIMIT 20;
```

## 🔄 Esecuzione Automatica

Il sistema ha scheduler automatici configurati in [routes/console.php](routes/console.php):

```php
// RapidAPI Episodes Mapping: Run daily at 5:30 AM
Schedule::command('rapidapi:map-episodes')
    ->timezone('Europe/Copenhagen')
    ->dailyAt('05:30');

// TCGdex to TCGCSV Mapping: Run daily at 5:50 AM
Schedule::command('tcgdex:map-to-tcgcsv --sets-only')
    ->timezone('Europe/Copenhagen')
    ->dailyAt('05:50');
```

## 🛠️ Problemi Comuni e Soluzioni

### Set non trovati nonostante esistano

**Problema:** Un set esiste in entrambi i database ma non viene mappato automaticamente.

**Soluzione:**
1. Controlla i nomi esatti: potrebbero avere differenze minime
2. Usa mapping manuale se necessario
3. Aggiungi pattern personalizzati nel comando `MapTcgdexToTcgcsvCommand.php`

### RapidAPI Episode senza codice

**Problema:** Alcuni episodi RapidAPI non hanno campo `code`.

**Soluzione:**
Il sistema fa fallback sul matching per nome. Verifica con:
```sql
SELECT * FROM rapidapi_episodes WHERE code IS NULL;
```

### Differenze tra sistemi

| Sistema | Set "Base Set" | Set "SWSH07" |
|---------|----------------|--------------|
| TCGCSV  | "Base Set" | "SWSH07: Evolving Skies" |
| RapidAPI | "Base Set" | "Evolving Skies" |
| TCGdex  | "base1" | "swsh7" |

I comandi di mapping gestiscono automaticamente queste differenze.

## 📝 File Correlati

- [app/Console/Commands/MapRapidApiEpisodesToGroupsCommand.php](app/Console/Commands/MapRapidApiEpisodesToGroupsCommand.php) - Mapping RapidAPI → TCGCSV
- [app/Console/Commands/MapTcgdexToTcgcsvCommand.php](app/Console/Commands/MapTcgdexToTcgcsvCommand.php) - Mapping TCGdex → TCGCSV
- [check_unmapped_sets.php](check_unmapped_sets.php) - Script di analisi completa
- [unmapped.php](unmapped.php) - Script per TCGdex unmapped
- [test_rapidapi_match.php](test_rapidapi_match.php) - Test matching specifici
- [TCGCSV_README.md](TCGCSV_README.md) - Documentazione TCGCSV

## 🎯 Best Practices

1. **Esegui sempre dry-run prima** per vedere cosa verrà modificato
2. **Mappa prima RapidAPI** (per avere logo e slug)
3. **Poi mappa TCGdex** (per avere ID univoci e carte)
4. **Verifica manualmente** i set recenti o speciali (promos, trainer kits)
5. **Usa lo script di analisi** regolarmente per monitorare la copertura

## 📞 Supporto

Se hai problemi con il mapping, controlla:
1. I log di Laravel in `storage/logs/`
2. La tabella `pipeline_runs` per tracciare le esecuzioni
3. Gli output degli script di test

Per mappature complesse, considera di estendere i pattern nei comandi di mapping esistenti.
