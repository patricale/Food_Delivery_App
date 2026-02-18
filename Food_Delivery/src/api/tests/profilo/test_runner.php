<?php

// SVOLTO DA COLUCCI PASQUALE, MATR. 358141

/**
 * File: test_runner.php
 * Descrizione: Script di Automated Integration Testing per il modulo Profilo.
 * Esegue test sequenziali sulle operazioni CRUD del Repository.
 * USAGE: Aprire http://localhost:8001/tests/profilo/test_runner.php
 */

header("Content-Type: text/plain; charset=UTF-8");
require_once __DIR__ . '/../../profilo/UserProfileRepo.php';

class ProfileTestRunner {
    private $repo;

    public function __construct() {
        echo "--- INIZIO TEST AUTOMATIZZATI ---\n";
        try {
            // Qui viene testata indirettamente la connessione al DB (Database::getInstance)
            $this->repo = new UserProfileRepository();
            $this->log("PASS", "Istanza UserProfileRepository creata e connessione DB OK.");
        } catch (Exception $e) {
            $this->log("FAIL", "Impossibile istanziare il Repository: " . $e->getMessage());
            die();
        }
    }

    private function log($status, $message) {
        echo "[$status] $message\n";
    }

    private function assert($condition, $message) {
        if ($condition) {
            $this->log("PASS", $message);
        } else {
            $this->log("FAIL", $message);
        }
    }

    public function run() {
        // Esegue la suite di test sequenziale
        $this->testFetchStudent();
        $this->testUpdateCycle();
        $this->testPasswordUpdate();
    }

    // TEST 1: Recupero Dati Studente (Lettura)
    private function testFetchStudent() {
        echo "\n[TEST 1] Verifica Recupero Dati (ID Test: 2 - Giulia)\n";
        
        // Usiamo ID 2 perché ID 1 spesso è admin o non esiste in init.sql
        $data = $this->repo->getUserById(2); 
        
        $this->assert(!empty($data), "Dati recuperati dal DB.");
        
        // Controllo robustezza: se l'ID 2 non esiste, il test non deve crashare ma fallire l'assert
        if ($data) {
            $this->assert($data['ruolo'] === 'studente', "Il ruolo è corretto (studente).");
            $this->assert(isset($data['matricola']), "Campo matricola presente.");
        } else {
            $this->log("WARN", "Utente ID 2 non trovato nel DB. Verificare init.sql.");
        }
    }

    // TEST 2: Ciclo Modifica -> Verifica -> Ripristino (Anagrafica)
    private function testUpdateCycle() {
        echo "\n[TEST 2] Ciclo Modifica e Ripristino Anagrafica\n";

        $testId = 2; // Giulia
        $originalData = $this->repo->getUserById($testId);
        
        if (!$originalData) {
            $this->log("SKIP", "Impossibile eseguire il test 2: Utente non trovato.");
            return;
        }

        $originalName = $originalData['nome'];
        $tempName = "TEST_AUTO_" . rand(1000, 9999);

        // A. Modifica
        $updateSuccess = $this->repo->updateClienteData($testId, $tempName, $originalData['cognome']);
        $this->assert($updateSuccess, "Update anagrafica eseguito.");

        // B. Verifica persistenza
        $newData = $this->repo->getUserById($testId);
        $this->assert($newData['nome'] === $tempName, "Il nome nel DB è cambiato in: $tempName");

        // C. Ripristino (Rollback manuale per non sporcare il DB)
        $restoreSuccess = $this->repo->updateClienteData($testId, $originalName, $originalData['cognome']);
        $this->assert($restoreSuccess, "Rollback dati originali completato.");
    }

    // TEST 3: Aggiornamento Password (Sicurezza)
    private function testPasswordUpdate() {
        echo "\n[TEST 3] Verifica Aggiornamento Password\n";
        
        $testId = 2;
        $tempPass = "nuova_password_sicura_123";
        $originalPass = "password"; // Valore di default in init.sql per ID 2

        // A. Modifica Password
        $success = $this->repo->updateAccountPassword($testId, $tempPass);
        $this->assert($success, "Query update password eseguita.");

        // B. Ripristino
        $restore = $this->repo->updateAccountPassword($testId, $originalPass);
        $this->assert($restore, "Password ripristinata al valore originale.");
    }
}

// Esecuzione
$testRunner = new ProfileTestRunner();
$testRunner->run();
echo "\n--- FINE TEST ---";

?>