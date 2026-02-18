# Documentazione Tecnica: Food Delivery Campus

Il progetto **Food Delivery Campus** adotta un'architettura **Stateless** basata su una collezione di **micro-SPA** (Single Page Applications). Questa documentazione funge da riferimento centrale per lo sviluppo, garantendo coerenza tra i vari componenti del sistema.

*NB: Questa è la docuementazione generale, per approfondire vedere la documentazione di ogni modulo (docs/Modulo_NomeModulo/)*

---

## Indice
- 📌 1. Infrastruttura e Deployment
- 📌 2. Architettura dei Dati (JSON Schemas)
- 📌 3. Design System e UI Guidelines
- 📌 4. Metodologia di Sviluppo
- 📌 5. Dettaglio Moduli e Team di Sviluppo
- 📌 6. Architettura Tecnica e Design Pattern Globali
- 📌 7. Struttura dei commit

---

## 1. Infrastruttura e Deployment

Il sistema è completamente containerizzato per garantire portabilità e coerenza dell'ambiente di sviluppo.

### 1.1 Moduli di Sistema
L'ecosistema è suddiviso in tre moduli principali isolati e interconnessi tramite una rete virtuale interna.

| Modulo | Porta | Stack | Descrizione |
| :-- | :--: | :-- | :-- |
| **Frontend** | 8000 | Nginx | Distribuzione contenuti statici e gestione UI. |
| **API Backend** | 8001 | Apache + PHP 8.2 (PDO) | Logica di business e richieste API. |
| **Persistenza Dati** | 3306 | MySQL 8.0 | Database relazionale con schema inizializzato automaticamente. |

### 1.2 Gestione dell'Ambiente
L'avvio e l'arresto del sistema sono automatizzati tramite script Bash:

- `start.sh`: Compila le immagini Docker e attiva i container.
- `stop.sh`: Arresta i servizi e libera le risorse mantenendo la persistenza dei dati tramite volumi.

---

## 2. Architettura dei Dati (JSON Schemas)

Per garantire una comunicazione priva di errori tra Frontend (JavaScript) e Backend (PHP), sono definiti **Contratti di Interfaccia** tramite lo standard JSON Schema. Questi schemi assicurano che ogni payload sia validato strutturalmente prima dell'elaborazione.

### 2.1 Catalogo degli Schemi
Tutti i file sono archiviati localmente. I percorsi correnti seguono la struttura del progetto.

| Area | Schema | Descrizione |
| :-- | :-- | :-- |
| **Autenticazione** | `login_request.schema.json` | Valida le credenziali (`email`, `password`). |
| **Autenticazione** | `auth_response.schema.json` | Definisce la risposta del server, includendo Token e Ruolo dell'utente. |
| **Cliente** | `restaurant_list.schema.json` | Struttura i dati per la griglia dei ristoranti (nome, voto, tempo consegna). |
| **Cliente** | `menu_details.schema.json` | Valida la lista dei piatti e i prezzi numerici per il carrello. |
| **Esercente** | `order_list.schema.json` | Contratto per le tre colonne della dashboard (Nuovo, Preparazione, Pronto). |
| **Esercente** | `order_status_update.schema.json` | Valida le richieste di avanzamento dello stato dell'ordine. |
| **Ordini** | `order_item.schema.json` | Struttura unificata per la visualizzazione di un ordine (Passato o Attivo). |
| **Profilo** | `get_profile_response.schema.json` | Profilo Utente Response. |
| **Profilo** | `update_profile_request.schema.json` | Aggiornamento Profilo Request. |

---

## 3. Design System e UI Guidelines

Il progetto utilizza un set di classi master e variabili CSS identificate dal prefisso `fdc-` per garantire coerenza visiva.

### 3.1 Design Tokens (Variabili CSS)
Le variabili sono definite nel file `global.css` e fungono da punto unico di verità per lo stile.

- **Colori Brand**: Arancione (`--fdc-primary: #d35400`) e Blu Notte (`--fdc-dark: #0b1320`).
- **Stati**: Giallo per nuovi ordini (`--fdc-status-new: #f1c40f`) e Verde per ordini pronti (`--fdc-status-ready: #2ecc71`).
- **Layout**: Arrotondamento standard delle card (`--fdc-radius: 12px`).

### 3.2 Componenti UI Master
- **`.fdc-card`**: Contenitore standard con ombra e bordi arrotondati per card ristoranti e ordini.
- **`.btn-fdc`**: Pulsante principale arancione per invio form o azioni standard.
- **`.btn-fdc-light`**: Variante bianca ad alto contrasto per il Logout su sfondi arancioni.
- **`.fdc-logo-box`**: Etichetta del brand utilizzata nella Navbar e nel Login.
- **`.fdc-footer`**: Barra informativa istituzionale in blu notte ancorata al fondo pagina.

