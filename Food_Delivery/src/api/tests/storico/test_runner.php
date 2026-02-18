<?php

// SVOLTO DA COLUCCI PASQUALE, MATR. 358141

/**
 * File: test_runner.php
 * Descrizione: Script di Automated Integration Testing per il modulo Storico Ordini.
 * Verifica il corretto funzionamento del Repository e delle Strategy di filtraggio.
 * USAGE: Aprire http://localhost:8001/tests/storico/test_runner.php
 */

header("Content-Type: text/plain; charset=UTF-8");

// 1. INCLUSIONE DIPENDENZE 
require_once __DIR__ . '/../../utils/Database.php'; 
require_once __DIR__ . '/../../storico/OrderHistoryRepository.php';

// Inclusione delle Strategie Concrete
require_once __DIR__ . '/../../storico/patterns/strategy/AllOrdersStrategy.php';
require_once __DIR__ . '/../../storico/patterns/strategy/ActiveOrdersStrategy.php';
require_once __DIR__ . '/../../storico/patterns/strategy/PastOrdersStrategy.php';

class HistoryTestRunner {
    private $repo;
    private $testUserId = 2; // ID di Giulia Bianchi (Utente di Test)

    public function __construct() {
        echo "--- INIZIO TEST AUTOMATIZZATI (MODULO STORICO) ---\n";
        echo "Target User ID: " . $this->testUserId . "\n\n";

        try {
            // Creiamo la connessione al DB e la passiamo al Repository (Dependency Injection)
            $database = new Database();
            $this->repo = new OrderHistoryRepository($database);
            
            $this->log("PASS", "Istanza OrderHistoryRepository creata con successo (DB Connected).");
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
        $this->testFetchAll();
        $this->testActiveOrdersFilter();
        $this->testPastOrdersFilter();
        $this->testDataStructure();
    }

    // TEST 1: Recupero Totale
    private function testFetchAll() {
        echo "\n[TEST 1] Fetch All Orders (Strategy: AllOrdersStrategy)\n";
        
        try {
            $strategy = new AllOrdersStrategy();
            $orders = $this->repo->getOrders($strategy, $this->testUserId);
            
            $count = count($orders);
            $this->assert(is_array($orders), "Il metodo restituisce un array.");
            $this->assert($count > 0, "Trovati $count ordini totali nel DB.");
        } catch (Exception $e) {
            $this->log("FAIL", "Eccezione durante il fetch: " . $e->getMessage());
        }
    }

    // TEST 2: Verifica Filtro Ordini Attivi
    private function testActiveOrdersFilter() {
        echo "\n[TEST 2] Verifica Logica Filtro 'Attivi' (ActiveOrdersStrategy)\n";

        $strategy = new ActiveOrdersStrategy();
        $orders = $this->repo->getOrders($strategy, $this->testUserId);
        
        $hasError = false;

        foreach ($orders as $o) {
            $stato = strtolower($o['stato']);
            $invalidStates = ['ritirato', 'rifiutato', 'non_ritirato', 'nonritritato', 'annullato'];
            
            if (in_array($stato, $invalidStates)) {
                $hasError = true;
                $this->log("FAIL", "Trovato ordine ID {$o['id']} con stato '$stato' dentro la lista Attivi!");
            }
        }

        if (!$hasError) {
            $this->assert(true, "Nessun ordine concluso trovato nella lista Attivi.");
        }
    }

    // TEST 3: Verifica Filtro Ordini Passati
    private function testPastOrdersFilter() {
        echo "\n[TEST 3] Verifica Logica Filtro 'Passati' (PastOrdersStrategy)\n";

        $strategy = new PastOrdersStrategy();
        $orders = $this->repo->getOrders($strategy, $this->testUserId);

        $allCorrect = true;
        foreach ($orders as $o) {
            $stato = strtolower($o['stato']);
            $validPastStates = ['ritirato', 'rifiutato', 'nonritirato'];
            
            if (!in_array($stato, $validPastStates)) {
                $allCorrect = false;
                $this->log("FAIL", "Trovato ordine ID {$o['id']} con stato '$stato' (NON concluso) nella lista Passati.");
            }
        }

        $this->assert($allCorrect, "Tutti gli ordini nella lista 'Passati' hanno stati conclusivi.");
    }

    // TEST 4: Integrità Struttura Dati
    private function testDataStructure() {
        echo "\n[TEST 4] Controllo Integrità Campi JSON\n";
        
        $strategy = new AllOrdersStrategy();
        $orders = $this->repo->getOrders($strategy, $this->testUserId);
        
        if (!empty($orders)) {
            $sample = $orders[0];
            $this->assert(isset($sample['id']), "Campo 'id' presente.");
            $this->assert(isset($sample['totale']), "Campo 'totale' presente.");
            $this->assert(isset($sample['ristorante_nome']), "Campo 'ristorante_nome' presente.");
            $this->assert(isset($sample['dettagli']), "Campo 'dettagli' presente.");
        } else {
            $this->log("WARN", "Impossibile testare struttura: nessun ordine.");
        }
    }
}

// ESECUZIONE (Pattern standard: Istanzia -> Esegui -> Fine)
$runner = new HistoryTestRunner();
$runner->run();
echo "\n--- FINE TEST ---\n";
?>