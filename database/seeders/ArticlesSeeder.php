<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Game;
use Carbon\Carbon;

class ArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $games = Game::all();

        foreach ($games as $game) {
            $this->seedArticlesForGame($game);
        }
    }

    private function seedArticlesForGame(Game $game): void
    {
        $articles = match($game->code) {
            'pokemon' => $this->getPokemonArticles($game->id),
            'mtg' => $this->getMtgArticles($game->id),
            'yugioh' => $this->getYugiohArticles($game->id),
            default => [],
        };

        foreach ($articles as $article) {
            Article::updateOrInsert(
                ['game_id' => $game->id, 'title' => $article['title']],
                $article
            );
        }
    }

    private function getPokemonArticles(int $gameId): array
    {
        return [
            [
                'game_id' => $gameId,
                'category' => 'Getting Started',
                'title' => 'Welcome to Pokémon TCG',
                'image_path' => null,
                'excerpt' => 'Learn the basics of collecting and playing the Pokémon Trading Card Game.',
                'body' => "## Getting Started with Pokémon TCG

The Pokémon Trading Card Game (TCG) is a collectible card game based on the Pokémon video game series. Players use decks of 60 cards featuring Pokémon characters.

**Basic Rules:**
- Each player starts with 7 cards
- Place 6 Prize cards face down
- First to collect all Prize cards wins

**Card Types:**
- Pokémon cards (Basic, Stage 1, Stage 2)
- Trainer cards (Items, Supporters, Stadiums)
- Energy cards (for attacks)

To learn more, visit the [official Pokémon TCG website](https://www.pokemon.com/us/pokemon-tcg/).",
                'external_url' => 'https://www.pokemon.com/us/pokemon-tcg/how-to-play/',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(30),
                'sort_order' => 1,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Collecting',
                'title' => 'Understanding Card Rarity',
                'image_path' => null,
                'excerpt' => 'Different rarity levels affect card value and collectibility.',
                'body' => "## Card Rarity Guide

Pokémon cards come in different rarity levels, indicated by symbols at the bottom of the card.

**Rarity Symbols:**
- Circle: Common
- Diamond: Uncommon
- Star: Rare
- Star with \"H\": Holo Rare

**Special Rarities:**
- **Ultra Rare (UR)**: Full art cards with unique designs
- **Secret Rare (SR)**: Cards with numbers beyond the set size
- **Rainbow Rare**: Colorful textured cards

Rare cards are harder to find in booster packs and typically have higher market value.",
                'external_url' => null,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(25),
                'sort_order' => 2,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Safety',
                'title' => 'Protecting Your Collection',
                'image_path' => null,
                'excerpt' => 'Keep your valuable cards safe with proper storage and handling.',
                'body' => "## Collection Protection Tips

Proper care ensures your cards maintain their value and condition over time.

### Storage Solutions
- Use **penny sleeves** for basic protection
- Add **top loaders** for valuable cards
- Store in **binders** with acid-free pages

### Handling Best Practices
- Always wash hands before handling cards
- Hold cards by the edges
- Keep cards away from liquids and direct sunlight

### Grading Services
Consider professional grading (PSA, BGS) for high-value cards to authenticate and preserve condition.",
                'external_url' => null,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(20),
                'sort_order' => 3,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Market Basics',
                'title' => 'Buying and Selling Cards',
                'image_path' => null,
                'excerpt' => 'Navigate the secondary market with confidence.',
                'body' => "## Trading Card Market Guide

The Pokémon card market is dynamic, with prices fluctuating based on demand, condition, and availability.

**Popular Marketplaces:**
- TCGPlayer
- eBay
- Local card shops
- Facebook groups

**Price Factors:**
- Card rarity and condition
- Current meta relevance
- Nostalgia and collectibility
- Recent tournament results

Always research recent sales before buying or selling to ensure fair prices.",
                'external_url' => 'https://www.tcgplayer.com/',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(15),
                'sort_order' => 4,
            ],
        ];
    }

    private function getMtgArticles(int $gameId): array
    {
        return [
            [
                'game_id' => $gameId,
                'category' => 'Getting Started',
                'title' => 'Introduction to Magic: The Gathering',
                'image_path' => null,
                'excerpt' => 'Begin your journey into the multiverse of Magic.',
                'body' => "## Welcome to Magic: The Gathering

Magic: The Gathering (MTG) is the original trading card game, created in 1993. Players take on the role of planeswalkers battling across the multiverse.

**Core Concepts:**
- Start with 20 life points
- Use mana from lands to cast spells
- Reduce opponent's life to zero to win

**Five Colors of Magic:**
- **White**: Order and protection
- **Blue**: Knowledge and control
- **Black**: Power and death
- **Red**: Chaos and destruction
- **Green**: Nature and growth

Learn more at the [official MTG website](https://magic.wizards.com/).",
                'external_url' => 'https://magic.wizards.com/en/intro',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(28),
                'sort_order' => 1,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Rules',
                'title' => 'Understanding the Stack',
                'image_path' => null,
                'excerpt' => 'Master one of Magic\'s most important concepts.',
                'body' => "## The Stack Explained

The stack is Magic's system for resolving spells and abilities in last-in, first-out order.

**How It Works:**
- Spells and abilities go on the stack
- Players can respond with instant-speed effects
- Stack resolves from top to bottom

**Example:**
- Player A casts Lightning Bolt
- Player B responds with Counterspell
- Counterspell resolves first, countering the Bolt

Understanding the stack is crucial for competitive play and strategic decision-making.",
                'external_url' => null,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(22),
                'sort_order' => 2,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Collecting',
                'title' => 'Reserved List Cards',
                'image_path' => null,
                'excerpt' => 'Learn about Magic\'s most valuable and collectible cards.',
                'body' => "## The Reserved List

In 1996, Wizards of the Coast created the Reserved List, promising never to reprint certain powerful cards.

**Key Facts:**
- Contains over 500 cards
- Includes iconic cards like **Black Lotus** and **Dual Lands**
- Will never be reprinted in any form
- Prices continue to increase over time

**Investment Considerations:**
- Reserved List cards are scarce and highly sought after
- Condition is crucial for value
- Authentication is important for high-value purchases

Research thoroughly before investing in Reserved List cards.",
                'external_url' => null,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(18),
                'sort_order' => 3,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Formats',
                'title' => 'Choosing Your Format',
                'image_path' => null,
                'excerpt' => 'Explore different ways to play Magic.',
                'body' => "## MTG Formats Overview

Magic offers various formats, each with different card pools and banned lists.

**Popular Formats:**
- **Standard**: Latest sets, rotating format
- **Modern**: Cards from 8th Edition forward
- **Commander**: Multiplayer format with 100-card singleton decks
- **Limited**: Sealed and Draft using unopened packs

**Format Selection:**
- Standard is great for beginners
- Commander is casual and social
- Modern offers deep strategy

Choose a format that matches your playstyle and budget.",
                'external_url' => 'https://magic.wizards.com/en/formats',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(12),
                'sort_order' => 4,
            ],
        ];
    }

    private function getYugiohArticles(int $gameId): array
    {
        return [
            [
                'game_id' => $gameId,
                'category' => 'Getting Started',
                'title' => 'Yu-Gi-Oh! TCG Basics',
                'image_path' => null,
                'excerpt' => 'It\'s time to duel! Learn the fundamentals of Yu-Gi-Oh!',
                'body' => "## Getting Started with Yu-Gi-Oh!

Yu-Gi-Oh! is a fast-paced trading card game where players summon monsters and cast spells to reduce their opponent's Life Points to zero.

**Game Setup:**
- Each player starts with 8000 Life Points
- Draw 5 cards to start
- Main Deck of 40-60 cards

**Card Types:**
- **Monster Cards**: Attack and defend
- **Spell Cards**: Various effects
- **Trap Cards**: Surprise effects

Visit [YuGiOh! Card Database](https://www.db.yugioh-card.com/) for official rulings.",
                'external_url' => 'https://www.yugioh-card.com/en/rulebook/',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(26),
                'sort_order' => 1,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Rules',
                'title' => 'The Extra Deck Explained',
                'image_path' => null,
                'excerpt' => 'Master Fusion, Synchro, Xyz, and Link summoning.',
                'body' => "## Understanding the Extra Deck

The Extra Deck holds powerful monsters summoned through special mechanics.

**Extra Deck Monster Types:**
- **Fusion Monsters**: Combine specific monsters
- **Synchro Monsters**: Tuner + non-Tuner(s)
- **Xyz Monsters**: Overlay monsters of same level
- **Link Monsters**: Use Link materials

**Deck Composition:**
- Maximum 15 cards in Extra Deck
- Build strategy around summoning mechanics
- Consider ratios carefully

Each mechanic offers unique strategic advantages.",
                'external_url' => null,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(20),
                'sort_order' => 2,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Collecting',
                'title' => 'Card Editions and Prints',
                'image_path' => null,
                'excerpt' => 'Understand different card editions and their value.',
                'body' => "## Yu-Gi-Oh! Card Editions

Yu-Gi-Oh! cards come in multiple editions, affecting collectibility and value.

**Edition Types:**
- **1st Edition**: First print run, most valuable
- **Unlimited**: Subsequent printings
- **Limited Edition**: Special promotional cards

**Rarity Levels:**
- Common
- Rare
- Super Rare
- Ultra Rare
- Secret Rare
- Ghost Rare (discontinued)
- Starlight Rare (newest, extremely rare)

**1st Edition cards** typically command premium prices, especially for meta-relevant or nostalgic cards.",
                'external_url' => null,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(16),
                'sort_order' => 3,
            ],
            [
                'game_id' => $gameId,
                'category' => 'Safety',
                'title' => 'Avoiding Counterfeit Cards',
                'image_path' => null,
                'excerpt' => 'Protect yourself from fake cards in the secondary market.',
                'body' => "## Spotting Fake Yu-Gi-Oh! Cards

With high-value cards reaching thousands of dollars, counterfeits are a real concern.

**Authentication Tips:**
- Check card texture and finish
- Verify font consistency
- Compare to known authentic cards
- Use a jeweler's loupe for details

**Red Flags:**
- Price too good to be true
- Poor print quality
- Incorrect holographic pattern
- Wrong card thickness

**Safe Buying Practices:**
- Buy from reputable sellers
- Request clear photos
- Use PayPal for buyer protection
- Consider professional authentication for expensive cards

Always be cautious when purchasing high-value cards online.",
                'external_url' => null,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(10),
                'sort_order' => 4,
            ],
        ];
    }
}

