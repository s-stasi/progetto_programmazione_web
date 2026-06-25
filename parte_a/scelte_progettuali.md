# Lido Sole e Sabbia - Gestionale (Parte A)

**Corso di Programmazione Web - Università degli Studi di Bergamo** **Progetto #1: Sviluppo di un'applicazione web in PHP**

Questo repository contiene la "Parte A" del progetto d'esame, consistente in un'applicazione gestionale per uno stabilimento balneare. Il documento illustra lo stack tecnologico adottato e le estensioni apportate allo schema del database rispetto alle specifiche di base.

## Gruppo: Tre Uomini e Mezzo
* **Samuele Stasi** (Matricola: 1093316)
* **Julia Zambonelli** (Matricola: 1093775)
* **Claudio Morgera** (Matricola: 1093069)

## Tecnologie Utilizzate
Il progetto è stato sviluppato adottando un'architettura client-server tradizionale, senza l'ausilio di framework o librerie esterne:
* **Backend:** PHP.
* **Database:** MySQL per la persistenza e l'integrità relazionale dei dati.
* **Frontend:** HTML5, CSS3, e Vanilla JavaScript. JavaScript è stato ampiamente utilizzato per gestire la logica dei modali e per implementare chiamate asincrone, riducendo i ricaricamenti di pagina.
* **AJAX**: utilizzato per il caricamento e l'aggiornamento dei dati nella griglia della spiaggia.

## Modifiche e Aggiunte al Database
Per supportare le funzionalità avanzate del gestionale, lo schema relazionale originale è stato esteso introducendo nuovi campi all'interno delle tabelle esistenti.

### Tabella `Cliente`
Per arricchire l'anagrafica e permettere una gestione più completa dei contatti, sono stati inseriti i seguenti attributi:
* **`email`** (`VARCHAR`): Indirizzo di posta elettronica per le comunicazioni.
* **`telefono`** (`VARCHAR`): Recapito telefonico.
