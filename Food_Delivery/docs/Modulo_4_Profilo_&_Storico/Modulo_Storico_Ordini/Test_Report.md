# Report di Test - Modulo Storico Ordini

*SVOLTO DA COLUCCI PASQUALE, MATR: 358141*

## 1. Strategia di Test
Per la validazione del modulo "Storico Ordini" sono state adottate due metodologie complementari, mirate a verificare sia la logica di business (Backend) che l'esperienza utente (Frontend):
* **White Box Testing (Automatizzato):** Verifica diretta delle classi PHP, del pattern *Strategy* e delle query SQL tramite script di test dedicato (`src/api/storico/test_runner.php`).
* **Black Box Testing (Manuale):** Verifica dell'interfaccia utente, del rendering dinamico delle tabelle, dei filtri e della corretta applicazione dei badge di stato.

## 2. Test Automatizzati (Backend)
Il componente `OrderHistoryRepository` e le implementazioni dell'interfaccia `IOrderFetchStrategy` (`ActiveOrdersStrategy`, `PastOrdersStrategy`, `AllOrdersStrategy`) sono stati sottoposti a unit testing automatico.

| ID Test | Descrizione | Risultato Atteso | Esito |
| :--- | :--- | :--- | :--- |
| **UNIT-01** | Istanza Repository | Connessione al Database e istanziazione del Repository con Dependency Injection riuscite. | ✅ PASS |
| **UNIT-02** | Fetch 'Tutti' (Strategy) | Restituzione di un array popolato contenente ordini con stati misti (Attivi e Passati). | ✅ PASS |
| **UNIT-03** | Logica Filtro 'Attivi' | L'array restituito **NON** deve contenere stati conclusivi (es. 'ritirato', 'rifiutato'). | ✅ PASS |
| **UNIT-04** | Logica Filtro 'Passati' | L'array restituito deve contenere **SOLO** stati conclusivi (es. 'ritirato', 'non_ritirato'). | ✅ PASS |
| **UNIT-05** | Struttura Dati JSON | Verifica integrità dei campi restituiti (presenza di `id`, `totale`, `ristorante_nome`, `dettagli`). | ✅ PASS |

## 3. Test Funzionali (Frontend/System)
Scenari eseguiti sull'interfaccia web (`Storico.html`) interagendo con i bottoni di filtro e verificando il comportamento del browser.

| ID Scenario | Azione Utente | Risultato Atteso | Esito | Note |
| :--- | :--- | :--- | :--- | :--- |
| **SYS-01** | Sicurezza Accesso (No Token) | Accesso diretto a `Storico.html` senza login (localStorage vuoto). | ⛔ REDIRECT | Il sistema reindirizza istantaneamente a `index.html`. |
| **SYS-02** | Caricamento Iniziale | Visualizzazione immediata dello spinner, seguito dalla lista di "Tutti" gli ordini. | ✅ PASS | Verifica corretta chiamata AJAX `onload`. |
| **SYS-03** | Filtro "In Corso" | La tabella si aggiorna mostrando solo badge **Gialli** (Attesa), **Arancioni** (Preparazione) o **Verdi** (Pronto). | ✅ PASS | Verifica UI dinamica e Strategy Backend. |
| **SYS-04** | Filtro "Conclusi" | La tabella mostra solo badge **Verdi** (Ritirato) o **Rossi** (Rifiutato/Non Ritirato). | ✅ PASS | Verifica corretta gestione stati finali. |
| **SYS-05** | Semantica Colori (CSS) | Verifica che lo stato 'Rifiutato' appaia con sfondo Rosso (`#e74c3c`) e 'Pronto' con sfondo Verde. | ✅ SUCCESS | Richiede configurazione corretta di `global.css`. |

## 4. Come Lanciare i Test
Per lanciare i test automatizzati e verificare l'output del backend:

1.  Avviare l'ambiente Docker:
    ```bash
    ./start.sh
    ```
2.  Aprire il browser o usare un tool come Postman/Curl all'indirizzo:
    `http://localhost:8001/tests/storico/test_runner.php`

L'output testuale mostrerà il log dettagliato dell'esecuzione passo-passo degli script PHP.