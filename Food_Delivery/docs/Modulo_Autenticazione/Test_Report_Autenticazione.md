# 🛡️ Test Report: Modulo Autenticazione & Infrastruttura

| **Meta-Dati** | **Dettagli** |
| :--- | :--- |
| 👤 **Autore** | Sale Mario (Matr. 364432) |
| 📅 **Data** | 10/02/2026 |
| 🏗️ **Ambiente** | Docker (PHP 8.0, MySQL 8.0) |
| 📦 **Modulo** | Auth |

---

## 1. 📊 Riepilogo Esecutivo

| Totale Test | ✅ Superati | ❌ Falliti | ⚠️ Skipped | Copertura Funzionale |
| :---: | :---: | :---: | :---: | :---: |
| **18** | **18** | **0** | **0** | **100%** |

---
 * 🔗 VISUALIZZA I RISULTATI QUI:
 * http://localhost:8001/tests/auth/test_auth_runner.php


## 2. ⚙️ Test Backend (White Box)
*Verifica delle logiche interne, sicurezza dei dati e integrità del database.*

| ID Test | Componente | Descrizione del Test | Requisito | Risultato Atteso | Esito |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **AUTH-01** | `Docker` | **Connettività Database** | *RNF-1.5* | Il container PHP deve comunicare con l'host `db` senza eccezioni `PDOException`. | 🟢 **PASS** |
| **AUTH-02** | `Security` | **Hashing Password** | *RNF-2.2* | Le password nel DB devono iniziare con `$2y$` (Bcrypt) e non essere in chiaro. | 🟢 **PASS** |
| **AUTH-03** | `Model` | **Reg. Studente** | *RF-1.10.1* | Registrando `user@studenti.unipr.it`, il campo `matricola` deve essere popolato. | 🟢 **PASS** |
| **AUTH-04** | `Model` | **Reg. Docente** | *RF-1.10.2* | Registrando `doc@unipr.it`, il campo `matricola` deve essere `NULL`. | 🟢 **PASS** |
| **AUTH-05** | `Controller` | **Gestione Duplicati** | *RF-1.10.3* | Tentativo di registrazione con email già presente restituisce HTTP `409 Conflict`. | 🟢 **PASS** |
| **AUTH-06** | `Validator` | **Dominio Email** | *RF-1.10.2* | Email non universitarie (es. `@gmail.com`) devono restituire HTTP `400 Bad Request`. | 🟢 **PASS** |
| **AUTH-07** | `Security` | **Transazioni DB** | *RNF-2.3* | In caso di errore durante la registrazione, il DB deve rollback (nessun utente parziale creato). | 🟢 **PASS** |

---

## 3. 🖥️ Test Frontend (Black Box - UX/UI)
*Verifica dell'interfaccia utente, reattività e routing.*

| ID Test | Componente | Scenario Utente | Requisito | Risultato Atteso | Esito |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **SYS-01** | `View` | **Input Studente** | *RF-1.10.1, RF-1.10.2* | Digitando `@studenti` nel campo email, il campo Matricola **appare** con animazione. | 🟢 **PASS** |
| **SYS-02** | `View` | **Input Docente** | *RF-1.10.1, RF-1.10.2* | Digitando `@unipr` nel campo email, il campo Matricola **scompare** e si resetta. | 🟢 **PASS** |
| **SYS-03** | `Routing` | **Login Esercente** | *RF-1.0.1* | Login con credenziali Esercente -> Redirect automatico a `Esercente.html`. | 🟢 **PASS** |
| **SYS-04** | `Routing` | **Login Cliente** | *RF-1.0.1* | Login con credenziali Studente -> Redirect automatico a `Cliente.html`. | 🟢 **PASS** |
| **SYS-05** | `Security` | **Protezione Rotte** | *RNF-2.3* | Accesso diretto via URL a pagine protette (senza Token) -> Redirect a `index.html`. | 🟢 **PASS** |
| **SYS-06** | `Factory` | **Creazione Viste** | *RNF-1.2* | `AuthViewFactory` crea correttamente `LoginView` e `RegisterUniPRView` in base al parametro. | 🟢 **PASS** |
| **SYS-07** | `Interfaccia` | **Contratto IAuthView** | *RNF-1.2* | `LoginView` e `RegisterUniPRView` implementano correttamente tutti i metodi di `IAuthView`. | 🟢 **PASS** |
| **SYS-08** | `View` | **Validazione Password Frontend** | *RF-1.10.5* | Inserendo password <8 o >72 caratteri, compare errore specifico sotto il campo. | 🟢 **PASS** |

---

## 4. 🧪 Test Automatizzati (Integration)
*Test eseguiti tramite script `test_auth_runner.php` che opera direttamente sul database.*

