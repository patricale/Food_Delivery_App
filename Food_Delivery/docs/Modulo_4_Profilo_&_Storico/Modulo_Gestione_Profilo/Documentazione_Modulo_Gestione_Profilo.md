# Documentazione Modulo: Gestione Profilo Utente

*SVOLTO DA COLUCCI PASQUALE, MATR: 358141*

## 1. Introduzione e Scopo

Il modulo **Gestione Profilo** è il componente del sistema responsabile della visualizzazione e della modifica delle informazioni personali degli utenti.
Il modulo è progettato per gestire in modo dinamico e sicuro le due tipologie di attori principali della piattaforma:

1. **Cliente Unipr (Studente/Docente)**: Caratterizzato da dati accademici (es. Matricola).
2. **Esercente**: Caratterizzato da dati aziendali (es. Partita IVA, Ragione Sociale).

L'obiettivo primario è garantire che ogni utente possa gestire i propri dati mantenendo l'integrità delle informazioni sensibili attraverso un'architettura rigorosamente **Stateless** e sicura.

---

## 2. Architettura del Modulo

Il modulo aderisce al pattern **Model-View-Presenter (MVP)** distribuito su container Docker, come definito nel *Diagramma di Deployment*.

### 2.1 Mappatura dei Componenti

| Componente | Ruolo (MVP) | File / Artefatto | Descrizione Tecnica |
| --- | --- | --- | --- |
| **View** | Interfaccia Passiva | `src/public/Profilo.html` | Pagina HTML statica servita dal container Frontend (Porta 8000). Contiene il form unico adattivo. |
| **Presenter** | Logica di Presentazione | `src/public/js/profile.js` | Script JavaScript. Gestisce il **Token JWT**, inietta l'header `Authorization` e manipola il DOM in base al ruolo. |
| **Controller** | Validazione e Orchestrazione | `src/api/profilo/update_profile.php` | Endpoint API. Implementa la **Validazione Ridondante** e decodifica il token per identificare l'utente. |
| **Model** | Accesso ai Dati | `src/api/profilo/UserProfileRepo.php` | Implementa la logica di persistenza. Istanzia il Database on-demand per garantire l'isolamento. |

### 2.2 Scelte Progettuali Chiave

1. **Sicurezza Stateless:**
Il sistema non utilizza sessioni server-side (`$_SESSION`). L'autenticazione avviene tramite **Token JWT** inviato nell'Header HTTP (`Authorization: Bearer <token>`). Il server verifica la firma del token a ogni singola richiesta.
2. **Difesa in Profondità:**
Nonostante la validazione client-side (JS), il Backend riesegue rigorosamente tutti i controlli sui dati in ingresso (Regex P.IVA, lunghezza password, sanitizzazione) per prevenire attacchi diretti alle API.
3. **Gestione Database:**
Per garantire la scalabilità e l'isolamento delle transazioni, la connessione al database viene creata (`new Database()`) e distrutta all'interno del ciclo di vita della singola richiesta HTTP.

---

## 3. Requisiti Soddisfatti

* **Visualizzazione:** L'utente visualizza i propri dati anagrafici corretti al caricamento.
* **Modifica:** L'utente può aggiornare password, nome/cognome (se Studente) o dati aziendali (se Esercente).
* **Adattabilità:** L'interfaccia nasconde/mostra i campi specifici in base al ruolo (Studente vs Esercente).

---

## 4. Analisi dei Diagrammi

### 4.1 Diagramma delle Classi

Rappresenta la struttura statica. Evidenzia la relazione di dipendenza tra il `UserProfileRepository` e la classe `Database`.

* **Nota Architetturale:** La freccia "creates" indica che il Repository è responsabile dell'istanziazione della connessione, confermando l'assenza di un Singleton globale.

### 4.2 Diagramma di Sequenza (Aggiornamento)

Descrive il flusso temporale della richiesta di modifica:

1. **Client:** Pre-validazione (match password) e invio richiesta con Header Auth.
2. **API (Controller):** Decodifica Token e **Validazione** (Regex/Sanitizzazione).
3. **Model:** Se validazione OK, esecuzione query di Update.

### 4.3 Diagramma di Deployment

Mostra la distribuzione fisica sui container:

* Frontend (Nginx) serve `Profilo.html`.
* Backend (Apache/PHP) esegue la logica di validazione e accesso DB.
* Comunicazione via rete interna Docker `food_net`.

---

## 5. Strategia di Test

Per garantire la robustezza e la sicurezza del modulo, sono stati eseguiti tre livelli di test.

### A. White Box Testing (Automatizzato)

Script: **`src/api/profilo/test_runner.php`**
Verifica le operazioni CRUD dirette sul database.

* Test di connessione (Istanza on-demand).
* Test di aggiornamento anagrafica e rollback.
* Test di aggiornamento password.

### B. Security Testing

Verifica specifica dei requisiti di sicurezza.

* **Bypass Client:** Invio dati invalidi via Postman -> Il server risponde `400 Bad Request`.
* **Accesso Anonimo:** Chiamata senza Token -> Il server risponde `401 Unauthorized`.

### C. Black Box Testing (System)

Scenari eseguiti sull'interfaccia `Profilo.html`.

* Verifica caricamento dinamico campi Esercente.
* Verifica feedback visivi (Alert success/error).

---

## 6.Adattamento Dinamico dell'Interfaccia

Il modulo di gestione profilo è stato adattato per riflettere dinamicamente i privilegi dell'utente loggato. Al caricamento della pagina (ProfileManager_JS), il sistema analizza il payload del Token JWT per determinare il ruolo corrente. In base al ruolo rilevato, viene applicata una logica di mascheramento degli elementi di navigazione (UI Masking): se l'utente è identificato come 'Esercente', i pulsanti di navigazione verso funzionalità esclusive del Cliente (es. "I Miei Ordini", "Lista Ristoranti") vengono rimossi programmaticalmente dal DOM. Questo approccio migliora l'usabilità, riducendo il carico cognitivo dell'utente ed evitando tentativi di navigazione verso aree non autorizzate o non pertinenti al proprio flusso di lavoro.

---

## 7. Conclusioni

Il modulo soddisfa pienamente i requisiti di progetto, dimostrando particolare attenzione alla **sicurezza applicativa**. L'adozione della validazione ridondante lato server e del protocollo stateless rende il sistema robusto contro manipolazioni esterne e pronto per uno scaling orizzontale.