# Documentazione Modulo: Storico Ordini

*SVOLTO DA COLUCCI PASQUALE, MATR: 358141*

## 1. Introduzione e Scopo

Il modulo **Storico Ordini** è il componente del sistema responsabile del recupero, del filtraggio e della visualizzazione degli ordini effettuati dall'utente.
Questo modulo è fondamentale per l'esperienza utente (UX), in quanto permette al cliente di monitorare il ciclo di vita di un ordine in tempo reale e di consultare le spese passate.

Il modulo è progettato per gestire due macro-categorie di visualizzazione:

1. **Ordini Attivi**: Ordini in corso di lavorazione (es. "In preparazione", "Pronto").
2. **Ordini Passati**: Ordini conclusi o archiviati (es. "Ritirato", "Rifiutato").

L'architettura backend fa un uso intensivo di **Design Patterns comportamentali** (Strategy e Factory) per garantire che la logica di filtraggio sia estendibile senza modificare il codice client.

---

## 2. Architettura del Modulo

Il modulo aderisce al pattern **Model-View-Presenter (MVP)** distribuito su container Docker.

### 2.1 Mappatura dei Componenti

| Componente | Ruolo (MVP) | File / Artefatto | Descrizione Tecnica |
| --- | --- | --- | --- |
| **View** | Interfaccia Passiva | `src/public/Storico.html` | Pagina HTML che ospita la tabella degli ordini e i bottoni di filtro. |
| **Presenter** | Logica di Presentazione | `src/public/js/history.js` | Script JavaScript. Gestisce le chiamate AJAX, manipola il DOM per il rendering della tabella e gestisce la logica dei badge di stato (colori). |
| **Controller** | API Endpoint | `src/api/storico/get_history.php` | Script PHP procedurale. Riceve la richiesta HTTP, estrae il token e invoca la Factory. |
| **Model** | Business Logic | `OrderHistoryRepository.php`, `OrderStrategyFactory.php` | Insieme di classi che implementano i pattern **Factory Method** e **Strategy** per l'incapsulamento delle query. |

---

## 3. Gestione dei Dati (Database)

Il modulo esegue operazioni di lettura (READ) complesse che coinvolgono diverse tabelle relazionali per costruire un oggetto "Ordine" completo di dettagli.

### 3.1 Schema Relazionale Coinvolto

Le query eseguono `JOIN` tra le seguenti entità:

1. **`ORDINE` (Tabella Principale)**
* Contiene i dati essenziali: `id_ordine`, `data_ora`, `totale`, `stato`.
* Funge da pivot per le relazioni.