---

## 4. Metodologia di Sviluppo

Il progetto Food Delivery Campus è stato realizzato seguendo rigorosamente il Modello a Cascata (Waterfall Model). Questo approccio sequenziale ha garantito che ogni fase fosse completata e validata prima di passare alla successiva, minimizzando i rischi di regressione architetturale.

### 4.1 Fasi del Ciclo di Vita
Ogni modulo funzionale ha attraversato il seguente pipeline di sviluppo:

- **Analisi dei Requisiti**: Definizione dei Casi d'Uso (UC), stesura delle Matrici di Conformità e Definizione dei Requisiti Funzionanli e Non Funzionali.
- **Progettazione (Design)**:
  - Modellazione UML: Diagrammi delle Classi, Sequenza, Attività e Deployment.
  - Definizione dei contratti di interfaccia (JSON Schema) e Schema E/R del Database.
- **Implementazione**: Sviluppo del codice seguendo i pattern architetturali definiti (MVP, Factory, Strategy).
- **Testing & Validazione**: Esecuzione di test unitari (White Box) e test di sistema (Black Box) documentati nei Test Report.

---

## 5. Dettaglio Moduli e Team di Sviluppo
Il sistema è suddiviso in 4 moduli verticali, ognuno affidato a un responsabile che ne ha curato l'intero stack (Frontend, Backend, Database e Documentazione).

### 5.1 Modulo 1: Autenticazione e Infrastruttura
**Responsabile**: Mario Sale (Matr. 364432)

Questo modulo costituisce la "porta d'ingresso" sicura alla piattaforma, gestendo l'identità digitale e l'orchestrazione iniziale dell'ambiente.

**Funzionalità Chiave**
- Registrazione Dinamica (Smart Role Detection): rilevamento automatico del ruolo utente tramite dominio email, con campo matricola condizionale.
- Login Stateless: autenticazione sicura tramite JWT (JSON Web Token).
- Pattern Builder: uso di `ClientRequestBuilder.js` per costruire chiamate HTTP autenticate.
- Factory Pattern: uso di `AuthViewFactory.js` per centralizzare la creazione delle viste di login e registrazione.

**File Principali**
* **Backend & API**
    - `src/api/auth/login.php`, `register.php` (Endpoint di autenticazione)
    - `src/api/utils/JwtUtils.php` (Gestione e verifica token JWT)

* **Frontend (Architettura MVP)**
    - `src/public/js/model/AuthModel.js` (Logica dati e stato)
    - `src/public/js/presenter/AuthPresenter.js` (Orchestratore logica/vista)
    - `src/public/js/view/Loginview.js`, `RegisterUniPRView.js` (Viste concrete)
    - `src/public/js/view/IAuthView.js` (Interfaccia per le viste)

* **Frontend (Servizi & Core)**
    - `src/public/js/services/AuthViewFactory.js` (Creazione istanze delle viste)
    - `src/public/js/services/ClientRequestBuilder.js` (Gestione chiamate di rete/fetch)
    - `src/public/index.html` (Entry point applicazione)

* **Documentazione**
    - `docs/Modulo_Autenticazione/*` *(Intero contenuto della cartella)*

### 5.2 Modulo 2: Area Cliente e Ordinazioni
**Responsabile**: Alessandro Di Stasi

Il cuore dell'esperienza utente B2C: gestione navigazione ristoranti, carrello e invio ordini.

**Funzionalità Chiave**
- Navigazione Ristoranti: recupero dinamico lista esercenti con rating e tempi di consegna.
- Gestione Carrello: logica client-side per aggiunta/rimozione piatti e calcolo totale in tempo reale.
- Creazione Ordine: invio del payload validato tramite JSON Schema.

**File Principali**

**Back end & API**
- `src/api/restaurants/*` 
- `src/api/orders/*` 

**Frontend**
- `src/public/js/Cliente_Presenter.js` 
- `src/public/Cliente.html` 

**Test**
- `src/api/tests/cliente/test_orders_runners.php`

**Documentazione**
- `docs/modulo2/*` *(Intero contenuto della cartella)*

### 5.3 Modulo 3: Dashboard Esercente & Gestione Operativa
**Responsabile**: Andrea Poccetti (Matr. 361127)

Interfaccia B2B avanzata per gestione ordini e catalogo prodotti.

