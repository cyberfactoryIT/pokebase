# Multi-Game System - Manual Testing Checklist

## Pre-requisiti
- [ ] Database migrated con `php artisan migrate`
- [ ] User con almeno 1 gioco attivo in `game_user`
- [ ] Dati TCGCSV importati con `game_id` popolato

---

## 1. Game Selection & Persistence
- [ ] **Login**: Alla prima visita, il sistema seleziona automaticamente il primo gioco attivo dell'utente
- [ ] **Dropdown visibile**: In navigation, vedo la dropdown con il nome del gioco corrente (es. "Pokémon TCG")
- [ ] **Switch game**: Click sulla dropdown → seleziono un altro gioco → la pagina ricarica e il nuovo gioco è selezionato
- [ ] **Persistenza**: Navigo tra pagine diverse (Dashboard → Expansions → Collection) → il gioco rimane lo stesso
- [ ] **Icon corretta**: Ogni gioco mostra l'icona/badge corretta nella dropdown

---

## 2. Dashboard Scoping
- [ ] **Statistiche corrette**: I numeri (cards, expansions, decks, collection) sono relativi solo al gioco selezionato
- [ ] **Switch game**: Cambio gioco → i numeri cambiano immediatamente
- [ ] **Nessun gioco attivo**: Se l'utente non ha giochi attivi → statistiche a 0 + CTA "Activate a game"

---

## 3. Expansions Page Scoping
- [ ] **Lista filtrata**: Vedo solo le espansioni del gioco corrente (es. solo set Pokemon se ho selezionato Pokemon)
- [ ] **Ricerca AJAX**: Cerco un'espansione → risultati solo dal gioco corrente
- [ ] **Nessuna contaminazione**: Non vedo MAI espansioni di altri giochi mescolate
- [ ] **Switch game**: Cambio gioco → la lista si aggiorna con le espansioni del nuovo gioco

---

## 4. Expansion Detail & Cards
- [ ] **Click su espansione**: Apro un'espansione → vedo solo le carte di QUELLA espansione E dello stesso gioco
- [ ] **Ricerca carte**: Cerco una carta nell'espansione → risultati filtrati per game_id
- [ ] **Card number sorting**: Le carte sono ordinate correttamente per numero progressivo
- [ ] **Cross-game protection**: Non posso accedere a un'espansione di un altro gioco tramite URL manuale

---

## 5. Collection Scoping
- [ ] **Collezione filtrata**: Vedo solo le carte della mia collezione relative al gioco corrente
- [ ] **Statistiche scoped**: Total cards, unique cards, foil cards, set completion → tutto filtrato per gioco
- [ ] **Top sets**: I "Top 5 sets" mostrano solo set del gioco corrente
- [ ] **Switch game**: Cambio gioco → la collezione cambia immediatamente

---

## 6. Decks Scoping  
- [ ] **Lista decks**: Vedo solo i mazzi relativi al gioco corrente
- [ ] **Creazione deck**: Creo un nuovo deck → viene associato automaticamente al gioco corrente
- [ ] **Aggiunta carte**: Aggiungo carte a un deck → solo carte del gioco corrente sono disponibili
- [ ] **Switch game**: Cambio gioco → i mazzi mostrati cambiano

---

## 7. Global Search
- [ ] **Ricerca carte**: La ricerca globale in navigation cerca solo nel gioco corrente
- [ ] **Risultati coerenti**: Click su un risultato → va alla carta del gioco corrente
- [ ] **Nessun cross-game**: Non vedo risultati di altri giochi

---

## 8. User Game Management
- [ ] **Profile page**: In `/profile` vedo la sezione "Active Games" con tutti i giochi disponibili
- [ ] **Enable/disable**: Posso attivare/disattivare giochi → salvataggio funziona
- [ ] **Dropdown update**: Se disattivo il gioco corrente → vengo automaticamente switchato a un altro gioco attivo
- [ ] **Nessun gioco attivo**: Se disattivo tutti i giochi → la dropdown mostra "Select Game" + CTA

---

## 9. Edge Cases
- [ ] **URL manipulation**: Provo ad accedere a `/tcg/expansions/123` di un altro gioco → ottengo 404 o redirect
- [ ] **Session timeout**: Logout/login → il gioco selezionato viene resettato al primo disponibile
- [ ] **No games**: Utente senza giochi attivi → vede CTA "Activate a game" ovunque
- [ ] **Performance**: Cambio gioco multiplo (3-4 volte) → nessun lag, query veloci

---

## 10. Data Integrity
- [ ] **game_id popolato**: Tutte le tabelle TCGCSV (groups, products, prices) hanno `game_id` NOT NULL
- [ ] **Nessun NULL orphans**: Query `SELECT * FROM tcgcsv_products WHERE game_id IS NULL` → 0 risultati
- [ ] **Backfill corretto**: Pokemon (cat 3) → game_id 1, MTG (cat 1) → game_id 2, Yu-Gi-Oh (cat 2) → game_id 3
- [ ] **game_user pivot**: Tutti gli utenti esistenti hanno almeno 1 gioco attivo (default Pokemon)

---

## ✅ Success Criteria
Tutti i test sopra devono passare. Il sistema non deve MAI:
- Mischiare dati di giochi diversi
- Mostrare espansioni/carte cross-game
- Perdere il contesto di gioco durante la navigazione
- Avere query slow (>500ms) per cambio gioco

---

## 🐛 Bug Reporting
Se trovi problemi, annota:
1. Gioco selezionato
2. Pagina/URL
3. Azione eseguita
4. Risultato atteso vs ottenuto
5. Query SQL se disponibile (check logs)