| ID Test | Componente | Descrizione del Test | Requisito | Risultato Atteso | Esito |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **INT-01** | `Database` | **Connettività Database** | *RNF-1.5* | Connessione al database stabilita e query di test eseguita con successo. | 🟢 **PASS** |
| **INT-02** | `Validator` | **Validazione Dominio Email** | *RF-1.10.2* | Domini @studenti.unipr.it e @unipr.it sono accettati, altri domini sono rifiutati. | 🟢 **PASS** |
| **INT-03** | `Validator` | **Validazione Lunghezza Password** | *RF-1.10.5* | Password <8 o >72 caratteri vengono rilevate, password 8-72 sono valide. | 🟢 **PASS** |
| **INT-04** | `Auth` | **Registrazione Utente** | *RF-1.10.1* | Utente con email @studenti.unipr.it viene registrato correttamente con matricola. | 🟢 **PASS** |
| **INT-05** | `Auth` | **Auto-login dopo Registrazione** | *RF-1.10.6* | Dopo registrazione, viene generato un token JWT valido per l'auto-login. | 🟢 **PASS** |
| **INT-06** | `Auth` | **Controllo Unicità Email** | *RF-1.10.3, RF-1.10.4* | Email già registrata viene rilevata, email nuova è accettata. | 🟢 **PASS** |
| **INT-07** | `Auth` | **Login con Credenziali Valide** | *RF-1.0.1* | Credenziali corrette permettono il login e generano token JWT. | 🟢 **PASS** |
| **INT-08** | `Auth` | **Login con Credenziali Non Valide** | *RF-1.0.2* | Password errata viene rifiutata. | 🟢 **PASS** |
| **INT-09** | `Security` | **Generazione Token JWT** | *RNF-2.1* | Token JWT generato ha formato corretto (3 parti) e lunghezza adeguata. | 🟢 **PASS** |
| **INT-10** | `Cleanup` | **Pulizia Dati di Test** | *N/A* | Utente di test viene eliminato correttamente dal database. | 🟢 **PASS** |

---

## 5. 📝 Note di Collaudo

> **Nota Tecnica:** Tutti i test sono stati eseguiti in ambiente isolato Docker resettando il volume del database (`docker-compose down -v`) prima di ogni sessione per garantire l'idempotenza dei dati.

* **Strumenti usati:** 
  - Browser Chrome (DevTools) per test frontend
  - Postman per test API Backend
  - **Script automatizzato `tests/auth/test_auth_runner.php` per test di integrazione**
  - PHPLogs, Docker logs

* **Pattern Testati:** MVP, Interfaccia Astratta, Factory Method, Builder Pattern, Transazioni DB

* **Copertura Requisiti:**
  - ✅ RF-1.0.1: Login e redirect
  - ✅ RF-1.0.2: Feedback credenziali non valide
  - ✅ RF-1.10.1: Registrazione con dati completi
  - ✅ RF-1.10.2: Validazione dominio email UniPR
  - ✅ RF-1.10.3: Controllo unicità email
  - ✅ RF-1.10.4: Feedback email duplicata
  - ✅ RF-1.10.5: Validazione password (min 8, max 72)
  - ✅ RF-1.10.6: Auto-login dopo registrazione

* **Ultima esecuzione:** 10/02/2026 alle ore 14:34

---

## 6. 📊 Riepilogo per Requisito

| Requisito | Descrizione | Test Copertura | Esito |
| :--- | :--- | :--- | :---: |
| **RF-1.0.1** | Accesso e redirect in base al ruolo | SYS-03, SYS-04, INT-07 | ✅ |
| **RF-1.0.2** | Feedback credenziali non valide | INT-08 | ✅ |
| **RF-1.10.1** | Registrazione con dati completi | AUTH-03, INT-04 | ✅ |
| **RF-1.10.2** | Validazione dominio email | AUTH-04, AUTH-06, SYS-01, SYS-02, INT-02 | ✅ |
| **RF-1.10.3** | Unicità email | AUTH-05, INT-06 | ✅ |
| **RF-1.10.4** | Feedback email duplicata | AUTH-05, INT-06 | ✅ |
| **RF-1.10.5** | Validazione formale dati (password) | SYS-08, INT-03 | ✅ |
| **RF-1.10.6** | Auto-login dopo registrazione | INT-05, INT-09 | ✅ |
| **RNF-1.2** | Pattern MVP | SYS-06, SYS-07 | ✅ |
| **RNF-1.5** | Docker Environment | AUTH-01, INT-01 | ✅ |
| **RNF-2.1** | Stateless Auth (JWT) | INT-09 | ✅ |
| **RNF-2.2** | Password Hashing | AUTH-02 | ✅ |
| **RNF-2.3** | Protezione API e Transazioni | AUTH-07 | ✅ |

---

## 7. ✅ Conclusione

Tutti i test relativi al modulo **Autenticazione** sono stati eseguiti con successo.  
Il modulo rispetta tutti i requisiti funzionali e non funzionali assegnati, inclusi:

- **RF-1.10.5**: Validazione password con messaggi specifici per campo
- **RF-1.10.6**: Auto-login dopo registrazione con generazione token JWT

**Il modulo è pronto per l'integrazione con il resto del sistema.** 🚀