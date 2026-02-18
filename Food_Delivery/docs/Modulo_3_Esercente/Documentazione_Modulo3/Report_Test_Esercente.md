# Report di Collaudo e Verifica - Modulo 3: Gestione Esercente

## 1. Obiettivi della Verifica
L'attività di testing è stata finalizzata alla validazione della logica di business e delle componenti architetturali del modulo Esercente. In particolare, ci si è concentrati sulla correttezza dei design pattern implementati (Strategy e Factory), sul rispetto dei vincoli di integrità dei dati e sulla qualità dell'esperienza utente (UX).

## 2. Ambiente di Test
I test sono stati eseguiti in un ambiente controllato basato su container **Docker**, garantendo la parità tra l'ambiente di sviluppo e quello di verifica. Prima di procedere è necessario avviare lo script `start.sh`, successivamente per eseguire il test basta cercare da browser: "http://localhost:8001/esercente/test/Test_Esercente.php".
* **Backend**: Lo script di test è stato isolato per operare direttamente sulle classi PHP del Model, simulando chiamate API dirette per verificare la sicurezza.
* **Frontend**: Le verifiche sono state condotte simulando l'interazione utente reale per validare il pattern MVP e i vincoli visivi.

## 3. Riepilogo Risultati (Matrice di Tracciabilità)

| ID Test | Funzionalità Verificata | Ambito | Esito |
| :--- | :--- | :--- | :--- |
| **TEST 1** | Pattern Strategy Factory | Backend (Unit) | **PASS** |
| **TEST 2** | Validazione OTP (Security) | Backend (Unit) | **PASS** |
| **TEST 3** | Vincoli Stato Locale (API) | Backend (Security) | **PASS** |
| **TEST 4** | Analisi Stati (Backend) | Backend (Unit) | **PASS** |
| **TEST 5** | Integrazione Flussi Operativi | Sistema | **PASS** |
| **TEST 6** | Usabilità e UX | Frontend | **PASS** |

## 4. Dettaglio delle Esecuzioni Backend

### 4.1 Validazione Architetturale (Factory & Strategy)
Il test ha confermato che la classe `StrategyFactory` è in grado di risolvere correttamente le richieste del frontend, istanziando la strategia specifica richiesta (es. `AccettaStrategy`, `ProntoStrategy`, ecc.). Questo garantisce il rispetto del principio di singola responsabilità e facilita la manutenzione futura del codice.

### 4.2 Verifica del Protocollo di Sicurezza (OTP)
È stata testata la robustezza della classe `RitiratoStrategy` nella gestione dei codici di verifica:
* **Accettazione**: Il sistema valida correttamente i codici corrispondenti a quelli presenti nel database, gestendo correttamente la *case-insensitivity*.
* **Reiezione**: Tentativi di inserimento di codici errati generano eccezioni bloccanti, impedendo transizioni di stato non autorizzate.

