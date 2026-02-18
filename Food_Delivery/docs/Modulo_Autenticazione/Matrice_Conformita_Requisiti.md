# ✅ Matrice di Conformità ai Requisiti

| Meta-Dati | Dettagli |
| :--- | :--- |
| 👤 **Autore** | Sale Mario (Matr. 364432) |
| 📅 **Data** | 10/02/2026 |
| 📊 **Copertura** | 100% Requisiti Assegnati |

Di seguito la mappatura puntuale tra i requisiti ufficiali (Analisi v1.1) e l'implementazione del modulo **Autenticazione**.

### 🟢 Requisiti Funzionali (RF)

| ID | Descrizione Breve | Implementazione Modulo Auth | Stato |
| :--- | :--- | :--- | :---: |
| **RF-1.0.1** | Accesso e Redirect | `AuthPresenter.js` reindirizza in base al ruolo nel JWT (`esercente`→Esercente.html, `studente/docente`→Cliente.html). | ✅ |
| **RF-1.0.2** | Feedback errore login | `LoginView.js` mostra alert rosso (`showError()`) su errore. | ✅ |
| **RF-1.10.1** | Registrazione dati | Form completo in `RegisterUniPRView.js` con campi: nome, cognome, email, password, matricola (condizionale). | ✅ |
| **RF-1.10.2** | Validazione dominio | Backend (`register.php`) accetta solo whitelist UniPR (`@studenti.unipr.it`, `@unipr.it`). | ✅ |
| **RF-1.10.3** | Unicità email | Verifica preventiva nel DB (`SELECT id_utente FROM UTENTE`) prima dell'INSERT. | ✅ |
| **RF-1.10.4** | Feedback duplicati | Errore HTTP `409 Conflict` gestito dal frontend (`showError()`). | ✅ |
| **RF-1.10.5** | Validazione formale | Controlli HTML5 `required` e PHP `empty()` + validazione JSON input. | ✅ |
| **RF-1.10.6** | Auto-login | Redirect automatico a `index.html` dopo registrazione `201`. | ✅ |

### 🔵 Requisiti Non Funzionali (RNF)

| ID | Descrizione Breve | Implementazione Modulo Auth | Stato |
| :--- | :--- | :--- | :---: |
| **RNF-1.1** | No Framework | PHP Nativo e JS Vanilla (ES6) senza librerie esterne. | ✅ |
| **RNF-1.2** | Pattern MVP | Struttura completa `Model` - `View` - `Presenter` con interfaccia `IAuthView` e Factory Method. | ✅ |
| **RNF-1.3** | API JSON RESTful | Scambio dati via JSON headers (`Content-Type: application/json`). | ✅ |
| **RNF-1.5** | Docker Environment | `docker-compose` con Apache e MySQL + script `init.sql`. | ✅ |
| **RNF-2.1** | Stateless Auth | Token JWT (no `$_SESSION`) con payload contenente ruolo utente. | ✅ |
| **RNF-2.2** | Password Hashing | Standard `password_hash` (Bcrypt) e `password_verify`. | ✅ |
| **RNF-2.3** | Transazioni DB | `BEGIN TRANSACTION` / `COMMIT` / `ROLLBACK` in `register.php`. | ✅ |