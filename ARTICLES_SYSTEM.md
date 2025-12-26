# 📰 Articles System Documentation

## Overview
Sistema completo di articoli informativi per Pokebase, con gestione admin e visualizzazione filtrata per gioco corrente.

## Features

### 🎯 User-Facing (Dashboard)
- **Grid responsive**: 1 col mobile → 2 col md → 3 col lg
- **Card per articolo** mostra:
  - Badge categoria
  - Immagine decorativa (se presente, max 1MB)
  - Excerpt (descrizione breve)
  - Button "Read more"
- **Accordion nativo HTML5** (`<details>/<summary>`) - zero JavaScript
- **Contenuto completo** con:
  - Titolo articolo
  - Body formattato (Markdown → HTML sicuro)
  - Link esterno (se presente) con `target="_blank"`
- **Filtro per categoria**: dropdown che ricarica la pagina

### 🛠️ Admin Interface (SuperAdmin Only)
- **CRUD completo** per articoli
- **Filtri avanzati**:
  - Per gioco
  - Per categoria
  - Ricerca testuale
- **Upload immagini** (max 1MB, automatico storage)
- **Editor Markdown** con preview sintassi
- **Sort order** e scheduling pubblicazione
- **Stato pubblicato/bozza**

## Database

### Table: articles
```sql
CREATE TABLE articles (
    id BIGINT UNSIGNED PRIMARY KEY,
    game_id BIGINT UNSIGNED,
    category VARCHAR(100),
    title VARCHAR(255),
    image_path VARCHAR(255) NULLABLE,
    excerpt TEXT,
    body LONGTEXT,
    external_url VARCHAR(255) NULLABLE,
    is_published BOOLEAN DEFAULT TRUE,
    published_at TIMESTAMP NULLABLE,
    sort_order INT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    INDEX idx_game_published (game_id, is_published),
    INDEX idx_game_publish_date (game_id, published_at)
);
```

## Routes

### Admin Routes (SuperAdmin only)
```
GET    /superadmin/articles              → List all
GET    /superadmin/articles/create       → Create form
POST   /superadmin/articles              → Store
GET    /superadmin/articles/{id}/edit    → Edit form
PUT    /superadmin/articles/{id}         → Update
DELETE /superadmin/articles/{id}         → Delete
```

### Public Routes
```
GET    /dashboard?article_category={cat} → Dashboard with filtered articles
```

## Models & Relationships

### Article Model
```php
Article::class
- belongsTo(Game::class)
- scopePublished()
- scopeForCurrentGame()
- getBodyHtmlAttribute() // Markdown → HTML
```

### Game Model
```php
Game::class
- hasMany(Article::class)
```

## Security

### XSS Prevention
- HTML escaping con `htmlspecialchars()`
- Markdown parser custom con whitelist tag
- No `{!! !!}` su input utente diretto

### Image Upload
- Max 1MB
- Validazione tipo MIME
- Storage in `storage/app/public/articles/`
- Symlink `public/storage → storage/app/public`

### Access Control
- Middleware `EnsureSuperAdmin`
- Verifica `auth()->user()->hasRole('superadmin')`
- Route group protetto

## Markdown Support

Supportati:
- `## Headers` → `<h2>`
- `**bold**` → `<strong>`
- `*italic*` → `<em>`
- `[text](url)` → `<a href="url">`
- `- list` → `<ul><li>`
- Paragrafi automatici

## Seeding

```bash
php artisan db:seed --class=ArticlesSeeder
```

Crea **12 articoli** (4 per gioco):
- **Pokémon**: Getting Started, Card Rarity, Collection Protection, Market Basics
- **Magic**: Introduction, The Stack, Reserved List, Formats
- **Yu-Gi-Oh**: TCG Basics, Extra Deck, Card Editions, Counterfeit Detection

## Testing Checklist

- [ ] Dashboard mostra articoli del gioco corrente
- [ ] Filtro categoria funziona
- [ ] Accordion si apre/chiude
- [ ] Markdown viene renderizzato correttamente
- [ ] Link esterni aprono in nuova tab
- [ ] SuperAdmin può creare/modificare/eliminare
- [ ] Upload immagini funziona
- [ ] Non-superadmin non può accedere ad admin

## Future Enhancements

- [ ] Rich text editor (TinyMCE/Quill) invece di Markdown raw
- [ ] Preview live durante editing
- [ ] Statistiche visualizzazioni
- [ ] Comments/reactions
- [ ] Multi-lingua
- [ ] SEO metadata
- [ ] Related articles

---
*Created: 26 December 2025*