2. **`ESERCENTE`**
* Collegata via `id_esercente`.
* Utilizzata per recuperare la `ragione_sociale` del ristorante (fondamentale per l'utente).


3. **`RIGA_ORDINE` e `PRODOTTO**`
* Tabelle di dettaglio.
* Vengono interrogate (spesso usando `GROUP_CONCAT`) per generare una stringa riassuntiva del contenuto dell'ordine (es. "Pizza Margherita (x2), Coca Cola (x1)").



---

## 4. Flussi Funzionali e Logica Applicativa

### 4.1 Recupero e Filtraggio Ordini (Flow di Lettura)

Il flusso applicativo è guidato dalla selezione del filtro nell'interfaccia utente.

1. **Richiesta (Frontend)**: L'utente clicca un filtro (es. "In Corso"). Il Presenter invoca l'API: `GET get_history.php?filter=active`.
2. **Routing della Strategia (Backend - Factory Pattern)**:
* Il Controller riceve la stringa `active`.
* Invoca `OrderStrategyFactory::getStrategy('active')`.
* La Factory istanzia e restituisce l'oggetto `ActiveOrdersStrategy`.


3. **Esecuzione (Backend - Strategy Pattern)**:
* Il Repository riceve l'oggetto Strategy (senza conoscerne la classe concreta).
* Invoca il metodo polimorfico `fetch($conn, $userId)`.
* La strategia esegue la query SQL specifica (es. `WHERE stato IN ('attesa', 'preparazione', ...)`).


4. **Rendering (Frontend)**:
* Il JSON viene restituito al client.
* `history.js` itera sui risultati e genera dinamicamente l'HTML, applicando le classi CSS corrette per i badge di stato (Verde, Giallo, Rosso) definiti nel file `global.css`.



---

## 5. Specifiche API (Interfaccia Backend)

Il backend espone un endpoint RESTful flessibile.

### `GET /storico/get_history.php`

Recupera la lista degli ordini filtrata.

* **Headers**:
* `Authorization`: Bearer Token (JWT).


* **Query Params**:
* `filter` (string, opzionale):
* `all` (default): Restituisce tutto lo storico.
* `active`: Solo ordini aperti.
* `past`: Solo ordini conclusi.




* **Risposta (200 OK)**:

```json
[
  {
    "id": 102,
    "data_ora": "2026-02-06 12:30:00",
    "totale": "7.50",
    "stato": "preparazione",
    "ristorante_nome": "Mensa Parco Ducale",
    "dettagli": "Cotoletta alla Milanese (x1)"
  },
  {
    "id": 101,
    "data_ora": "2026-02-06 12:15:00",
    "totale": "10.50",
    "stato": "attesa",
    "ristorante_nome": "Mensa Parco Ducale",
    "dettagli": "Pasta al Pomodoro (x1), Lasagne (x1)"
  }
]

```

---

## 6. Misure di Sicurezza Implementate

1. **Validazione dell'Input (Factory)**:
* L'input `filter` non viene inserito direttamente nella query SQL.
* Viene passato a uno `switch` case nella Factory. Se il filtro non è riconosciuto, il sistema fa fallback su `AllOrdersStrategy`, prevenendo comportamenti imprevisti o Injection basate su parametri.


2. **Autenticazione Stateless**:
* L'accesso ai dati è protetto dal controllo del Token nell'header `Authorization`. Se manca, l'API risponde con `401 Unauthorized`.


3. **Prepared Statements**:
* Tutte le strategie utilizzano `PDO::prepare()` e binding dei parametri (`:userId`) per prevenire SQL Injection.

4. **Controllo degli Accessi e Sicurezza** Per garantire la riservatezza dei dati, è stato implementato un meccanismo di sicurezza a doppio livello basato sul ruolo (Role-Based Access Control):
* **Lato Backend:** L'endpoint API get_history.php integra ora un controllo preventivo di coerenza ("Guard Clause"). Prima di istanziare le strategie di recupero ordini, il sistema verifica nel Database se l'ID utente estratto dal Token JWT appartiene alla tabella ESERCENTE. In caso positivo, la richiesta viene immediatamente interrotta con codice HTTP 403 Forbidden. Questo previene vulnerabilità di tipo ID Spoofing, impedendo agli esercenti di accedere ai dati riservati ai clienti anche in caso di manipolazione del Token lato client.

* **Lato Frontend:** Il Presenter (history.js) effettua una validazione preliminare del payload JWT all'apertura della pagina; se viene rilevato il ruolo 'esercente', l'utente viene reindirizzato forzatamente alla propria Dashboard, impedendo la visualizzazione dell'interfaccia cliente.

---

## 7. Dettagli Implementativi e Testing

L'implementazione si distingue per l'uso di pattern avanzati per la gestione della complessità delle query.

### 7.1 Struttura del Codice (Patterns)

#### Backend (Model)

* **Factory Method** (`OrderStrategyFactory`): Centralizza la logica di creazione. Se in futuro si volesse aggiungere un filtro "Ordini Annullati", basterà aggiungere un case qui e una nuova classe Strategy, rispettando il principio *Open/Closed* (SOLID).
* **Strategy** (`IOrderFetchStrategy`): Interfaccia che definisce il contratto `fetch()`. Le classi concrete (`ActiveOrdersStrategy`, `PastOrdersStrategy`, `AllOrdersStrategy`) contengono le logiche SQL specifiche.
* **Context** (`OrderHistoryRepository`): Riceve la strategia ed esegue l'azione, disaccoppiando l'esecuzione dalla logica di selezione.

### 7.2 Strategia di Test

Come per il modulo Profilo, è stata adottata una strategia di testing duale.

#### A. White Box Testing (Automatizzato)

Script: **`src/api/storico/test_runner.php`**.
Questo script esegue test di integrazione backend verificando:

1. Il corretto instradamento della Factory.
2. La "purezza" dei filtri (es. verifica che la strategia `Active` non restituisca mai ordini con stato `ritirato`).
3. L'integrità della struttura dati JSON restituita.

#### B. Black Box Testing (System Testing)

Verifiche manuali su `Storico.html`:

* **Visual Check**: Coerenza dei colori dei badge (es. Rosso per 'Rifiutato', Verde per 'Pronto').
* **Navigation**: Funzionamento dei bottoni di filtro (Tutti / In Corso / Conclusi).
* **Empty State**: Verifica del messaggio di cortesia "Nessun ordine trovato" quando la lista è vuota.

---

### 7.3 Report dei Risultati

Per i dettagli completi sull'esecuzione dei test, consultare il file dedicato: [Test_Report.md](https://www.google.com/search?q=./Test_Report.md)