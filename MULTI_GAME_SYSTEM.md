# Multi-Game System Architecture

## Overview

Il sistema supporta **più giochi TCG** (Pokemon, Magic, etc.) attraverso la tabella `games` che fa da hub centrale per:
- Collezioni utenti
- Mazzi (decks)
- Catalogo carte
- Integrazione con TCGCSV

---

## Tabella `games`

### Struttura
```php
Schema::create('games', function (Blueprint $table) {
    $table->id();
    $table->string('name');                    // es. "Pokémon TCG"
    $table->string('code')->unique();          // es. "pokemon"
    $table->integer('tcgcsv_category_id')->nullable()->unique(); // Mapping con TCGCSV
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Mapping TCGCSV
La colonna `tcgcsv_category_id` mappa i giochi interni con le **category** di TCGCSV:

| Game          | Internal ID | Code      | TCGCSV Category ID |
|---------------|-------------|-----------|-------------------|
| Pokémon TCG   | 1           | pokemon   | 3                 |
| Magic: The Gathering | -    | mtg       | 1                 |
| Yu-Gi-Oh!     | -           | yugioh    | 2                 |

**Nota**: Il `category_id` di TCGCSV è diverso dal nostro `game_id` interno.

---

## Model `Game`

### File
[app/Models/Game.php](app/Models/Game.php)

### Relazioni

```php
// Get all decks for this game
$game->decks()

// Get all cards in card catalog
$game->cardCatalog()

// Get all user collections for this game
$game->userCards()

// Get TCGCSV groups (sets) for this game
$game->tcgcsvGroups()

// Get TCGCSV products (cards) for this game
$game->tcgcsvProducts()
```

### Helper Methods

```php
// Get Pokemon game
$pokemon = Game::pokemon();

// Future games
$mtg = Game::where('code', 'mtg')->first();
```

---

## Foreign Keys in altre tabelle

### `decks` table
```php
$table->foreignId('game_id')
    ->nullable()
    ->constrained('games')
    ->nullOnDelete();
```

### `card_catalog` table
```php
$table->foreignId('game_id')
    ->nullable()
    ->constrained('games')
    ->nullOnDelete();
```

### `user_collection` table
```php
$table->foreignId('game_id')
    ->nullable()
    ->constrained('games')
    ->nullOnDelete();
```

**Nota**: `nullable()` per compatibilità con dati esistenti pre-multigioco.

---

## Come funziona l'integrazione TCGCSV

### 1. Import Groups (Sets)
Quando importi gruppi da TCGCSV:
```bash
php artisan tcgcsv:import-pokemon --only=groups
```

Il sistema importa tutti i set con `category_id=3` (Pokemon) nella tabella `tcgcsv_groups`.

### 2. Import Products (Cards)
```bash
php artisan tcgcsv:import-pokemon --groupId=123
```

Importa tutte le carte del set 123 con `category_id=3` in `tcgcsv_products`.

### 3. Relazione con Game
Per collegare prodotti TCGCSV con il nostro sistema di giochi:

```php
// Get Pokemon game
$pokemon = Game::pokemon(); // id=1, tcgcsv_category_id=3

// Get all TCGCSV products for this game
$cards = TcgcsvProduct::where('category_id', $pokemon->tcgcsv_category_id)->get();

// Or using the relation
$cards = $pokemon->tcgcsvProducts()->get();
```

---

## Aggiungere un nuovo gioco

### Step 1: Aggiorna GameSeeder
```php
DB::table('games')->updateOrInsert(
    ['code' => 'mtg'],
    [
        'name' => 'Magic: The Gathering',
        'tcgcsv_category_id' => 1, // MTG category in TCGCSV
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]
);
```

### Step 2: Run Seeder
```bash
php artisan db:seed --class=GameSeeder
```

### Step 3: Import data da TCGCSV
Crea un nuovo command simile a `TcgcsvImportPokemon`:
```bash
php artisan make:command TcgcsvImportMtg
```

Configura `category_id = 1` per MTG nel nuovo service.

### Step 4: Aggiorna UI
- Aggiungi filtri per gioco nelle view
- Aggiungi selettori di gioco nei form di creazione deck
- Filtra collezioni per gioco

---

## Query comuni

### Collezione per gioco
```php
$pokemonCards = UserCollection::query()
    ->whereHas('card', function($q) {
        $q->where('category_id', 3); // Pokemon
    })
    ->get();
```

### Deck per gioco
```php
$pokemonDecks = Deck::where('game_id', 1)->get();
```

### Statistiche per gioco
```php
$stats = Game::withCount([
    'decks',
    'userCards',
    'cardCatalog'
])->get();
```

---

## Vantaggi del sistema multi-gioco

✅ **Separazione dati** - Collezioni e mazzi separati per gioco  
✅ **Scalabilità** - Facile aggiungere nuovi giochi  
✅ **Flessibilità** - Ogni gioco può avere regole diverse  
✅ **Integrazione TCGCSV** - Mapping diretto con category_id  
✅ **Performance** - Query filtrate per gioco sono più veloci  

---

## Note tecniche

### Perché `game_id` è nullable?
Per compatibilità con dati esistenti creati prima dell'implementazione multi-gioco. In futuro potrebbe essere reso `NOT NULL` dopo migrazione dati.

### Differenza tra `game_id` e `tcgcsv_category_id`
- `game_id`: ID interno del nostro database (auto-increment)
- `tcgcsv_category_id`: ID esterno usato da TCGCSV API

Esempio:
- Pokemon: `game_id=1`, `tcgcsv_category_id=3`
- MTG: `game_id=2`, `tcgcsv_category_id=1`

Questo permette di mappare correttamente i dati anche se gli ID non corrispondono.
