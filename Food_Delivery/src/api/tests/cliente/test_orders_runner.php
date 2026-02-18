<?php
/**
 * TEST RUNNER - MODULO 2: ORDINI (Versione Plain Text)
 * Di Stasi Alessandro 358140
 * Percorso: http://localhost:8001/tests/cliente/test_orders_runner.php
 */

// 1. Impostiamo l'header per dire al browser che questo è TESTO SEMPLICE, non HTML
header("Content-Type: text/plain; charset=UTF-8");

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/JwtUtils.php';

echo "=== ESECUZIONE TEST: MODULO ORDINI ===\n\n";

try {
    // ---------------------------------------------------------
    // STEP 1: VERIFICA CONNESSIONE DB
    // ---------------------------------------------------------
    $database = new Database();
    $db = $database->getConnection();
    echo "[OK] Connessione al Database stabilita.\n";

    // ---------------------------------------------------------
    // STEP 2: PREPARAZIONE TOKEN (Utente Giulia Bianchi ID: 2)
    // ---------------------------------------------------------
    $testUserId = 2;
    $payload = ['id' => $testUserId, 'ruolo' => 'cliente'];
    $testToken = JwtUtils::generateToken($payload);
    
    echo "[INFO] Token JWT generato per utente ID: $testUserId\n";

    // ---------------------------------------------------------
    // STEP 3: CHIAMATA HTTP INTERNA (Loopback request)
    // ---------------------------------------------------------
    // Nota: Usiamo localhost:80 perché siamo dentro il container Docker
    $apiUrl = "http://localhost:80/storico/get_history.php?filter=all";
    
    echo "[INFO] Invio richiesta GET a: $apiUrl\n";

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "Authorization: Bearer " . $testToken . "\r\n" .
                        "Accept: application/json\r\n",
            "ignore_errors" => true // Cattura anche le risposte 401/500 senza crashare
        ]
    ];

    $context = stream_context_create($opts);
    $rawResponse = file_get_contents($apiUrl, false, $context);

    if ($rawResponse === FALSE) {
        throw new Exception("Impossibile contattare l'API interna.");
    }

    // ---------------------------------------------------------
    // STEP 4: ANALISI RISPOSTA
    // ---------------------------------------------------------
    $data = json_decode($rawResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "\n[ERRORE FATALE] La risposta non è un JSON valido.\n";
        echo "Raw Output:\n" . $rawResponse . "\n";
        exit;
    }

    if (isset($data['records'])) {
        $count = count($data['records']);
        echo "\n[SUCCESSO] Test Superato. L'API ha risposto correttamente.\n";
        echo "-------------------------------------------------------\n";
        echo "Numero ordini trovati: $count\n";
        
        if ($count > 0) {
            $firstOrder = $data['records'][0];
            echo "Dettagli primo ordine:\n";
            echo " - Ristorante: " . ($firstOrder['ristorante'] ?? 'N/D') . "\n";
            echo " - Data:       " . ($firstOrder['data'] ?? 'N/D') . "\n";
            echo " - Totale:     " . ($firstOrder['totale'] ?? 'N/D') . "\n";
            echo " - Stato:      " . ($firstOrder['stato'] ?? 'N/D') . "\n";
        }
        echo "-------------------------------------------------------\n";
    } else {
        echo "\n[FALLITO] La chiave 'records' non è presente nella risposta.\n";
        echo "Messaggio dal server: " . ($data['message'] ?? 'Nessun messaggio') . "\n";
        echo "Risposta completa:\n";
        print_r($data);
    }

} catch (Exception $e) {
    echo "\n[ECCEZIONE] " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
?>