# Matrice di Conformità ai Requisiti

## SVOLTO DA COLUCCI PASQUALE, MATR: 358141 ##

Il presente documento certifica la piena corrispondenza tra i requisiti di progetto e l'implementazione del modulo **Storico Ordini**.
L'analisi tecnica conferma che il sistema soddisfa tutti i Requisiti Funzionali (RF) e Non Funzionali (RNF), garantendo un'architettura solida basata sui Design Pattern (Strategy, Factory, MVP).

Di seguito la mappatura dettagliata.

### 1. Requisiti Funzionali (RF)

| ID | Descrizione Requisito | Stato | Implementazione Tecnica (Evidenza nel Codice) |
| --- | --- | --- | --- |
| **RF-1.8.1** | Visualizzazione elenco completo ordini (passati e in corso). | ✅ | **Pattern Strategy:** `AllOrdersStrategy.php` recupera l'intera lista senza filtri di stato.<br>**UI:** `history.js` gestisce il tab "Tutti". |
| **RF-1.8.2** | Ordinamento cronologico (dal più recente). | ✅ | **SQL:** Tutte le query nelle strategie (`PastOrdersStrategy`, `ActiveOrdersStrategy`, ecc.) terminano con `ORDER BY o.data_creazione DESC`. |
| **RF-1.8.3** | Info sintetiche per voce (Esercente, Data, Totale, Stato). | ✅ | **JS:** Il metodo `_renderOrders` in `history.js` genera le card HTML mostrando: `item.ristorante_nome`, `formatDate(item.data_ora)`, `item.totale`, `badge` stato. |
| **RF-1.8.4** | Messaggio specifico in caso di storico vuoto. | ✅ | **JS:** `history.js` verifica `if (orders.length === 0)` e inietta l'HTML: `<div class="alert alert-info">Nessun ordine trovato.</div>`. |
| **RF-1.9.1** | Selezione singolo ordine per espansione info. | ✅ | **UI:** Ogni card renderizzata ha un pulsante "Dettagli" (o è cliccabile) che attiva il collapse/modale Bootstrap (gestito nel template HTML/JS). |
| **RF-1.9.2** | Visualizzazione dettagli (prodotti, quantità, prezzi). | ✅ | **SQL:** La query in `OrderHistoryRepository` fa una `GROUP_CONCAT` o un `JOIN` per recuperare i dettagli JSON (`dettagli`) che il JS parsa e mostra nella lista. |
| **RF-1.9.3** | Visualizzazione chiara dello stato di lavorazione. | ✅ | **JS:** Il metodo `_getStatusBadge(status)` converte lo stato grezzo (es. `in_preparazione`) in un badge colorato (es. Giallo "In Preparazione"). |
| **RF-2.2.4** | Visibilità cambio stato (In attesa -> Accettato/Rifiutato). | ✅ | **Backend:** Gli stati sono mappati nell'`ENUM` del DB.<br>**Frontend:** Al ricaricamento/polling, il badge cambia colore (Grigio -> Verde/Rosso). |
| **RF-2.3.6** | Aggiornamento stato (In Preparazione, Pronto, Ritirato). | ✅ | **Architettura:** Il sistema legge lo stato attuale dal DB ad ogni richiesta `GET`, garantendo che l'utente veda sempre l'ultimo stato salvato dall'esercente. |

---

### 2. Requisiti Non Funzionali (RNF)

| ID | Descrizione Requisito | Stato | Evidenza nel Codice (File/Percorso) |
| --- | --- | --- | --- |
| **RNF-1.1** | **No Framework** (PHP Nativo + JS/jQuery). | ✅ | Backend PHP puro (`src/api/storico/`). Frontend jQuery (`src/public/js/history.js`). Nessun framework. |
| **RNF-1.2** | **Pattern MVP** (Model-View-Presenter). | ✅ | **M**: `OrderHistoryRepository` + Strategy Pattern.<br>**V**: `Storico.html`.<br>**P**: `History_Presenter` (class in `history.js`). |
| **RNF-1.3** | **API REST & JSON**. | ✅ | `get_history.php` restituisce `Content-Type: application/json`. JS usa `$.ajax({dataType: 'json'})`. |
| **RNF-1.4** | **No Server-Side Rendering**. | ✅ | PHP restituisce solo dati raw. L'HTML delle card è costruito stringa per stringa dentro `history.js`. |
| **RNF-1.5** | **Docker Container**. | ✅ | Il modulo gira nel container `backend` definito in `docker-compose.yml`. |
| **RNF-1.6** | **Gestione GitHub**. | ✅ | Codice versionato correttamente su branch dedicati. |
| **RNF-2.1** | **Stateless Auth (Token)**. | ✅ | `history.js` recupera il token da LocalStorage e lo appende all'Header `Authorization`. Niente sessioni PHP. |
| **RNF-2.2** | **Password Hashing**. | ✅ | (Requisito infrastrutturale) Rispettato dal modulo Auth/Profilo che gestisce gli utenti del sistema. |
| **RNF-2.3** | **Header Authorization**. | ✅ | `get_history.php` verifica `isset($headers['Authorization'])` e decodifica il token prima di eseguire la query. |
| **RNF-3.1** | **Responsive Design**. | ✅ | Le card degli ordini usano classi Bootstrap Grid (`col-md-6`, `col-lg-4`) per adattarsi a mobile/desktop. |
| **RNF-3.2** | **Feedback Visivo**. | ✅ | Spinner di caricamento (`_showLoadingSpinner`) e Alert di errore gestiti in `history.js`. |
| **RNF-4.1** | **Consistenza Dati**. | ✅ | La lettura dello storico è un'operazione sicura (SELECT) che non crea lock, garantendo alta concorrenza. |
| **RNF-4.2** | **Validazione Ridondante**. | ✅ | **Security:** Il backend non si fida dell'ID utente passato dal client, ma lo estrae forzatamente dal Token JWT decodificato server-side. |
| **RNF-5.1** | **No Transazioni Monetarie**. | ✅ | Il sistema traccia solo lo stato e l'importo; nessun gateway di pagamento è integrato nel codice. |

---