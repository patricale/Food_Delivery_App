# Documentazione Tecnica - Modulo 3: Gestione Esercente ANDREA POCCETTI - 361127

## 1. Visione Generale del Modulo
Il **Modulo 3** costituisce l'interfaccia operativa per i partner commerciali (Ristoranti/Bar) del servizio *Food Delivery Campus*.
L'obiettivo è fornire agli esercenti uno strumento centralizzato per amministrare la propria attività, configurare l'offerta commerciale e gestire il flusso degli ordini in tempo reale.

Il sistema è progettato con un'architettura modulare che garantisce la separazione delle responsabilità tramite il pattern **Model-View-Presenter (MVP)**, separando nettamente la logica di visualizzazione (View), la gestione dei dati (Model) e la logica di controllo (Presenter).

## 2. Organizzazione dei Task
Il lavoro è suddiviso in task sequenziali raggruppati per aree funzionali, riflettendo l'effettiva implementazione del codice.

### 🟢 FASE 1: Infrastruttura Core

#### **Task 3.1: Architettura MVP e Routing**
* **Obiettivo:** Predisposizione dell'ambiente di lavoro SPA (Single Page Application).
* **Descrizione:** Implementazione dell'architettura Model-View-Presenter lato client. Il Presenter orchestra l'inizializzazione e gestisce la navigazione tra le sezioni alternando la visibilità dei container senza ricaricamenti di pagina, gestendo anche il ciclo di vita del polling.
* **Componenti:**
    * `Esercente.html`: Container principale.
    * `Esercente_View.js`: Gestione del DOM, template letterali HTML e binding degli eventi UI.
    * `Esercente_Model.js`: Gestione chiamate AJAX e comunicazione con le API.
    * `Esercente_Presenter.js`: Logica di business frontend, routing e coordinamento.

### 🟠 FASE 2: Gestione Business (Configurazione)

#### **Task 3.2: Gestione Stato Attività**
* **Obiettivo:** Controllo della disponibilità operativa.
* **Descrizione:** Implementazione del flusso per la modifica dello stato di apertura ("APERTO"/"CHIUSO"). L'evento UI viene catturato dalla funzione **`onShopToggle`** nel Presenter, che richiede al Model di aggiornare lo stato sul server e, in caso di successo, aggiorna l'indicatore visivo nell'header.
* **Output Tecnico:** API `Get_Status.php`, `Toggle_Shop_Status.php`, gestione eventi `onShopToggle`.

#### **Task 3.3: Gestione Catalogo Prodotti**
* **Obiettivo:** Amministrazione dell'offerta commerciale con vincoli di integrità e visualizzazione dinamica.
* **Descrizione:** Sviluppo funzionalità CRUD con controlli di coerenza.
    * **Visualizzazione (Read):** Caricamento dei prodotti attivi (`is_deleted = 0`) e rendering dinamico della tabella menu con gestione dei permessi UI.
    * **Creazione/Modifica:** Aggiunta piatti tramite modale Bootstrap.
    * **Toggle Disponibilità:** Interruttore immediato per rendere un piatto non ordinabile senza eliminarlo.
    * **Vincolo Operativo (Soft Delete):** Eliminazione logica (`is_deleted = 1`). È stato implementato un blocco di sicurezza: la View disabilita i tasti di creazione/eliminazione e il backend (`Create.php` e `Delete.php`) respinge la richiesta se il locale risulta "APERTO", per evitare incongruenze con ordini in corso.
* **Output Tecnico:** Controller `Read.php`, `Create.php`, `Delete.php`, `Toggle_Availability.php`.

### 🟡 FASE 3: Gestione Operativa e Ordini (Business Logic Avanzata)

#### **Task 3.4: Dashboard Kanban e Polling Real-Time**
* **Obiettivo:** Monitoraggio visuale del flusso ordini.
* **Descrizione:**
    * **Visualizzazione Kanban:** La View organizza gli ordini in tre colonne semantiche: *In Arrivo* (Attesa), *In Preparazione*, *Pronto per il Ritiro*.
    * **Polling Intelligente:** Il Presenter utilizza un `setInterval` (2000ms) per interrogare l'API `Get_Orders.php`, aggiornando la UI in modo reattivo e mantenendo lo stato dei focus sugli input attivi.
* **Output Tecnico:** Rendering dinamico in `Esercente_View.js` (`renderOrders`), gestione timer in `Esercente_Presenter.js`.

#### **Task 3.5: Implementazione Architetturale (Pattern Strategy & Factory)**
* **Obiettivo:** Gestione scalabile delle transizioni di stato lato Backend.
* **Descrizione:** **Implementazione architetturale** della logica di cambio stato tramite Design Pattern per garantire estensibilità e pulizia del codice.
    * **Strategy Pattern:** Ogni transizione è una classe isolata: `AccettaStrategy`, `RifiutaStrategy`, `ProntoStrategy`, `RitiratoStrategy` e `NonRitiratoStrategy`.
    * **Factory Pattern:** La classe `StrategyFactory` istanzia la strategia corretta basandosi sul parametro `azione` inviato dal frontend.
* **Output Tecnico:** File `Order_Strategies.php` (interfaccia `OrderStrategy`), `Strategy_Factory.php`, API `Update_Order_Status.php`.

#### **Task 3.6: Sicurezza e Consegna (Verifica OTP)**
* **Obiettivo:** Finalizzazione sicura della transazione tramite "Two-Factor verification" semplificata.
* **Descrizione:**
    * **Frontend:** Le card nella colonna "Pronto" presentano un campo di input integrato per inserire il Codice di Verifica. Il sistema supporta l'invio tramite click o tasto Invio.
    * **Backend:** La `RitiratoStrategy` esegue una validazione stretta: confronta l'OTP inviato con il `codice_ritiro` nel DB. Se coincidono, l'ordine passa a "Ritirato", altrimenti viene sollevata un'eccezione gestita dal frontend con modale di errore.
* **Output Tecnico:** Logica di validazione in `Order_Strategies.php`, gestione modali (`showErrorModal`) e input OTP in `Esercente_View.js`.

### 🔵 FASE 4: Predisposizione Integrazione e Sicurezza (JWT)

#### **Task 3.7: Mocking Autenticazione JWT (Provvisorio)**
* **Obiettivo:** Preparazione dell'architettura alla sicurezza Token-Based in vista del merge finale.
* **Natura Provvisoria:** 🚧 Implementazione di un sistema di "Mocking" che simula il flusso JWT reale. Utilizza un generatore di token locale (`Generate_Test_Token.php`) per permettere lo sviluppo isolato del modulo Esercente prima dell'integrazione effettiva con il Modulo Autenticazione.
* **Middleware:** Introduzione di `Auth_Helper` per simulare l'estrazione sicura dell'ID utente dal token, eliminando la dipendenza da parametri statici (es. `id_esercente=3`) e preparando il backend alla logica di produzione.
* **Frontend:** Adattamento di `Esercente_Model.js` per l'iniezione automatica del *Bearer Token*. Questa struttura permetterà in futuro di passare al sistema reale semplicemente sostituendo il token di test con quello fornito dal Login, senza modificare il codice.