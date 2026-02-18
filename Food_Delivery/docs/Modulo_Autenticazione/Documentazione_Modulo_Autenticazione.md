# 📘 Documentazione Tecnica: Modulo Autenticazione & Infrastruttura

| **Meta-Dati** | **Dettagli** |
| :--- | :--- |
| 👤 **Autore** | Sale Mario (Matr. 364432) |
| 📅 **Ultima Modifica** | 10/02/2026 |
| 📦 **Modulo** | Auth |
| 🔧 **Tecnologie** | PHP 8.0, MySQL 8.0, Docker, JS Vanilla |

---

## 1. 🎯 Introduzione e Scopo

Il modulo **Autenticazione (Auth)** è il componente infrastrutturale responsabile della sicurezza, dell'identità digitale e della gestione degli accessi alla piattaforma *"Food Delivery Campus"*.

Il modulo è progettato per gestire tre pillar fondamentali:
1.  **Registrazione Dinamica (UC-1.10)**: Rilevamento automatico del ruolo in base al dominio email (`@studenti.unipr.it` vs `@unipr.it`) e gestione condizionale della matricola.
2.  **Login Sicuro (UC-1.0)**: Verifica delle credenziali tramite Hashing (Bcrypt) e rilascio di Token JWT Stateless.
3.  **Infrastruttura Dati**: Configurazione dell'ambiente Docker e inizializzazione dello schema database relazionale.

---

## 2. 🏗️ Architettura del Modulo

Il sistema aderisce rigorosamente al pattern **Model-View-Presenter (MVP)**, garantendo la separazione tra logica di business, interfaccia e dati.

### 2.1 Mappatura dei Componenti

| Componente | Ruolo (MVP) | File / Artefatto | Descrizione Tecnica |
| :--- | :--- | :--- | :--- |
| **Interfaccia View** | `Contratto` | `IAuthView.js` | Interfaccia astratta che definisce i metodi obbligatori per tutte le viste di autenticazione. |
| **View Concreta** | `Interfaccia` | `LoginView.js`<br>`RegisterUniPRView.js` | Implementano `IAuthView`. Gestiscono il DOM e la logica visiva "smart" (es. campo matricola a comparsa dinamica). |
| **Presenter** | `Logica UI` | `AuthPresenter.js` | Mediatore. Intercetta gli eventi delle View, chiama il Model e gestisce il routing post-login. |
| **Factory** | `Creazionale` | `AuthViewFactory.js` | Factory Method pattern. Centralizza la creazione delle viste concrete. |
| **Model** | `Business Logic` | `AuthModel.js` | Gestisce le chiamate asincrone (`fetch`) al backend. |
| **Builder** | `Utility` | `ClientRequestBuilder.js` | Builder Pattern per costruzione fluida di richieste HTTP con autenticazione JWT. |
| **Controller** | `API` | `api/auth/login.php`<br>`api/auth/register.php` | Endpoint REST. Validano input e interagiscono con il database. |
| **Infra** | `System` | `docker-compose.yml`<br>`init.sql` | Orchestrazione container e definizione DDL. |

### 2.2 Pattern Implementati

1. **MVP (Model-View-Presenter)** - Separazione delle responsabilità
2. **Interfaccia Astratta** - `IAuthView` definisce contratto per le viste
3. **Factory Method** - `AuthViewFactory` crea viste in base al tipo
4. **Builder Pattern** - `ClientRequestBuilder` costruisce richieste HTTP

---

## 3. 🗄️ Gestione dei Dati (Database)

Il modulo definisce lo schema relazionale primario per la gestione delle identità.

### 3.1 Schema Relazionale (E/R)

* 🔑 **UTENTE (Padre)**
    * Tabella centrale contenente le credenziali: `id_utente`, `email`, `password` (Hash Bcrypt), `ruolo`.
* 🎓 **CLIENTE_UNIPR (Estensione)**
    * Dati anagrafici per studenti e docenti.
    * La colonna `matricola` è **NULLABLE** per supportare i docenti (che non la possiedono).

---

## 4. 🔄 Flussi Funzionali

### 4.1 Registrazione Condizionale (Flow UC-1.10)
1.  **Input (Frontend):** Un listener JS analizza il dominio della mail in tempo reale (on `input` event).
2.  **Logica View:** Se `@studenti.unipr.it` → campo matricola appare; se `@unipr.it` → campo nascosto.
3.  **Elaborazione (Backend):** PHP verifica il dominio e imposta `matricola = NULL` per i docenti.

### 4.2 Login e Routing (Flow UC-1.0)
1.  **Auth:** Il backend cerca l'utente e verifica l'hash con `password_verify()`.
2.  **Token:** Se valido, genera un **JWT** contenente il claim `ruolo`.
3.  **Routing:** Il Frontend reindirizza: `esercente` → `Esercente.html`, `studente/docente` → `Cliente.html`.

---

## 5. 📡 Specifiche API

#### **POST** `http://localhost:8001/auth/login.php`
*Autentica l'utente e restituisce il Token di sessione.*

```json
{
    "message": "Login effettuato con successo.",
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "ruolo": "studente",
    "id_utente": 1
}
```

## 6. 🛡️ Misure di Sicurezza

| Misura | Dettaglio Implementativo |
| :--- | :--- |
| 🔒 **Password Hashing** | Utilizzo dell'algoritmo **Bcrypt** (`$2y$`) con costo adattivo. Nessuna password è salvata in chiaro. |
| 💉 **SQL Injection** | Utilizzo esclusivo di **Prepared Statements** (PDO) con binding dei parametri per prevenire iniezioni. |
| 🎫 **Stateless Auth** | Autenticazione basata su **JWT**, eliminando le sessioni server-side (in conformità a **RNF-2.1**). |
| ✉️ **Input Validation** | Whitelist rigorosa lato server dei domini email accettati (`unipr.it`, `studenti.unipr.it`). |