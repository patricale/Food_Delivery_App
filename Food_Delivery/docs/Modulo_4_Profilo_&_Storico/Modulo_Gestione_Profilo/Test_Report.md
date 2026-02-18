# Report di Test - Modulo Gestione Profilo

*SVOLTO DA COLUCCI PASQUALE, MATR: 358141*

## 1. Strategia di Test
Per la validazione del modulo sono state adottate due metodologie:
* **White Box Testing (Automatizzato):** Verifica diretta delle classi PHP e delle query SQL tramite script di test (`src/api/profilo/test_runner.php`).
* **Black Box Testing (Manuale):** Verifica dell'interfaccia utente e dell'integrazione Frontend-Backend.

## 2. Test Automatizzati (Backend)
Il componente `UserProfileRepository` è stato sottoposto a unit testing automatico.

| ID Test | Descrizione | Risultato Atteso | Esito |
| :--- | :--- | :--- | :--- |
| **UNIT-01** | Istanza Repository | Connessione al DB riuscita | ✅ PASS |
| **UNIT-02** | Fetch Utente Esistente | Restituzione array dati non vuoto | ✅ PASS |
| **UNIT-03** | Coerenza Ruolo | ID Studente restituisce ruolo 'studente' | ✅ PASS |
| **UNIT-04** | Update Anagrafica | La query UPDATE modifica effettivamente il record | ✅ PASS |
| **UNIT-05** | Integrità Dati | Rollback dei dati di test al valore originale | ✅ PASS |
| **UNIT-06** | Sicurezza Password | Update e Rollback della password crittografata | ✅ PASS |

## 3. Test Funzionali (Frontend/System)
Scenari eseguiti sull'interfaccia web (`Profilo.html`).

| ID Scenario | Azione Utente | Risultato Atteso | Esito | Note |
| :--- | :--- | :--- | :--- | :--- |
| **SYS-01** | Caricamento Profilo Studente | Campi Nome/Cognome popolati. Matricola in sola lettura. | ✅ PASS | Verifica vincolo immutabilità matricola. |
| **SYS-02** | Cambio Ruolo (Simulato) | Visualizzazione campi P.IVA e Ragione Sociale per Esercente. | ✅ PASS | Verifica UI dinamica. |
| **SYS-03** | Modifica Password (Mismatch) | Inserimento password diverse in 'Nuova' e 'Conferma'. | ⚠️ BLOCCO | Il sistema mostra alert warning (non invia dati). |
| **SYS-04** | Modifica Dati Valida | Modifica nome e click su "Salva". | ✅ SUCCESS | Feedback verde e persistenza al reload. |
| **SYS-05** | Sicurezza Accesso | Accesso diretto a `Profilo.html` senza login. | ⛔ REDIRECT | Reindirizzamento automatico a `index.html`. |

## 4. Test di Sicurezza 
Verifica specifica dei requisiti non funzionali.

| ID Test | Scenario di Attacco / Verifica | Comportamento Atteso (Backend) | Esito | 
| :--- | :--- | :--- | :--- | 
| **SEC-01** | **Bypass Client-Side**<br>Invio diretto via API (Postman) di P.IVA errata (3 cifre). | **HTTP 400 Bad Request**<br>Il server blocca la richiesta e restituisce errore JSON. | ✅ PASS |
| **SEC-02** | **Injection Scripting**<br>Tentativo di inserimento tag HTML nel campo Nome. | **Sanitizzazione / Rifiuto**<br>Il server rifiuta o pulisce l'input. | ✅ PASS |
| **SEC-03** | **Accesso Anonimo**<br>Chiamata API senza Header Authorization. | **HTTP 401 Unauthorized**<br>Nessun dato restituito. | ✅ PASS |
| **SEC-04** | **Token Falsificato**<br>Invio di un token con firma alterata o payload corrotto. | **HTTP 401 Unauthorized**<br>Accesso negato. | ✅ PASS | 

## 5. Come Lanciare i Test
Per lanciare i test, avviare i container (comando sul terminale: './start.sh') e scrivere nell'URL: http://localhost:8001/tests/profilo/test_runner.php, in output l'esito dei test automatizzati tramite lo script 'Test_Report.md'.