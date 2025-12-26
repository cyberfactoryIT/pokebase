# 🚀 Multi-Game System - Deployment Guide

## Overview
Il sistema multi-gioco supporta Pokemon TCG, Magic: The Gathering e Yu-Gi-Oh! con completa separazione dei dati e scoping automatico.

## 📋 Pre-requisiti
- PHP 8.2+
- Composer
- MySQL 8.0+
- Git
- Accesso SSH al server di produzione

## 🎯 Deployment Automatico

### 1. Sul server di produzione, esegui:
```bash
cd /path/to/pokebase
./deploy.sh
```

Lo script farà automaticamente:
- ✅ Backup del database
- ✅ Pull del codice da Git
- ✅ Installazione dipendenze
- ✅ Esecuzione migrations
- ✅ Seeding dei giochi
- ✅ Rebuild cache
- ✅ Verifica deployment

### 2. Importa i dati TCGCSV

**Pokemon (obbligatorio):**
```bash
php artisan tcgcsv:import --game=pokemon --only=all
```

**Magic: The Gathering (opzionale):**
```bash
php artisan tcgcsv:import --game=mtg --only=all
```

**Yu-Gi-Oh (opzionale):**
```bash
php artisan tcgcsv:import --game=yugioh --only=all
```

⚠️ **Nota:** L'import completo può richiedere 10-30 minuti per gioco.

### 3. Associa utenti esistenti ai giochi

Se hai già utenti registrati, associali al gioco Pokemon:
```bash
php artisan tinker
```
```php
DB::table('game_user')->insert(
    User::all()->map(fn($u) => [
        'user_id' => $u->id, 
        'game_id' => 1, 
        'is_enabled' => true
    ])->toArray()
);
exit
```

## 🔄 Deployment Manuale

Se preferisci il controllo manuale:

```bash
# 1. Backup
mysqldump -u root pokebase > backup_$(date +%Y%m%d).sql

# 2. Pull codice
git pull origin main

# 3. Dipendenze
composer install --no-dev --optimize-autoloader

# 4. Migrations
php artisan migrate --force

# 5. Seed giochi
php artisan db:seed --class=GamesSeeder --force

# 6. Cache
php artisan config:clear && php artisan cache:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache

# 7. Import dati
php artisan tcgcsv:import --game=pokemon --only=all
```

## 📊 Database Structure

### Nuove tabelle:
- `games` - Definizione dei giochi supportati
- `game_user` - Pivot per associare utenti ai giochi

### Colonne aggiunte:
- `tcgcsv_groups.game_id`
- `tcgcsv_products.game_id`
- `tcgcsv_prices.game_id`

## 🎮 Configurazione Giochi

I giochi sono configurati in `database/seeders/GamesSeeder.php`:

| Game | Code | TCGCSV Category | Status |
|------|------|-----------------|--------|
| Pokémon TCG | `pokemon` | 3 | ✅ Active |
| Magic: The Gathering | `mtg` | 1 | ✅ Active |
| Yu-Gi-Oh! | `yugioh` | 2 | ✅ Active |

## 🔧 Comandi Utili

**Verifica stato import:**
```bash
php artisan tinker
```
```php
// Conta groups per gioco
DB::table('tcgcsv_groups')->selectRaw('game_id, COUNT(*) as count')->groupBy('game_id')->get();

// Conta products per gioco
DB::table('tcgcsv_products')->selectRaw('game_id, COUNT(*) as count')->groupBy('game_id')->get();

// Conta prices per gioco
DB::table('tcgcsv_prices')->selectRaw('game_id, COUNT(*) as count')->groupBy('game_id')->get();
```

**Ri-importa singolo group:**
```bash
php artisan tcgcsv:import --game=pokemon --groupId=1
```

**Pulisci e ri-importa:**
```bash
# ATTENZIONE: Cancella tutti i dati TCGCSV!
php artisan tinker
```
```php
DB::statement('SET FOREIGN_KEY_CHECKS=0');
DB::table('tcgcsv_prices')->truncate();
DB::table('tcgcsv_products')->truncate();
DB::table('tcgcsv_groups')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');
exit
```

## 🐛 Troubleshooting

### Import fallito
```bash
# Verifica log
tail -100 storage/logs/laravel.log

# Verifica connessione API
curl https://tcgcsv.com/3/groups
```

### Game_id NULL nei record
```bash
# Verifica che game_id sia in fillable
grep -n "fillable" app/Models/Tcgcsv*.php

# Ri-importa dopo fix
php artisan tcgcsv:import --game=pokemon --only=groups
```

### Utenti senza giochi
```bash
# Associa tutti al Pokemon
php artisan tinker
>>> User::all()->each(fn($u) => $u->games()->syncWithoutDetaching([1 => ['is_enabled' => true]]));
```

## 📝 Rollback

Se qualcosa va storto:

```bash
# Restore database
mysql -u root pokebase < backup_YYYYMMDD.sql

# Rollback migrations
php artisan migrate:rollback --step=5

# Reset code
git reset --hard HEAD~1

# Clear cache
php artisan cache:clear
```

## ✅ Verifica Post-Deployment

1. Vai su `https://tuosito.com/dashboard`
2. Verifica dropdown giochi in alto a destra
3. Cambia gioco e verifica che i dati cambino
4. Controlla che le collezioni utente siano filtrate
5. Verifica che la navigazione sia sempre scoped sul gioco corrente

## 📞 Support

Per problemi o domande:
- Controlla logs: `storage/logs/laravel.log`
- Verifica database: `php artisan tinker`
- Review migrations: `database/migrations/2025_12_26_*.php`
