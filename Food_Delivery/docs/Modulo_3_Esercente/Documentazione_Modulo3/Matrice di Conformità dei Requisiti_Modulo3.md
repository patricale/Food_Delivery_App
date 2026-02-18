# Matrice di Conformità dei Requisiti - Modulo 3: Esercente

## 1. Introduzione
Il presente documento attesta la conformità del software sviluppato rispetto ai requisiti funzionali e non funzionali definiti nel documento di analisi per il Modulo 3 (Esercente). La mappatura indica dove ogni requisito è implementato nel codice sorgente.

---

## 2. Requisiti Funzionali

| ID | Requisito Funzionale | Stato | Evidenza Tecnica (File e Metodi) |
|:---|:---|:---:|:---|
| **RF-2.0.1** | Visualizzazione elenco prodotti nel menù | ✅ | `src/api/esercente/menu/Read.php`, `Esercente_Model.getMenu()`, `Esercente_View.renderMenu()` |
| **RF-2.0.2** | Modifica disponibilità prodotto (toggle) | ✅ | `src/api/esercente/menu/Toggle_Availability.php`, `Esercente_Presenter.onToggleProduct` |
| **RF-2.0.4** | Blocco CRUD prodotti se locale **APERTO** | ✅ | Backend: `Create.php`, `Delete.php` (Check SQL `stato_apertura`). Frontend: `Esercente_View.updateHeaderStatus` |
| **RF-2.0.5** | Validazione dati prodotto (prezzi, obbligatorietà) | ✅ | `src/api/esercente/menu/Create.php` (Server-side validation), `Esercente_Presenter.onAddProduct` |
| **RF-2.2.1** | Visualizzazione ordini "In attesa" con dettagli | ✅ | `src/api/esercente/ordini/Get_Orders.php`, `Esercente_View.renderOrders` (colonna `col-attesa`) |
| **RF-2.2.2** | Accettazione ordine (passaggio a 'preparazione') | ✅ | `src/api/esercente/ordini/strategies/Order_Strategies.php` (`AccettaStrategy`) |
| **RF-2.2.3** | Rifiuto ordine (passaggio a 'rifiutato') | ✅ | `src/api/esercente/ordini/strategies/Order_Strategies.php` (`RifiutaStrategy`) |
| **RF-2.2.4** | Visibilità immediata cambio stato lato Cliente | ✅ | Aggiornamento persistente su DB tramite `Update_Order_Status.php` |
| **RF-2.3.1** | Visualizzazione ordini attivi in lavorazione | ✅ | `Esercente_View.renderOrders` (colonne `col-preparazione` e `col-pronto`) |
| **RF-2.3.2** | Avanzamento stato: Accettato -> In Preparazione | ✅ | `Order_Strategies.php` (Logica integrata in `AccettaStrategy`) |
| **RF-2.3.3** | Avanzamento stato: In Preparazione -> Pronto | ✅ | `src/api/esercente/ordini/strategies/Order_Strategies.php` (`ProntoStrategy`) |
| **RF-2.3.4** | Generazione automatica codice ritiro (OTP) | ✅ | Gestito a livello DB (`init.sql`) e attivato da `ProntoStrategy` |
| **RF-2.3.5** | Verifica codice ritiro per stato "Ritirato" | ✅ | `Order_Strategies.php` (`RitiratoStrategy`), `Esercente_Presenter.onVerifyCode` |
| **RF-2.3.6** | Aggiornamento in tempo reale (Polling) | ✅ | `Esercente_Presenter.js` (Funzione `startPolling` ogni 5 secondi) |
| **RF-2.3.7** | Chiusura ordine come "Non Ritirato" | ✅ | `src/api/esercente/ordini/strategies/Order_Strategies.php` (`NonRitiratoStrategy`) |

---

## 3. Requisiti Non Funzionali

| ID | Requisito Non Funzionale | Stato | Evidenza Tecnica |
|:---|:---|:---:|:---|
| **RNF-1.1** | Tecnologie (PHP nativo, HTML/CSS/JS/jQuery) | ✅ | Struttura dei file `.php` e `.js` nella cartella `src/` |
| **RNF-1.2** | Pattern Model-View-Presenter (MVP) | ✅ | Separazione netta: `Esercente_Model.js`, `Esercente_View.js`, `Esercente_Presenter.js` |
| **RNF-1.3** | Comunicazione RESTful API JSON | ✅ | Headers `application/json` in `src/api/` e chiamate `fetch/ajax` nel Model |
| **RNF-1.4** | Divieto di Server-Side Rendering (SSR) | ✅ | `Esercente.html` è uno skeleton vuoto popolato da `Esercente_View.js` |
| **RNF-1.5** | Fornitura tramite container Docker | ✅ | Presente `docker-compose.yml` e `docker/backend/Dockerfile` |
| **RNF-1.6** | Gestione codice e Documentazione (GitHub) | ✅ | Repo GitHub e cartella `docs/Modulo_3_Esercente/` |
| **RNF-2.1** | Autenticazione Stateless (Token JWT) | ✅ | `src/api/utils/JwtUtils.php` e `localStorage.getItem('jwt_token')` |
| **RNF-2.3** | Protezione API tramite Token | ✅ | Chiamata a `Auth_Helper::authenticate()` in tutti i controller API |
| **RNF-3.1** | Interfaccia Responsive (Layout adattabile) | ✅ | Utilizzo di classi Bootstrap 5 (`row`, `col-md-4`, `d-flex`) in `Esercente.html` |
| **RNF-3.2** | Feedback visivo costante | ✅ | `Esercente_View.showSuccessModal()`, Spinner di caricamento e animazioni CSS |
| **RNF-4.2** | Validazione ridondante Backend | ✅ | Controllo permessi e tipi dato in `Strategy_Factory.php` e nei controller CRUD |

---

## 4. Conclusioni
Tutti i requisiti identificati nell'analisi per il Modulo 3 sono stati implementati e verificati. La conformità architetturale (MVP) e tecnologica (PHP/JS/Docker) è rispettata integralmente, come dimostrato dalla suite di test presente in `src/api/tests/esercente/`.