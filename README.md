# 🃏 Basecard - Trading Card Collection Manager

<p align="center">
  <img src="public/images/logo_basecard.svg" width="200" alt="Basecard Logo">
</p>

<p align="center">
  <strong>Platform per collezionisti di carte da gioco (Pokemon TCG, Magic: The Gathering, Yu-Gi-Oh!)</strong>
</p>

---

## 📖 Overview

**Basecard** è una piattaforma web completa per gestire collezioni di carte da gioco, monitorare prezzi, costruire mazzi e valutare il valore della propria collezione nel tempo.

### 🎯 Core Features
- 📦 **Collection Management**: Traccia tutte le tue carte con quantità e varianti
- 💰 **Price Tracking**: Prezzi aggiornati da TCGCSV (USA) e Cardmarket (EU)
- 🎴 **Deck Builder**: Crea e valuta mazzi con statistiche dettagliate
- 📊 **Analytics**: Monitora l'andamento del valore della tua collezione
- 🎮 **Multi-Game**: Supporto per Pokemon, MTG e Yu-Gi-Oh!
- 🌍 **Multi-Language**: Interfaccia in Inglese, Italiano e Danese
- 💳 **Subscription Plans**: Tier Basic, Advanced e Premium

---

## 🛠️ Tech Stack

- **Framework**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
- **Database**: MySQL 8.0+
- **Email**: Brevo SMTP
- **Payments**: Stripe (Subscriptions + One-time)
- **APIs**: TCGCSV, Cardmarket, RapidAPI

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL 8.0+

### Installation

1. **Clone repository**
```bash
git clone https://github.com/cyberfactoryIT/pokebase.git
cd pokebase
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure `.env`**
```env
APP_URL=http://localhost
DB_DATABASE=basecard
DB_USERNAME=root
DB_PASSWORD=

ORGANIZATIONS_ENABLED=false
DEFAULT_GAME_ID=1

STRIPE_KEY=your_stripe_key
BREVO_API_KEY=your_brevo_key
```

5. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

6. **Import card data**
```bash
php artisan tcgcsv:import --game=pokemon --only=all
```

7. **Build assets**
```bash
npm run build
```

8. **Start server**
```bash
php artisan serve
```

Visit `http://localhost:8000`

---

## 📚 Documentation

- **[PROJECT_STATUS.md](PROJECT_STATUS.md)** - Status completo applicazione, architecture, features
- **[OPERATIONS.md](OPERATIONS.md)** - Comandi operativi e deployment
- **[ROADMAP.md](ROADMAP.md)** - Sviluppi futuri pianificati
- **[DEPRECATION.md](DEPRECATION.md)** - Features e codice deprecato

---

## 🔧 Common Operations

### Deploy to Production
```bash
./deploy.sh
```

### Import Card Data
```bash
# Pokemon
php artisan tcgcsv:import --game=pokemon --only=all

# Magic: The Gathering
php artisan tcgcsv:import --game=mtg --only=all

# Yu-Gi-Oh
php artisan tcgcsv:import --game=yugioh --only=all
```

### Create Admin User
```bash
php artisan make:superadmin
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

---

## 🌍 Multi-Language Support

Supported languages:
- 🇬🇧 English (EN)
- 🇮🇹 Italiano (IT)
- 🇩🇰 Dansk (DA)

Translation files in `resources/lang/{locale}/`

---

## 💳 Subscription Tiers

| Tier | Price | Features |
|------|-------|----------|
| **Basic** | Free | Limited deck creation, no price visibility |
| **Advanced** | €9.99/mo | Full price access, unlimited decks |
| **Premium** | €19.99/mo | All Advanced + analytics, priority support |

**Deck Evaluation**: €9.99 one-time (365 days price access)

---

## 🎮 Supported Games

- 🔴 **Pokemon TCG** (Primary)
- 🔵 **Magic: The Gathering**
- 🟡 **Yu-Gi-Oh!**

Users can switch between games via navbar dropdown.

---

## 🏗️ Project Structure

```
pokebase/
├── app/
│   ├── Console/Commands/     # Artisan commands
│   ├── Http/Controllers/     # Controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic
│   └── Policies/            # Authorization
├── config/                  # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── public/                  # Public assets
├── resources/
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript
│   ├── lang/               # Translations
│   └── views/              # Blade templates
├── routes/
│   ├── web.php             # Web routes
│   └── auth.php            # Auth routes
├── storage/                 # Logs, cache, uploads
└── tests/                   # PHPUnit tests
```

---

## 🔒 Security

- Email verification required
- Optional 2FA (Two-Factor Authentication)
- CSRF protection
- Password reset via email
- Stripe secure payments

---

## 🐛 Common Issues

### "Class not found"
```bash
composer dump-autoload
php artisan clear-compiled
```

### Routes not working
```bash
php artisan route:clear
php artisan route:cache
```

### Session issues
```bash
php artisan session:clear
php artisan cache:clear
```

---

## 📝 Environment Variables

### Critical Settings
```env
ORGANIZATIONS_ENABLED=false  # DO NOT CHANGE
DEFAULT_GAME_ID=1           # 1=Pokemon, 2=MTG, 3=YGO
```

### Email (Brevo)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email
MAIL_PASSWORD=your_brevo_key
MAIL_FROM_ADDRESS=noreply@basecard.dk
```

### Payments (Stripe)
```env
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

### APIs
```env
CARDMARKET_APP_TOKEN=your_token
RAPIDAPI_KEY=your_key
```

---

## 👥 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

---

## 📄 License

This project is proprietary software owned by Basios ApS.

---

## 🔗 Links

- **Production**: [https://basecard.dk](https://basecard.dk)
- **Support**: support@basecard.dk
- **Documentation**: See `PROJECT_STATUS.md`

---

## 👨‍💻 Development

### Watch for changes
```bash
npm run dev
```

### Debug mode
```bash
# In .env
APP_DEBUG=true
DB_LOG_QUERIES=true
```

### Queue worker
```bash
php artisan queue:work
```

---

## 🎨 Design System

- **Theme**: Dark mode (black #000, card bg #161615)
- **Primary Color**: Blue gradient
- **Font**: System fonts
- **Logo**: `/public/images/logo_basecard.svg`

---

## 📊 Database

### Main Tables
- `users` - Users with default game
- `games` - Pokemon, MTG, Yu-Gi-Oh!
- `tcgcsv_products` - Card catalog
- `tcgcsv_prices` - USA pricing
- `cardmarket_prices` - EU pricing
- `user_collection` - User cards
- `decks` - User decks
- `subscriptions` - Active subscriptions

---

## ⚡ Performance

- Route caching enabled in production
- View caching enabled
- Database indexes on foreign keys
- Lazy loading for images
- CDN for static assets (planned)

---

## 📱 Mobile

- Fully responsive design
- Touch-friendly UI
- PWA support (planned)

---

Made with ❤️ by Basios ApS
