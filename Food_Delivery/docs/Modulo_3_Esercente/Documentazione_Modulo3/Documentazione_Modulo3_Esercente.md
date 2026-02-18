# 🥡 Food Delivery Campus - Modulo Esercente

![Version](https://img.shields.io/badge/versione-1.0-blue.svg)
![Status](https://img.shields.io/badge/stato-COMPLETO-success.svg)
![Tech](https://img.shields.io/badge/backend-PHP-purple.svg)
![Tech](https://img.shields.io/badge/frontend-JS%20%2F%20MVP-yellow.svg)
![Database](https://img.shields.io/badge/database-MySQL-orange.svg)

> **Documentazione Tecnica Ufficiale** > **Modulo:** 3 - Gestione Esercente  
> **Autore:** Andrea Poccetti, Matricola: 361127  
> **Riferimento:** Esame di Ingegneria del Software

---

## 📑 Indice dei Contenuti
1. [Visione del Progetto](#-1-visione-generale-e-scopo)
2. [Architettura del Sistema](#-2-architettura-del-sistema)
3. [Dettaglio dei Task Implementati](#-3-dettaglio-implementativo-dei-task)
4. [Modellazione Dati](#-4-modellazione-dei-dati-database)
5. [Verifica e Testing](#-5-verifica-e-validazione-testing)
6. [Installazione](#-6-guida-allinstallazione)

---

## 🔭 1. Visione Generale e Scopo

Il **Modulo 3** costituisce il cuore operativo B2B della piattaforma *Food Delivery Campus*.
È progettato per offrire ai partner commerciali (Ristoranti, Bar, Pizzerie) una **Dashboard Centralizzata** per gestire l'intero ciclo di vita dell'attività.

### 🎯 Obiettivi Chiave
* ⚡ **Real-Time:** Monitoraggio ordini in tempo reale.
* 🛡️ **Sicurezza:** Validazione consegne tramite OTP (2FA).
* 🔧 **Controllo:** Gestione autonoma di menu e stato apertura.

---

## 🏗️ 2. Architettura del Sistema

Il sistema è costruito su un'architettura **distribuita e containerizzata**, seguendo rigorosi pattern di progettazione per garantire manutenibilità e scalabilità.

### 🖥️ Frontend: Pattern MVP
L'interfaccia utente è una **SPA (Single Page Application)** basata sul pattern **Model-View-Presenter**:

| Componente | Responsabilità | File Principale |
| :--- | :--- | :--- |
| **Model** 🧠 | Gestione dati e comunicazione API (AJAX). | `Esercente_Model.js` |
| **View** 🎨 | Rendering del DOM e gestione eventi UI. | `Esercente_View.js` |
| **Presenter** 🎬 | Logica di business, routing e orchestrazione. | `Esercente_Presenter.js` |

### ⚙️ Backend: Design Patterns
Per gestire la complessità delle transizioni di stato, il backend abbandona i costrutti condizionali giganti a favore di pattern OOP:

> **💡 Design Pattern Highlight**
>
> * **Strategy Pattern:** Ogni cambio stato (es. *Accetta*, *Rifiuta*, *Ritira*) è una classe isolata (`AccettaStrategy`, `RitiratoStrategy`).
> * **Factory Pattern:** Una `StrategyFactory` decide dinamicamente quale strategia istanziare in base all'input utente.

---

## 📝 3. Dettaglio Implementativo dei Task

Lo sviluppo è stato suddiviso in fasi logiche incrementali.

### 🟢 FASE 1: Infrastruttura Core

#### **Task 3.1: Architettura MVP e Routing**
* **Obiettivo:** Creazione dello scheletro SPA.
* **Implementazione:** Il `Presenter` gestisce la navigazione (Tab *Ordini*, *Menu*, *Profilo*) manipolando la visibilità dei container HTML. Nessun ricaricamento di pagina richiesto.

### 🟠 FASE 2: Gestione Business (Configurazione)

#### **Task 3.2: Gestione Stato Attività**
* **Funzione:** Toggle Aperto/Chiuso.
* **Vincolo:** 🔒 Quando il locale è **APERTO**, le modifiche strutturali al menu sono bloccate per evitare inconsistenze sugli ordini in corso.

#### **Task 3.3: Gestione Catalogo (CRUD)**
* **Visualizzazione:** Rendering dinamico tabella prodotti.
* **Soft Delete:** Implementazione della cancellazione logica (`is_deleted = 1`). I dati non vengono mai persi fisicamente.
* **Quick Actions:** Toggle rapido disponibilità piatto (es. "Esaurito").

### 🟡 FASE 3: Gestione Operativa (Advanced Logic)

#### **Task 3.4: Dashboard Kanban & Polling**
Visualizzazione degli ordini su tre colonne semantiche:
1.  🕒 **Ordini in arrivo** (Attesa)
2.  👨‍🍳 **In Preparazione**
3.  ✅ **Pronto per Ritiro**

> **🔄 Polling Intelligente:** Il sistema esegue un refresh ogni `2000ms` mantenendo il focus sugli input attivi (es. non interrompe l'utente mentre digita un codice).

#### **Task 3.6: Sicurezza e Consegna (OTP)**
Implementazione della **Two-Factor Verification** per il ritiro.
1.  Cliente comunica codice univoco (es. `X9Y2`).
2.  Esercente inserisce codice nella dashboard.
3.  Backend verifica corrispondenza:
    * ✅ **Match:** Ordine concluso (`Ritirato`).
    * ❌ **Mismatch:** Errore bloccante.

### 🔵 FASE 4: Predisposizione Integrazione e Sicurezza (JWT)

#### **Task 3.7: Autenticazione & API Security**
* **Obiettivo:** Migrazione a sistema Token-Based (JWT).
* **Middleware:** `Auth_Helper` centralizza la validazione ed estrae l'ID sicuro dal Token (addio ID in URL).
* **Ownership Check:** 🔒 Verifica rigorosa lato backend. Impedisce la modifica di risorse (es. ordini) appartenenti ad altri esercenti.
* **Frontend:** Iniezione automatica dell'header `Bearer Token` in tutte le chiamate AJAX.

---

## 🗄️ 4. Modellazione dei Dati (Database)

Schema relazionale ottimizzato (3NF).

#### 🏪 Entità: ESERCENTE
Estende `UTENTE`. Contiene dati fiscali e lo stato operativo.
```sql
stato_apertura BOOLEAN DEFAULT TRUE
```

#### 📦 Entità: ORDINE
Gestisce il flusso vitale.
```sql
stato ENUM('attesa', 'preparazione', 'pronto', 'ritirato', ...)
codice_ritiro VARCHAR(10) UNIQUE -- 🔑 Chiave di sicurezza OTP
```

#### 🍔 Entità: PRODOTTO
Supporta lo storico ordini tramite Soft Delete.

```sql
is_deleted BOOLEAN DEFAULT FALSE -- Non cancelliamo mai fisicamente!
```

---

## ✅ 5. Verifica e Validazione (Testing)
Il modulo è stato validato tramite test unitari e di integrazione in ambiente Docker.

| ID Test | Ambito | Descrizione Verifica | Esito |
| :--- | :--- | :--- | :--- |
| TEST 1 | ⚙️ Backend | Pattern Factory instanzia la strategia corretta | 🟢 PASS |
| TEST 2 | 🛡️ Security | Validazione OTP (Case-insensitive & Reject) | 🟢 PASS |
| TEST 3 | 🔒 Integrity | Blocco eliminazione piatti a Locale APERTO | 🟢 PASS |
| TEST 4 | 🔄 System | Ciclo completo Ordine (UI -> API -> DB) | 🟢 PASS |
| TEST 5 | 👁️ UX | Persistenza focus input durante Polling | 🟢 PASS |

---

## 🚀 6. Guida all'Installazione
Prerequisiti: Docker Desktop.

### 1️⃣ Avvio Ambiente
Eseguire lo script di start (wrapper di docker-compose):

```bash
./start.sh
```

### 2️⃣ Accesso Dashboard
Aprire il browser all'indirizzo:

🔗 http://localhost:8000/Esercente.html

User: .....  
Password: ....

### 3️⃣ Esecuzione Test Suite
Per lanciare la suite di test automatizzata:

🔗 http://localhost:8001/tests/esercente/Test_Esercente.php