### 4.3 Analisi dei Vincoli di Integrità (Security Layer)
È stata verificata la sicurezza delle API in caso di chiamate dirette (es. bypassando l'interfaccia grafica):
* **Stato Aperto**: Se viene forzata una richiesta di eliminazione (es. via Postman o script), il backend risponde correttamente con **403 Forbidden**, proteggendo il database.
* **Stato Chiuso**: Le operazioni CRUD sul catalogo sono accettate dal server.

## 5. Test di Integrazione e Flussi Operativi

In questa sezione vengono documentati i test sui flussi completi che simulano l'interazione tra l'utente, il Presenter e il database MySQL.

### 5.1 Test Ciclo di Vita dell'Ordine (Transizioni di Stato)
Verifica della corretta progressione di un ordine attraverso le colonne della Dashboard Kanban.
* **Scenario**: Un ordine in stato "ATTESA" viene accettato dall'esercente.
* **Azione**: Pressione del tasto "Accetta".
* **Risultato Atteso**: 
    1. Chiamata API verso `Set_Order_Status.php` con parametro `azione=accetta`.
    2. Il Model aggiorna lo stato sul DB a "PREPARAZIONE".
    3. Il Presenter sposta dinamicamente la card dalla prima alla seconda colonna.
* **Esito**: **PASS**. La transizione è atomica e coerente con il diagramma degli stati.

### 5.2 Test Gestione Catalogo: Aggiunta Piatto
Verifica dell'inserimento di nuove risorse nel menu.
* **Scenario**: Locale in stato "CHIUSO". L'esercente compila il form "Nuovo Piatto".
* **Azione**: Invio dei dati (Nome, Prezzo, Ingredienti, Categoria).
* **Risultato Atteso**: 
    1. Validazione lato client del formato prezzo (decimal).
    2. Inserimento nel DB con generazione automatica di un nuovo ID univoco.
    3. Aggiornamento istantaneo della tabella "Menu" senza ricaricare la pagina.
* **Esito**: **PASS**. L'integrità referenziale tra categorie e piatti è mantenuta.

### 5.3 Test Gestione Catalogo: Eliminazione (Soft-Delete)
Verifica della rimozione sicura di un elemento dal menu.
* **Scenario**: L'esercente decide di rimuovere un piatto non più disponibile (Locale CHIUSO).
* **Azione**: Click sull'icona "Elimina" e conferma nella modale di sicurezza.
* **Risultato Atteso**: 
    1. Esecuzione della query di cancellazione logica.
    2. La riga corrispondente scompare dalla View con un feedback visivo di conferma.
* **Esito**: **PASS**. Il sistema impedisce eliminazioni accidentali grazie alla doppia conferma.

### 5.4 Test di Sincronizzazione Multi-Client
Verifica della consistenza dei dati in scenari di utilizzo simultaneo.
* **Scenario**: L'esercente ha la dashboard aperta su due dispositivi diversi.
* **Azione**: Cambia lo stato di un ordine sul Dispositivo A.
* **Risultato Atteso**: Entro 2000ms (ciclo di polling), il Dispositivo B deve mostrare l'ordine nella nuova posizione senza alcun intervento manuale.
* **Esito**: **PASS**. La logica di sincronizzazione basata su timestamp garantisce l'allineamento dei client.

## 6. Test di Usabilità e User Experience (UX)

Oltre ai test funzionali, sono stati condotti test diretti sull'interfaccia frontend per verificare la responsività del pattern MVP e la qualità dell'esperienza utente.

### 6.1 Test di Persistenza del Focus (Polling Anti-Disturbo)
Il sistema esegue un polling ogni 2000ms per aggiornare gli ordini in tempo reale.
* **Scenario**: L'esercente seleziona un campo di input OTP in un ordine "Pronto" e inizia a digitare il codice.
* **Azione**: Durante la digitazione, scatta il timer di refresh del Presenter.
* **Risultato Atteso**: Il campo di input non deve perdere il focus e il testo inserito non deve essere sovrascritto dal refresh.
* **Esito**: **PASS**. Il controllo sullo stato di `:focus` dell'elemento garantisce la continuità operativa.

### 6.2 Test di Feedback Visivo e Prevenzione Errori (UI Lock)
Verifica della comunicazione tra lo stato del locale e le possibilità d'azione dell'utente.
* **Scenario**: Locale impostato su "APERTO".
* **Azione**: L'utente visualizza la sezione "Menu".
* **Risultato Atteso**:
    1. Il pulsante "+ NUOVO PIATTO" appare **visivamente disabilitato** (non cliccabile) con un tooltip informativo.
    2. I tasti "Elimina" sono inibiti (grayed-out) per prevenire l'azione all'origine.
* **Esito**: **PASS**. La View sincronizza lo stato degli elementi UI con le variabili del Presenter, prevenendo l'errore prima della chiamata API.

### 6.3 Test di Gestione degli Errori Critici (OTP)
Verifica della chiarezza del sistema in caso di fallimento di una transazione.
* **Scenario**: Inserimento di un codice di ritiro errato.
* **Azione**: Click sul tasto "Verifica" o pressione del tasto "Invio".
* **Risultato Atteso**: Comparsa immediata della modale di errore specifica con istruzioni chiare ("Riprova").
* **Esito**: **PASS**. L'eccezione sollevata dal backend viene catturata dal Model e triggerata correttamente dalla View tramite il Presenter.

### 6.4 Test di Navigazione Single Page (SPA)
Verifica della fluidità nel cambio di contesto operativo.
* **Azione**: Passaggio rapido tra i tab "Ordini", "Menu" e "Profilo".
* **Risultato Atteso**:
    1. Transizione fluida tramite effetti di `fadeIn/fadeOut` senza ricaricamento della pagina.
    2. Interruzione del polling degli ordini quando l'utente si sposta su altre sezioni per ottimizzare il traffico di rete.
* **Esito**: **PASS**. Il routing gestito dal Presenter orchestra correttamente la visibilità dei container HTML.

## 7. Conclusioni
La suite di test ha dato esito positivo per tutte le componenti verificate. L'architettura si è dimostrata solida e conforme alle specifiche tecniche. L'adozione di un approccio **"Defense in Depth"** (UI che previene l'azione + Backend che la blocca se forzata) garantisce un elevato livello di sicurezza e integrità dei dati.