# Template Cleanup Report — pokebase

Generated: 2025-12-06

Scopo
-----
Questo report elenca i residui del template e i placeholder trovati nel repository `pokebase`. Include file, linee rilevate e suggerimenti pratici per la pulizia e la personalizzazione.

Sintesi
-------
- Il progetto deriva dallo skeleton Laravel (file `composer.json` con `name: laravel/laravel` e README predefinito).
- Alcuni valori sono già personalizzati (es. `APP_NAME=Pokebase`), ma molti placeholder rimangono (indirizzi email di esempio, testi dummy, file README generico, test di esempio).

Principali file con marker
--------------------------

1. `.env.example` (`/Users/barbaramanighetti/site/pokebase/.env.example`)
   - MAIL_FROM_ADDRESS="hello@example.com"
   - COMPANY_INFO_VIRK_USER_ID=TUO_USER_ID
   - COMPANY_INFO_VIRK_PASSWORD=LA_TUA_PASSWORD
   - Note: APP_NAME è già settato a `Pokebase`.

2. `.env` (`/Users/barbaramanighetti/site/pokebase/.env`) — se presente contiene:
   - MAIL_FROM_ADDRESS="hello@example.com"
   - COMPANY_INFO_VIRK_USER_ID=TUO_USER_ID
   - COMPANY_INFO_VIRK_PASSWORD=LA_TUA_PASSWORD
   - Attenzione: il file `.env` non dovrebbe contenere credenziali sensibili in repository pubblico.

3. `README.md` (`/Users/barbaramanighetti/site/pokebase/README.md`)
   - Contenuto: README generico del framework Laravel. Va aggiornato con informazioni specifiche del progetto.

4. Tests
   - `tests/Feature/ExampleTest.php` — test di esempio (classe `ExampleTest`).
   - `tests/Unit/ExampleTest.php` — test di esempio.
   - Diversi test usano `test@example.com` come email fissa (es. `tests/Feature/ProfileTest.php`, `tests/Feature/Auth/RegistrationTest.php`, `tests/Feature/Auth/RegistrationCreatesOrganizationTest.php`).
   - `database/seeders/DatabaseSeeder.php` e `database/seeders/AdminUsersSeeder.php` contengono `test@example.com` e `superadmin@example.com`.

5. Language files
   - `resources/lang/*/messages.php` contiene chiavi come `'footer_dummy' => 'Dummy footer content: :privacy | :terms | :contact'` (anche versioni italiane/danesi).
   - `resources/lang/en/faq.php` contiene commenti `// Placeholders`.

6. `config/mail.php`
   - Default: `'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com')`

7. `public/.gitignore`
   - Intestazione: "## Template .gitignore file from MAMP PRO" — valutare se necessario.

8. `app/Http/Controllers/SupportController.php`
   - Usa `support@example.com` come fallback in `config('mail.support_address', 'support@example.com')`.

9. `composer.json`
   - `name` è `laravel/laravel` (skeleton). Valuta di aggiornare con il nome del pacchetto/progetto.

10. `public/build` and compiled assets
   - Contiene asset compilati (CSS/JS). Valuta se mantenerli nel repo.

11. `composer.lock`
   - Contiene autor email di alcuni pacchetti come `hello@gjcampbell.co.uk` — normale per lockfile.

Dettaglio occorrenze rilevate (estratto)
---------------------------------------
- `hello@example.com` — `.env`, `.env.example`, `config/mail.php`
- `test@example.com` — tests and seeders
- `superadmin@example.com` — `database/seeders/AdminUsersSeeder.php`
- `TUO_USER_ID`, `LA_TUA_PASSWORD` — `.env.example` (commenti in italiano)
- `footer_dummy` keys — `resources/lang/*/messages.php`
- `ExampleTest` classes — `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`
- README generico — `README.md`

Rischi e priorità
------------------
- Priorità alta: rimuovere o non committare credenziali reali da `.env` e rivedere `.env.example` per non lasciare valori sensibili nel repo.
- Priorità media: aggiornare README, seeders e test per evitare dati di esempio hard-coded.
- Priorità bassa: rimuovere intestazioni template nei `.gitignore` o aggiornare se non rilevanti.

Azioni consigliate e comandi utili
---------------------------------
1. Rimuovere credenziali da `.env` (se presente nel repo) e assicurarsi che `.gitignore` includa `.env`:

```bash
# Rimuovi dal repo se committato accidentalmente
git rm --cached .env
# Aggiungi .env a .gitignore se non presente
printf '\n.env\n' >> .gitignore
git add .gitignore
```

2. Aggiornare `.env.example` rimuovendo email di esempio, o documentando chiaramente i valori da mettere.
3. Aggiornare `README.md` con setup del progetto e comandi utili (composer install, npm install, php artisan migrate --seed).
4. Sostituire `test@example.com`/`superadmin@example.com` nei seeders/tests con dati generati dinamicamente (Faker) o definiti via constant/test helper.

Esempio rapido per seeders/tests:
- Usa `
$faker->unique()->safeEmail` in seeders.
- Nei test, crea l'utente con `User::factory()->create(['email' => 'user@example.test']);` o simile.

Ulteriori suggerimenti
----------------------
- Considera l'aggiunta di `CONTRIBUTING.md` e `SECURITY.md` se il progetto è pubblico.
- Rivedi la policy di commit per non includere asset buildati (`public/build`) se vuoi mantenerli fuori dal repo.

Prossimi passi che posso fare per te
-----------------------------------
- Generare una `README.md` iniziale personalizzata (bozza) per `Pokebase`.
- Creare un patch che: rimuova `ExampleTest` e aggiorni `.env.example` (sostituendo le email di esempio con placeholder), e aggiorni `database/seeders` per usare faker.
- Produrre un report CSV o JSON dettagliato di tutte le occorrenze (file:line:snippet).

Se vuoi che applichi modifiche automatiche, dimmi quale delle azioni sopra eseguire (ad esempio: "Crea README bozza" o "Applica patch: rimuovi ExampleTest e aggiornare .env.example").

---
Report generato automaticamente. Se vuoi l'output in un diverso formato (JSON/CSV) o vuoi che applichi direttamente alcune modifiche, dimmi e procedo.
