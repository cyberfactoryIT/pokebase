# Collection & Deck Management System

## Overview

The Collection & Deck Management system is a comprehensive card tracking and deck building feature that allows users to:
- Track their personal card collection with quantities, conditions, and foil status
- Create and manage multiple decks
- Quickly add cards from their collection to decks
- Search and add cards from the full catalog
- See which deck cards they don't own yet

---

## Database Structure

### `user_collection` Table
Stores all cards owned by users with detailed metadata.

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users table
- `product_id` - Foreign key to tcgcsv_products (the actual card)
- `quantity` - Integer (number of copies owned)
- `condition` - String (Near Mint, Lightly Played, Moderately Played, Heavily Played, Damaged)
- `is_foil` - Boolean (whether the card is foil/holo)
- `notes` - Text (user notes about the card)
- `created_at` / `updated_at` - Timestamps

**Indexes:**
- Unique index on (`user_id`, `product_id`) - prevents duplicate entries
- Foreign key constraints ensure referential integrity

### `deck_cards` Table
Stores cards within user decks.

**Columns:**
- `id` - Primary key
- `deck_id` - Foreign key to decks table
- `product_id` - Foreign key to tcgcsv_products (direct reference, no intermediate table)
- `quantity` - Integer (number of copies in deck, max 4 for most TCGs)
- `created_at` / `updated_at` - Timestamps

**Key Change:** Originally used `card_catalog_id` as an intermediate table, but was migrated to use `product_id` directly for simpler relationships and better performance.

---

## Models & Relationships

### `User` Model
**Relationships:**
```php
// Get all user's decks
public function decks()
{
    return $this->hasMany(Deck::class);
}

// Get all cards in user's collection
public function collection()
{
    return $this->hasMany(UserCollection::class);
}
```

### `UserCollection` Model
**Fillable Fields:** `user_id`, `product_id`, `quantity`, `condition`, `is_foil`, `notes`

**Relationships:**
```php
// Get the user who owns this collection entry
public function user()
{
    return $this->belongsTo(User::class);
}

// Get the actual card (TcgcsvProduct)
public function card()
{
    return $this->belongsTo(TcgcsvProduct::class, 'product_id', 'product_id');
}
```

### `DeckCard` Model
**Fillable Fields:** `deck_id`, `product_id`, `quantity`

**Relationships:**
```php
// Get the deck this card belongs to
public function deck()
{
    return $this->belongsTo(Deck::class);
}

// Get the actual card (TcgcsvProduct)
public function card()
{
    return $this->belongsTo(TcgcsvProduct::class, 'product_id', 'product_id');
}
```

---

## Controllers

### `CollectionController`
Handles all collection CRUD operations.

**Key Methods:**

#### `index()`
- Displays user's entire collection
- Shows stats: total unique cards, total quantity, most common set
- Displays cards in grid with images, quantities, condition badges, foil indicators
- Includes quick add search box for adding new cards

#### `add(Request $request)`
- Validates: `product_id` (required), `quantity` (default 1), `condition`, `is_foil`, `notes`
- Uses `updateOrCreate()` to either create new entry or increment quantity if card already exists
- Returns JSON response for AJAX calls or redirects for form submissions

#### `remove(UserCollection $collection)`
- Soft deletes collection entry
- Decrements stats

#### `update(Request $request, UserCollection $collection)`
- Updates quantity, condition, is_foil, or notes
- Validates input

#### `checkCard($productId)`
- Returns JSON with boolean indicating if user owns the card
- Used for real-time ownership checking

---

### `DeckController`
Handles deck CRUD and card management.

**Key Methods:**

#### `index()`
- Lists all user's decks
- Shows card count and deck format for each

#### `show(Deck $deck)`
- Displays deck details and all cards in the deck
- **Dual search interface:**
  - Left column: Search within user's collection only (`collection_only=1`)
  - Right column: Search full catalog with ownership indicators
