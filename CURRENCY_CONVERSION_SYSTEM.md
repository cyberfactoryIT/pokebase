# Currency Conversion System

## Overview
Implemented a comprehensive currency conversion system that allows users to set a preferred currency in their profile. All prices throughout the application are automatically converted to the user's preferred currency, with the original price shown in parentheses.

## Features

### 1. User Profile Settings
- **Location**: [/profile](resources/views/profile/edit.blade.php)
- Users can select a preferred currency from a dropdown
- Supported currencies: EUR, USD, GBP, DKK, SEK, NOK, CHF, JPY, CAD, AUD
- If no currency is selected, default currencies are used:
  - Cardmarket prices: EUR
  - TCGPlayer prices: USD

### 2. Currency Service
- **Location**: [app/Services/CurrencyService.php](app/Services/CurrencyService.php)
- Handles all currency conversion logic
- Exchange rates (base: EUR, updated December 2025)
- Methods:
  - `getCurrencies()`: Get all available currencies
  - `getSymbol($currency)`: Get currency symbol
  - `convert($amount, $from, $to)`: Convert amount between currencies
  - `formatPrice($amount, $originalCurrency, $preferredCurrency)`: Format price with conversion

### 3. Helper Functions
- **Location**: [app/helpers.php](app/helpers.php)
- `formatPrice($amount, $originalCurrency, $user)`: Format price based on user preference
- `getUserCurrency($user, $defaultSource)`: Get user's preferred currency or default

### 4. Database Changes
- **Migration**: `2025_12_29_173416_add_preferred_currency_to_users_table.php` (already exists)
- Added columns to `users` table:
  - `preferred_currency` (varchar(3), nullable)
  - `default_game_id` (bigint unsigned, nullable, foreign key to games)

### 5. Updated Views

#### Collection View
- **Location**: [resources/views/collection/index.blade.php](resources/views/collection/index.blade.php)
- Shows collection value with currency toggle (EUR/USD)
- If user has preferred currency, displays converted value with original in parentheses
- Example: "74.60 kr (original price €10.00)"

#### Deck Valuation View
- **Location**: [resources/views/pokemon/deck_valuation/step3.blade.php](resources/views/pokemon/deck_valuation/step3.blade.php)
- Shows deck total value with currency conversion
- Individual card prices are also converted
- Format: "74.60 kr (€10.00)" for total, "7.46 kr each" for individual cards

#### Deck Show View
- **Location**: [resources/views/decks/show.blade.php](resources/views/decks/show.blade.php)
- Updated DeckController to calculate both EUR and USD prices
- Shows deck total value with EUR/USD toggle
- Individual cards display their converted prices
- Supports both Cardmarket (EUR) and TCGPlayer (USD) prices
- **Controller**: [app/Http/Controllers/DeckController.php](app/Http/Controllers/DeckController.php)
  - Method `getDeckTopStats()` calculates `total_value_eur`, `total_value_usd`
  - Loads `rapidapiCard` relationship for Cardmarket prices

### 6. Translations
Added translations for currency preferences in:
- [resources/lang/en/profile/edit.php](resources/lang/en/profile/edit.php)
- [resources/lang/it/profile/edit.php](resources/lang/it/profile/edit.php)
- [resources/lang/da/profile/edit.php](resources/lang/da/profile/edit.php)

Translation keys:
- `preferred_currency`: "Preferred Currency"
- `preferred_currency_description`: Description text
- `use_default_currency`: "Use default (EUR for Cardmarket, USD for TCGPlayer)"
- `original_price`: "Original price" (for collection view)

## Usage Examples

### In Controllers
```php
$user = auth()->user();
$preferredCurrency = $user->preferred_currency;

if ($preferredCurrency) {
    $displayValue = \App\Services\CurrencyService::convert(
        $originalValue, 
        'EUR', 
        $preferredCurrency
    );
}
```

### In Views
```php
@if($user->preferred_currency)
    @php
        $converted = \App\Services\CurrencyService::convert(
            $price, 
            'EUR', 
            $user->preferred_currency
        );
        $symbol = \App\Services\CurrencyService::getSymbol($user->preferred_currency);
    @endphp
    <p>{{ $symbol }}{{ number_format($converted, 2) }}</p>
    <p class="text-xs">({{ __('collection/index.original_price') }}: €{{ number_format($price, 2) }})</p>
@else
    <p>€{{ number_format($price, 2) }}</p>
@endif
```

### Using Helper Functions
```php
// In Blade templates
{{ formatPrice($price, 'EUR', auth()->user()) }}

// Returns: "74.60 kr (original price €10.00)" if user prefers DKK
// Returns: "€10.00" if user has no preference
```

## Exchange Rates (as of December 2025)

| Currency | Rate (base: EUR) |
|----------|------------------|
| EUR      | 1.00             |
| USD      | 1.05             |
| GBP      | 0.85             |
| DKK      | 7.46             |
| SEK      | 11.20            |
| NOK      | 11.50            |
| CHF      | 0.95             |
| JPY      | 155.0            |
| CAD      | 1.45             |
| AUD      | 1.65             |

## Testing

Test the system with Tinker:
```bash
php artisan tinker

# Test conversion
App\Services\CurrencyService::convert(10, 'EUR', 'DKK')
// Returns: 74.6

# Test formatting
App\Services\CurrencyService::formatPrice(10, 'EUR', 'DKK')
// Returns: "74.60 kr (original price €10.00)"

# Update user preference
$user = App\Models\User::first();
$user->preferred_currency = 'DKK';
$user->save();

# Test with user
formatPrice(10, 'EUR', $user)
// Returns: "74.60 kr (original price €10.00)"
```

## Future Improvements

1. **Dynamic Exchange Rates**: Integrate with an exchange rate API (e.g., exchangerate-api.com) for real-time rates
2. **Admin Panel**: Allow admins to update exchange rates manually
3. **More Currencies**: Add support for additional currencies based on user demand
4. **Card Details Page**: Apply currency conversion to individual card detail pages
5. **Price History**: Show price trends in user's preferred currency

## Notes

- Exchange rates are static and should be updated periodically
- Currency preference is saved per user and persists across sessions
- The EUR/USD toggle in collection and deck views works independently from the user preference
- Original prices are always shown in parentheses when a conversion is applied
- Symbol placement follows currency conventions (EUR/USD before amount, Nordic currencies after)