**Funzionalità Chiave**
- Dashboard Kanban Real-Time: tre colonne (In Arrivo, In Preparazione, Pronto) con polling intelligente (refresh ogni 2000ms senza perdere il focus).
- Pattern Strategy & Factory (Backend): stati ordine gestiti da classi dedicate istanziate da `StrategyFactory`.
- Sicurezza OTP (2FA): ritiro ordine con codice univoco per verifica cliente.
- Soft Delete & Vincoli: cancellazione logica (is_deleted=1) e blocco modifiche menu se locale in stato "APERTO".

**File Principali**
- `src/public/Esercente.html` (dashboard)
- `src/api/esercente/ordini/strategies/*` (implementazione Strategy)
- `src/public/js/Esercente_Presenter.js` (polling e routing)
- `src/public/js/Esercente_Model.js` (model)
- `src/public/js/Esercente_View.js` (view)
- `src/api/esercente/menu/*` (API menu)
- `src/api/esercente/ordini/*` (API ordini)
- `src/api/tests/esercente/Test_Esercente.php` (test)
- `src/api/utils/Auth_Helper.php
- `docs/Modulo_3_Esercente/*` (documentazione)

### 5.4 Modulo 4: Profilo, Storico e Core Infrastructure
**Responsabile**: Pasquale Colucci (Matr. 358141)

Gestione persistenza trasversale, sicurezza dati utente e storico ordini.

**Funzionalità Chiave**
- Core Database: connessione MySQL centralizzata via `Database.php`, istanziata on-demand.
- Storico Ordini (Strategy Pattern e Factory Method): filtri Attivi/Passati con `ActiveOrdersStrategy` e `PastOrdersStrategy`.
- UI Masking & RBAC: adattamento dinamico di Profilo.html e controlli backend per accessi autorizzati.
- Validazione Ridondante: controlli P.IVA e input sia lato client che server.

**File Principali Modulo Storico Ordini**

**Backend & API**

* `src/api/storico/patterns/factory/OrderStrategyFactory.php`
* `src/api/storico/patterns/strategy/ActiveOrdersStrategy.php`
* `src/api/storico/patterns/strategy/AllOrdersStrategy.php`
* `src/api/storico/patterns/strategy/PastOrdersStrategy.php`
* `src/api/storico/patterns/strategy/IOrderFetchStrategy.php`
* `src/api/storico/get_history.php`
* `src/api/storico/OrderHistoryRespository.php`

**Frontend**

* `src/public/js/history.js`
* `src/public/Storico.html`

**Test**

* `src/api/tests/storico/test_runner.php`

**Documentazione**

* `docs/Modulo_4_Profilo_&_Storico/Modulo_Storico_Ordini/*` *(Intero contenuto della cartella)*

---

**File Principali Modulo Gestione Profilo**

**Backend & API**

* `src/api/profilo/get_profile.php`
* `src/api/profilo/update_profile.php`
* `src/api/profilo/UserProfileRepo.php`

**Frontend**

* `src/public/js/profile.js`
* `src/public/Profilo.html`

**Test**

* `src/api/tests/profilo/test_runner.php`

**Documentazione**

* `docs/Modulo_4_Profilo_&_Storico/Modulo_Gestione_Profilo/*` *(Intero contenuto della cartella)*

---

**File Principali Core Infrastructure**

* `src/api/utils/Database.php`
---

## 6. Architettura Tecnica e Design Pattern Globali
Il progetto si distingue per l'adozione coerente di pattern architetturali avanzati su tutto lo stack applicativo.

### 6.1 Frontend: Pattern MVP (Model-View-Presenter)
Tutte le interfacce (Login, Cliente, Esercente, Profilo) sono SPA che separano nettamente le responsabilità.

- **Model**: gestione dati e comunicazione AJAX (fetch).
- **View**: gestione DOM ed eventi utente.
- **Presenter**: orchestration della logica di business e collegamento Model-View.

### 6.2 Backend: Pattern Comportamentali e Creazionali
Per garantire manutenibilità e scalabilità, il backend PHP adotta oggetti dedicati.

- **Strategy Pattern**: usato nei Moduli 3 e 4 per comportamenti intercambiabili.
- **Factory Method**: istanzia strategie e viste senza accoppiare a classi concrete.

### 6.3 Sicurezza e Autenticazione
- **JWT (Stateless)**: nessuna sessione salvata sul server, autenticazione via Bearer Token.
- **Password Hashing**: cifratura con Bcrypt.
- **OTP (One Time Password)**: verifica a due fattori per il ritiro fisico dell'ordine.
- **SQL Injection Prevention**: uso esclusivo di Prepared Statements (PDO).

---

## 7. Struttura dei commit
I commit del progetto seguono una struttura ben precisa: 
* feat: aggiunto layout base
* fix: corretto stile header
* docs: aggiornato README
* refactor: rinominati componenti
* chore: aggiornato .gitignore
