# Progetto T21
# UniPr Food Delivery

Questo repository ospita il codice sorgente e l'infrastruttura per il sistema di gestione delle ordinazioni alimentari dell'Università di Parma. Il progetto adotta un'architettura a microservizi containerizzata per garantire la massima portabilità e coerenza dell'ambiente di sviluppo tra i vari collaboratori.

## Chi simao su GitHub:
* **Pocc04** -> **Andrea Poccetti**
* **FreddoBliset** -> **Mario Sale**
* **Patricale** -> **Pasquale Colucci**
* **barbagianni901** -> **Alessandro Di Stasi**


## Requisiti di Sistema

Per l'esecuzione e lo sviluppo del progetto è necessaria l'installazione dei seguenti strumenti:
* **Git**: per il controllo di versione.
* **Docker & Docker Compose**: per l'orchestrazione dei servizi applicativi.

---

## Guida all'Installazione e Avvio

Seguire i passaggi indicati di seguito per configurare l'ambiente di lavoro sulla propria macchina locale:

1. **Clonazione del Progetto** Scaricare i file sorgente tramite il comando:  
   `git clone https://github.com/Progetti-ING-SW-INFO-UniPR/INGSW-2526-T21.git`

2. **Accesso alla Directory** Posizionarsi nella cartella radice del progetto:  
   `cd food-delivery`

3. **Configurazione dei Permessi ed Esecuzione** L'abilitazione degli script varia in base al sistema operativo in uso:
   * **Linux e macOS**: Abilitare i permessi di esecuzione con il comando:  
     `chmod +x start.sh stop.sh`
   * **Windows**: Gli script `.sh` richiedono un terminale compatibile con Bash (consigliato **Git Bash** o **WSL**). In questi ambienti, è possibile utilizzare il comando `chmod` come su Linux. In alternativa, gli script possono essere eseguiti direttamente tramite il comando:  
     `sh start.sh`

4. **Avvio del Sistema** Eseguire lo script di automazione per compilare le immagini e attivare i container:  
   `./start.sh`

5. **Verifica dello Stato** Confermare che tutti i servizi siano operativi visualizzando l'elenco dei container attivi:  
   `docker ps`

6. **Accesso all'Applicazione** Aprire il browser web all'indirizzo:  
   `http://localhost:8000`

7. **Arresto del Sistema** Al termine della sessione di lavoro, arrestare i servizi e liberare le risorse di sistema tramite lo script:  
   `./stop.sh`

--- START OF TESTING GUIDE --

## Guida al Testing Integrale del Sistema - Food Delivery Campus

Questa guida descrive la procedura per testare l'intero ecosistema del progetto, coprendo i flussi del Cliente e dell'Esercente, i vincoli di sicurezza e la persistenza dei dati.

## 1. Credenziali di Test (da `init.sql`)

### Profilo Cliente (Studente UniPR)
* **Email:** `mario.rossi@studenti.unipr.it`
* **Password:** `password123`

### Profilo Esercente (Mensa Parco Ducale)
* **Email:** `info@mensa-unipr.it`
* **Password:** `password123`
* **ID Esercente:** 3

---

## 2. Missioni di Test (Flusso Completo)

### Missione A: Il Cliente effettua un ordine
1.  **Login:** Vai su `index.html` e accedi come **Mario Rossi**.
2.  **Navigazione:** Dovresti vedere la lista dei ristoranti. Seleziona **"Mensa Parco Ducale"**.
3.  **Dettagli Ordine:** Inserisci una nota e conferma l'ordine.
4.  **Carrello:** Scegli alcuni prodotti (es. Margherita, Cocacola) e clicca su **"Ordina Ora"**.
5.  **Verifica Stato:** Verrai reindirizzato allo **Storico**. L'ordine appena creato deve apparire nello stato **"ATTESA"**.

### Missione B: L'Esercente gestisce l'ordine
1.  **Login:** Apri una nuova finestra (o usa il Logout) e accedi come **Mensa Parco Ducale**.
2.  **Dashboard:** Grazie al **polling automatico**, dovresti vedere l'ordine di Mario Rossi apparire nella colonna **"Ordini in arrivo"**.
3.  **Visualizzazione Note:** Verifica che il box delle note mostri correttamente il messaggio inserito dal cliente.
4.  **Flusso di Lavoro (Pattern Strategy):**
    * Clicca su **"Accetta"**: L'ordine si sposta in **"In preparazione"**.
    * (Opzionale) Torna nel browser del Cliente: lo stato si sarà aggiornato in tempo reale.
    * Clicca su **"Pronto"**: L'ordine si sposta in **"Pronto per il ritiro"**.

### 🔑 Missione C: Verifica Sicurezza e Consegna (OTP)
1.  **Recupero Codice:** Torna nella vista del Cliente. Sulla card dell'ordine pronto apparirà il codice univoco (es: `A1B2C3`).
2.  **Consegna:** Lato Esercente, clicca su **"Consegna"**.
3.  **Test Errore:** Inserisci un codice sbagliato. Il sistema deve bloccare l'azione mostrando un feedback di errore.
4.  **Test Successo:** Inserisci il codice corretto. L'ordine viene rimosso dalla dashboard attiva dell'esercente e passa nello stato finale **"Ritirato"**.

### Missione D: Gestione Menù e Vincoli (Modulo 3)
1.  **Locale Aperto:** Assicurati che l'esercente sia in stato **"APERTO"**. Prova ad eliminare un prodotto dal menu. Il sistema deve impedirlo.
2.  **Locale Chiuso:** Cambia lo stato in **"CHIUSO"**. Ora prova ad aggiungere o eliminare un prodotto. L'operazione deve essere consentita.
3.  **Disponibilità:** Prova a disabilitare un prodotto (toggle) mentre il locale è aperto. Questo deve funzionare sempre.

### Missione E: Profilo e Storico (Modulo 4)
1.  **Modifica Profilo:** Vai nella sezione **"Profilo"** (sia come cliente che come esercente) e modifica la password o i dati di contatto. Verifica che al ricaricamento i dati siano persistenti.
2.  **Filtri Storico:** Nello **Storico Ordini** del cliente, usa i filtri (Attivi, Passati, Tutti) per verificare che la logica di filtraggio lato server funzioni correttamente.

--- END OF TESTING GUIDE --

## Note Importanti

**Persistenza**: I dati salvati nel database sono mantenuti tramite volumi Docker; l'arresto dei container non comporta la perdita delle informazioni registrate.

**Ambiente Windows**: Per una corretta esecuzione degli script di gestione, si raccomanda l'utilizzo di Git Bash, incluso nell'installazione standard di Git per Windows.