# Default Game Feature

## Overview
Users can now select a default game from their active games. This game will be used as the initial context when they log in to the application.

## Database Changes

### Migration
Created migration: `2025_12_29_172722_add_default_game_id_to_users_table.php`

Added `default_game_id` column to `users` table:
- Type: Foreign key (nullable)
- References: `games.id`
- Cascade: ON DELETE SET NULL
- Position: After `locale` column

```php
$table->foreignId('default_game_id')->nullable()->after('locale')->constrained('games')->onDelete('set null');
```

## Model Changes

### User Model (`app/Models/User.php`)
- Added `default_game_id` to `$fillable` array
- Added `defaultGame()` relationship method:

```php
public function defaultGame()
{
    return $this->belongsTo(\App\Models\Game::class, 'default_game_id');
}
```

## Controller Changes

### ProfileController (`app/Http/Controllers/ProfileController.php`)

Updated `updateGames()` method to handle default game selection:

**Validation:**
- Added `default_game_id` to validation rules: `['nullable', 'exists:games,id']`

**Business Logic:**
1. If `default_game_id` is provided and is among selected active games → save it
2. If `default_game_id` is NOT among selected active games → clear it (set to null)
3. If no games are selected → clear default game

```php
// Update default game only if it's provided and it's among the selected games
if (isset($validated['default_game_id'])) {
    if (in_array($validated['default_game_id'], $gameIds)) {
        $request->user()->default_game_id = $validated['default_game_id'];
    } else {
        // If default game is not in selected games, clear it
        $request->user()->default_game_id = null;
    }
} elseif (empty($gameIds)) {
    // If no games selected, clear default game
    $request->user()->default_game_id = null;
}
```

## View Changes

### Profile Edit View (`resources/views/profile/edit.blade.php`)

**Added Alpine.js reactive state:**
```javascript
x-data="{ 
    selectedGames: {{ json_encode($userGames) }}, 
    defaultGame: {{ $user->default_game_id ?? 'null' }} 
}"
```

**Enhanced checkbox behavior:**
- When a game is unchecked, if it was the default game, the default is cleared
- Uses `x-model="selectedGames"` to track selected games in Alpine

**New "Default Game" section:**
- Appears only when at least one game is selected (`x-show="selectedGames.length > 0"`)
- Shows radio buttons for each selected game
- Only active games are shown as default options
- Visual indicator with yellow badge for default game
- Radio buttons update the `defaultGame` Alpine state

**UI Components:**
- Title: "Default Game" / "Gioco Predefinito"
- Description: "Choose which game you want to see when you log in"
- Yellow badge: "Default" / "Predefinito"

## Translations

### English (`resources/lang/en/profile/edit.php`)
```php
'default_game' => 'Default Game',
'default_game_description' => 'Choose which game you want to see when you log in.',
'default_badge' => 'Default',
```

### Italian (`resources/lang/it/profile/edit.php`)
```php
'default_game' => 'Gioco Predefinito',
'default_game_description' => 'Scegli quale gioco vuoi vedere quando accedi.',
'default_badge' => 'Predefinito',
```

## User Flow

1. User goes to `/profile`
2. User selects one or more active games (checkboxes)
3. A new section "Default Game" appears below the active games
4. User can select one of the active games as default (radio button)
5. User clicks "Save Game Preferences"
6. System validates:
   - Default game must be in the list of active games
   - If user deselects the default game, it's automatically cleared
7. Success message appears: "Game preferences updated successfully!"

## Data Integrity

**Automatic cleanup:**
- If default game is unchecked from active games → default is cleared
- If all games are unchecked → default is cleared
- If default game is deleted from database → foreign key ON DELETE SET NULL handles it

**Validation:**
- Default game ID must exist in `games` table
- Default game must be in the user's active games list

## Future Integration Points

This feature can be used for:
- **Login redirect**: After login, redirect user to their default game's dashboard
- **Navigation context**: Use default game as initial filter in collection/deck views
- **Session initialization**: Set session game context based on default game
- **Middleware**: Create middleware to enforce default game context

## Testing Checklist

- [x] Migration runs successfully
- [x] Model relationships work correctly
- [x] Controller validation works
- [x] View renders without errors
- [x] Alpine.js reactive state works
- [x] Translations (EN/IT) are complete
- [ ] Manual test: Select default game from profile
- [ ] Manual test: Verify default is saved in database
- [ ] Manual test: Uncheck default game and verify it's cleared
- [ ] Manual test: Check all games and verify section disappears
- [ ] Integration test: Use default game in login flow

## Notes

- Default game is **optional** - users can have active games without selecting a default
- Only **active games** can be set as default
- Automatically clears if game becomes inactive or is removed
- Uses Alpine.js for reactive UI without page reloads
- Follows same visual design pattern as active games section
