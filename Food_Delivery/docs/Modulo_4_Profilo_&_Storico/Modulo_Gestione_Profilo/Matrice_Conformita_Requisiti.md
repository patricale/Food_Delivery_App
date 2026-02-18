# Matrice di Conformità ai Requisiti 

## SVOLTO DA COLUCCI PASQUALE, MATR: 358141 ##

Il presente documento certifica la corrispondenza tra i requisiti iniziali di progetto e l'effettiva implementazione software del modulo **Gestione Profilo**.
L'analisi dimostra che il sistema soddisfa pienamente il 100% dei Requisiti Funzionali (RF) e Non Funzionali (RNF) richiesti, garantendo robustezza, sicurezza e aderenza agli standard architetturali definiti (MVP, Stateless, Docker).

Di seguito viene riportata la mappatura puntuale tra ciascun requisito e la componente software (File/Classe/Funzione) che lo implementa.

---

### 1. Requisiti Funzionali (RF)

| ID | Descrizione Requisito | Stato | Implementazione Tecnica (Evidenza nel Codice) |
| --- | --- | --- | --- |
| **RF-1.3.1** | Recupero e visualizzazione dati anagrafici utente loggato. | ✅ | **API:** `GET /api/profilo/get_profile.php`. <br>**JS:** `profile.js` (metodo `loadProfileData`) popola i campi HTML al caricamento. |
| **RF-1.3.2** | Modifica campi informativi (es. password) con sovrascrittura. | ✅ | **API:** `POST /api/profilo/update_profile.php`<br>**JS:** `handleUpdateSubmit` invia il payload JSON con i nuovi dati. |
| **RF-1.3.3** | Validazione formale dei nuovi dati prima del salvataggio. | ✅ | **Backend:** `update_profile.php` verifica regex P.IVA e lunghezza password.<br>**Frontend:** `profile.js` controlla che le due password coincidano. |
| **RF-1.3.4** | Aggiornamento persistente per sessioni future. | ✅ | **DB:** Le query `UPDATE` in `UserProfileRepo.php` committano le modifiche sul database MySQL persistente. |
| **RF-1.3.5** | Feedback visivo esplicito al termine (conferma). | ✅ | **UI:** Box Alert in cima al form.<br>**JS:** `showFeedback()` mostra messaggi verdi (Successo) o rossi (Errore). |
| **RF-2.1.1** | Visualizzazione dati Esercente (Nome, Descrizione, Indirizzo). | ✅ | **JS:** `toggleMerchantFields` mostra il blocco `#merchantFields` se il ruolo è 'esercente', nascondendo i campi studente. |
| **RF-2.1.2** | Modifica campi Esercente (Nome, Descrizione, Indirizzo). | ✅ | **API:** `UserProfileRepo::updateMerchantProfile` esegue l'update mirato sulla tabella `ESERCENTE`. |
| **RF-2.1.5** | Blocco salvataggio se campi obbligatori vuoti. | ✅ | **Backend:** `update_profile.php` restituisce `400 Bad Request` se mancano campi marcati come `required`. |
| **RF-2.1.6** | Aggiornamento in tempo reale lista pubblica. | ✅ | Il DB è centralizzato: una modifica in `update_profile.php` è immediatamente leggibile dalle API di listaggio (Lato Cliente). |
| **RF-1.10.5** | Messaggi di errore specifici per campo non valido. | ✅ | **API:** Restituisce JSON dettagliato (es. `{"message": "P.IVA non valida"}`).<br>**JS:** Mostra l'errore specifico nell'alert box. |

---

### 2. Requisiti Non Funzionali (RNF)

| ID | Descrizione Requisito | Stato | Evidenza nel Codice (File/Percorso) |
| --- | --- | --- | --- |
| **RNF-1.1** | **No Framework** (PHP Nativo + JS/jQuery). | ✅ | Codice PHP puro senza librerie esterne. Frontend usa solo jQuery e Bootstrap. |
| **RNF-1.2** | **Pattern MVP** (Model-View-Presenter). | ✅ | **M**: `UserProfileRepo.php`<br>**V**: `Profilo.html` (Passiva)<br>**P**: `profile.js` (Logica) |
| **RNF-1.3** | **API REST & JSON**. | ✅ | Tutti gli endpoint usano `header('Content-Type: application/json')` e verbi HTTP standard. |
| **RNF-1.4** | **No Server-Side Rendering**. | ✅ | PHP restituisce solo dati JSON grezzi. L'HTML è statico e popolato via JS. |
| **RNF-1.5** | **Docker Container**. | ✅ | Progetto avviabile via `docker-compose up` (vedi `docker-compose.yml`). |
| **RNF-1.6** | **Gestione GitHub**. | ✅ | Uso di branch dedicati (`fix/`, `develop`) e Pull Request. |
| **RNF-2.1** | **Stateless Auth (Token)**. | ✅ | Nessuna sessione PHP (`$_SESSION`). Token JWT inviato nell'header `Authorization`. |
| **RNF-2.2** | **Password Hashing**. | ✅ | Uso di `password_hash($pwd, PASSWORD_DEFAULT)` in `update_profile.php`. |
| **RNF-2.3** | **Header Authorization**. | ✅ | `get_profile.php` e `update_profile.php` verificano la presenza del Bearer Token. |
| **RNF-3.1** | **Responsive Design**. | ✅ | Layout basato su griglia Bootstrap (`col-md-6`, `d-none d-md-block`) in `Profilo.html`. |
| **RNF-3.2** | **Feedback Visivo**. | ✅ | Implementato tramite metodo `showFeedback()` in `profile.js`. |
| **RNF-4.1** | **Consistenza Dati**. | ✅ | Transazioni Database gestite in `UserProfileRepo.php` (`beginTransaction`/`commit`). |
| **RNF-4.2** | **Validazione Ridondante**. | ✅ | **CRUCIALE:** Il backend riesegue regex e controlli lunghezza, ignorando la validazione JS. |
| **RNF-5.1** | **No Transazioni Monetarie**. | ✅ | Il sistema non gestisce pagamenti elettronici; solo logica informativa. |