- **Ownership badges:** Cards not in collection show "Not in Collection" badge with quick-add button
- Shows quantity controls and remove buttons for each card

#### `addCard(Request $request, Deck $deck)`
- Validates: `product_id` (required), `quantity` (default 1)
- Uses `updateOrCreate()` to add card or increment quantity
- Returns JSON for AJAX or redirects

#### `removeCard(Deck $deck, DeckCard $deckCard)`
- Removes card from deck
- Deletes the deck_card entry

#### `updateCardQuantity(Request $request, Deck $deck, DeckCard $deckCard)`
- Updates quantity (typically 1-4 for TCGs)
- Validates min 1, max 4

---

### `Api\CardSearchController`
Global typeahead search API with collection filtering.

**Route:** `GET /api/search/cards`

**Parameters:**
- `q` - Search query (min 2 chars)
- `limit` - Max results (default 12)
- `collection_only` - Boolean (filter by user's collection)

**Authentication Handling:**
- Uses `web` middleware to enable session-based authentication
- `collection_only` filter only applies when user is authenticated
- If not authenticated, searches full catalog regardless of `collection_only` parameter

**Query Logic:**
```php
if ($collectionOnly && Auth::check()) {
    $userId = Auth::id();
    $results->whereIn('tcgcsv_products.product_id', function($query) use ($userId) {
        $query->select('product_id')
            ->from('user_collection')
            ->where('user_id', $userId);
    });
}
```

**Ranking:**
1. Prefix matches first (name starts with query)
2. Newer sets first (published_on DESC)
3. Card number ASC
4. ID ASC (tiebreaker)

**Response Format:**
```json
[
    {
        "product_id": 123,
        "name": "Charizard",
        "card_number": "4",
        "group_id": 1,
        "set_name": "Base Set",
        "group_name": "Base Set",
        "group_published_on": "1999-01-09",
        "image_url": "https://..."
    }
]
```

---

### `Api\CollectionController`
Lightweight endpoint for fetching user's collection IDs.

**Route:** `GET /collection/ids`

**Purpose:** Returns only an array of `product_id` values from user's collection for client-side ownership checking.

**Response Format:**
```json
[1234, 5678, 9012, ...]
```

**Usage:** JavaScript loads this once on page load and stores in a `Set()` for instant O(1) lookup when displaying catalog cards.

---

## Frontend Features

### Collection Page (`/collection`)

**Quick Add Search Box:**
- Typeahead search with debouncing (300ms)
- Searches full catalog via `/api/search/cards`
- Opens modal with form for:
  - Quantity (number input)
  - Condition (select dropdown)
  - Foil (checkbox)
  - Notes (textarea)

**Collection Grid:**
- Card images with quantity badges
- Condition badges (color-coded)
- Foil indicators (sparkle icon)
- Click card for detail view
- Actions: Edit quantity, Remove from collection

**Stats Section:**
- Total Unique Cards
- Total Card Quantity
- Most Collected Set

---

### Deck Show Page (`/decks/{id}`)

**Dual Search System:**

**Left Column - "Add from Collection":**
- Blue-themed search box
- Searches with `collection_only=1` parameter
- Only shows cards user owns
- Limit: 20 results
- Click card to add to deck

**Right Column - "Add from Catalog":**
- Purple-themed search box
- Searches full catalog (no `collection_only` filter)
- Shows ownership status for each card:
  - If card IS in collection: Blue "Deck" button
  - If card NOT in collection: Orange "(Not in Collection)" badge + Green "Collection" button + Orange "Deck" button
- **Quick Add to Collection:** Green button adds card to collection with quantity=1, then refreshes search
- Limit: Default 12 results

**Deck Card List:**
- Each card shows:
  - Thumbnail image
  - Card name
  - Set name and number
  - **Ownership indicator:** If card not in collection, shows orange "Not in Collection" badge with green "+" button
  - Quantity input (auto-submit on change)
  - Remove button (red trash icon)

**JavaScript Functionality:**

**loadUserCollectionIds():**
```javascript
// Fetches /collection/ids on page load
// Stores result in Set() for O(1) lookup
const userCollectionProductIds = new Set([1234, 5678, ...]);
```

**searchCollectionCards(query):**
```javascript
// Searches with collection_only=1
fetch(`/api/search/cards?q=${query}&collection_only=1&limit=20`)
```

**searchCatalogCards(query):**
```javascript
// Searches full catalog
fetch(`/api/search/cards?q=${query}`)
// Checks each result against userCollectionProductIds Set
const inCollection = userCollectionProductIds.has(card.product_id);
```

**quickAddToCollection(productId, cardName):**
```javascript
// POSTs to /collection/add
// Adds to userCollectionProductIds Set
// Refreshes catalog search to update UI
```

**quickAddCardToCollection(productId, cardName, form):**
```javascript
// Used for inline "+" button in deck card list
// Adds card to collection and reloads page to update badge
```

---

## Navigation Shortcuts

### Collection Icon (Header)
- Always visible in top navigation
- Purple icon with active state highlighting
- Direct link to `/collection`

### Deck Dropdown (Header)
- Shows latest 10 decks with card counts
- "View All Decks" link at bottom
- Empty state with "Create Deck" button if no decks exist
- Updates on hover (Alpine.js)

---

## User Flow Examples

### Flow 1: Building a Deck from Collection
1. User clicks "Decks" → "Create New Deck"
2. Names deck (e.g., "Fire Deck")
3. In deck view, uses left search box "Add from Collection"
4. Types "Char" → sees only Charizard cards they own
5. Clicks card → added to deck with quantity 1
6. Repeats for other cards in collection

### Flow 2: Adding Missing Cards
1. User opens existing deck
2. Sees 3 cards with "Not in Collection" badges
3. Clicks green "+" button on first card
4. Card added to collection with quantity 1
5. Page reloads, badge disappears
6. User can now track that they need to physically acquire this card

### Flow 3: Discovering New Cards for Deck
1. User in deck view, right search box "Add from Catalog"
2. Types "Pika" → sees all Pikachu cards
3. Sees "(Not in Collection)" on several cards
4. Clicks green "Collection" button → card added to collection
5. Clicks orange "Deck" button → card added to deck
6. Both userCollectionProductIds Set and deck updated in real-time

---

## API Authentication Flow

**Problem:** API routes (`/api/*`) don't have session middleware by default in Laravel.

**Solution:** Added `web` middleware to search endpoint:
```php
Route::middleware(['web'])->get('/search/cards', [CardSearchController::class, 'index']);
```

**Result:** 
- `Auth::check()` now returns true for logged-in users
- `collection_only` filter works correctly
- Maintains CSRF protection via X-CSRF-TOKEN header

---

## Performance Considerations

### Collection ID Loading
- Loads once per page via `/collection/ids`
- Stores in JavaScript `Set()` for O(1) lookups
- Avoids repeated database queries for ownership checking

### Search Debouncing
- 300ms delay before API call
- Prevents excessive requests while typing
- Request ID tracking prevents race conditions

### Query Optimization
- Indexes on `user_id` and `product_id` columns
- Efficient `whereIn` subqueries for collection filtering
- LIMIT clauses prevent large result sets

### Image Loading
- Lazy loading on collection grid
- Thumbnails in search results
- Fallback to icon if image_url is null

---

## Future Enhancements (Potential)

### Collection Features
- **Import/Export:** CSV import for bulk adding cards
- **Wishlist:** Separate list for cards user wants to acquire
- **Price Tracking:** Integration with TCGPlayer API for collection value
- **Trade Lists:** Mark cards as available for trade
- **Condition Photos:** Upload images of card condition

### Deck Features
- **Deck Statistics:** Mana curve, type distribution, rarity breakdown
- **Deck Validation:** Check if deck meets format rules (60 cards for Standard, etc.)
- **Deck Sharing:** Public URLs for sharing decks
- **Deck Cloning:** Duplicate deck as starting point
- **Sideboard:** Separate 15-card sideboard section
- **Proxy Printing:** Generate printable proxy sheets for testing

### Search & Filtering
- **Advanced Filters:** Filter by rarity, type, set, cost
- **Saved Searches:** Save common search queries
- **Recent Searches:** Show last 5 searches
- **Autocomplete Improvements:** Show card images in dropdown
- **Keyboard Navigation:** Arrow keys to navigate results

### Integration
- **Deck Testing:** Play against AI or other users
- **Tournament Tracking:** Record deck performance in events
- **Collection Sync:** Import from external tools (Moxfield, Archidekt)

---

## Technical Architecture Summary

**Backend (Laravel):**
- RESTful controllers with clear separation of concerns
- Eloquent ORM with optimized relationships
- API endpoints with session-based authentication
- Validation via Form Requests
- JSON responses for AJAX interactions

**Frontend (Blade + Alpine.js):**
- Server-side rendering with Blade templates
- Alpine.js for reactive components (modals, dropdowns)
- Vanilla JavaScript for search and API calls
- Tailwind CSS for styling
- Real-time ownership checking with client-side Set

**Database (MySQL):**
- Normalized schema with foreign keys
- Unique constraints prevent duplicates
- Indexes optimize lookups
- Timestamps track changes

**API Design:**
- RESTful conventions
- Consistent JSON responses
- Error handling with appropriate HTTP codes
- Web middleware for session support
- CORS and CSRF protection

---

## File Locations

### Controllers
- `app/Http/Controllers/CollectionController.php`
- `app/Http/Controllers/DeckController.php`
- `app/Http/Controllers/Api/CardSearchController.php`
- `app/Http/Controllers/Api/CollectionController.php`

### Models
- `app/Models/UserCollection.php`
- `app/Models/DeckCard.php`
- `app/Models/Deck.php`
- `app/Models/User.php`

### Views
- `resources/views/collection/index.blade.php`
- `resources/views/decks/index.blade.php`
- `resources/views/decks/show.blade.php`
- `resources/views/decks/create.blade.php`
- `resources/views/layouts/navigation.blade.php` (navigation shortcuts)

### Routes
- `routes/web.php` - Web routes (collection, decks, collection/ids)
- `routes/api.php` - API routes (search/cards)

### Migrations
- `database/migrations/XXXX_create_user_collection_table.php`
- `database/migrations/XXXX_modify_deck_cards_to_use_product_id.php`

---

## Testing Checklist

### Collection
- [ ] Add card to collection with all fields (quantity, condition, foil, notes)
- [ ] Add duplicate card (should increment quantity, not create duplicate)
- [ ] Remove card from collection
- [ ] Update card quantity, condition, notes
- [ ] Search and quick-add from collection page
- [ ] View collection stats

### Decks
- [ ] Create new deck
- [ ] Add card from collection search (left column)
- [ ] Add card from catalog search (right column)
- [ ] See "Not in Collection" badge for cards not owned
- [ ] Click "+" to add card to collection from deck view
- [ ] Update card quantity in deck
- [ ] Remove card from deck
- [ ] Delete entire deck

### Search
- [ ] Typeahead works with 2+ characters
- [ ] Results update as typing
- [ ] Debouncing prevents excessive requests
- [ ] Collection filter shows only owned cards
- [ ] Catalog search shows all cards with ownership indicators
- [ ] Ownership checking works in real-time

### Navigation
- [ ] Collection icon in header links to collection
- [ ] Deck dropdown shows latest 10 decks
- [ ] Dropdown updates when new deck created
- [ ] Empty state shows "Create Deck" button

### Edge Cases
- [ ] User with no collection sees empty state
- [ ] User with no decks sees empty state
- [ ] Adding 5th copy of card to deck (should allow or show error based on rules)
- [ ] Long card names don't break layout
- [ ] Missing images show fallback icon
- [ ] Slow network doesn't break search (loading states)
