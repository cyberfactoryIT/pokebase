# Scripts - Production Support Scripts

Questa cartella contiene script di supporto per la pipeline ETL.

**Nota**: Gli script di sync giornalieri sono nella root del progetto per facilitare l'esecuzione via cron.

## Script Disponibili

### ETL Pipeline

#### `run-pipeline-background.sh`
Esegue la pipeline ETL in background con logging.

```bash
./scripts/run-pipeline-background.sh

# Monitor log
tail -f storage/logs/etl-pipeline.log
```

#### `simulate-etl-pipeline.sh`
Pipeline ETL completa per sincronizzazione dati.

### Deploy

#### `deploy.sh`
Script di deploy per aggiornare l'applicazione su server di produzione.

```bash
./scripts/deploy.sh
```

## Script nella Root

Gli script eseguiti da cron sono nella root del progetto:
- `run-pipeline-background.sh` - ETL Pipeline
- `sync-lorcana-daily.sh` - Sync Lorcana
- `sync-cardmarket-daily.sh` - Sync CardMarket
- `sync-onepiece-daily.sh` - Sync One Piece (in pausa)

## Setup Cron

```bash
# ETL Pipeline - ogni giorno alle 2 AM
0 2 * * * cd /var/www/basecard.dk/app/ && ./run-pipeline-background.sh

# Lorcana sync - ogni giorno alle 3 AM
0 3 * * * cd /var/www/basecard.dk/app/ && ./sync-lorcana-daily.sh

# CardMarket sync - ogni giorno alle 4 AM
0 4 * * * cd /var/www/basecard.dk/app/ && ./sync-cardmarket-daily.sh
```

## Log

I log delle esecuzioni vengono salvati in `storage/logs/`.
