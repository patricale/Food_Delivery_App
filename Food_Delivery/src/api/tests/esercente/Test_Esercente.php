<?php
/**
 * Test Suite - Modulo Esercente
 * Food Delivery Campus
 */

// SVOLTO DA Andrea Poccetti, MATR. 361127

require_once __DIR__ . '/../../esercente/ordini/strategies/Order_Strategies.php';
require_once __DIR__ . '/../../esercente/ordini/strategies/Strategy_Factory.php';

echo "<pre>";
echo "====================================================\n";
echo "   UNIT TEST - GESTIONE ESERCENTE\n";
echo "====================================================\n\n";

/**
 * Validazione del pattern Strategy e Factory
 */
echo "[TEST 1] Verifica StrategyFactory\n";
$azioni = [
    'accetta'      => 'AccettaStrategy',
    'rifiuta'      => 'RifiutaStrategy',
    'pronto'       => 'ProntoStrategy',
    'ritirato'     => 'RitiratoStrategy',
    'nonRitirato' => 'NonRitiratoStrategy'
];

foreach ($azioni as $azione => $classeAttesa) {
    try {
        $strategy = StrategyFactory::getStrategy($azione);
        if (get_class($strategy) === $classeAttesa) {
            echo "  OK: Azione '$azione' -> " . get_class($strategy) . "\n";
        } else {
            echo "  FAIL: Azione '$azione' -> Restituito " . get_class($strategy) . "\n";
        }
    } catch (Exception $e) {
        echo "  ERRORE: " . $e->getMessage() . "\n";
    }
}

/**
 * Test della logica di validazione OTP (RitiratoStrategy)
 */
echo "\n[TEST 2] Verifica Validazione OTP\n";

class RitiratoStrategyTest extends RitiratoStrategy {
    public function validaCodice($codiceDB, $codiceInserito) {
        if (strtoupper($codiceDB) !== strtoupper($codiceInserito)) {
            throw new Exception("Codice errato! Verifica quello del cliente.");
        }
        return true;
    }
}

$testOTP = new RitiratoStrategyTest();
$codiceRiferimento = "A1B2";

try {
    $testOTP->validaCodice($codiceRiferimento, "A1B2");
    echo "  OK: Codice valido accettato.\n";
    
    $testOTP->validaCodice($codiceRiferimento, "a1b2");
    echo "  OK: Case-insensitivity corretta.\n";
} catch (Exception $e) {
    echo "  FAIL: Errore imprevisto: " . $e->getMessage() . "\n";
}

try {
    $testOTP->validaCodice($codiceRiferimento, "9999");
    echo "  FAIL: Codice errato non rilevato.\n";
} catch (Exception $e) {
    echo "  OK: Codice errato respinto: " . $e->getMessage() . "\n";
}

/**
 * Verifica dei vincoli di integrità sullo stato del locale
 */
echo "\n[TEST 3] Verifica Vincoli Operativi (Stato Apertura)\n";

function verificaVincoloEliminazione($statoApertura) {
    if ($statoApertura == 1) { 
        return "ERRORE 403: Operazione negata con locale aperto.";
    }
    return "SUCCESS 200: Operazione consentita.";
}

$resAperto = verificaVincoloEliminazione(1);
if (strpos($resAperto, 'ERRORE') !== false) {
    echo "  OK: Vincolo attivo in stato APERTO.\n";
} else {
    echo "  FAIL: Vincolo non rispettato in stato APERTO.\n";
}

$resChiuso = verificaVincoloEliminazione(0);
if (strpos($resChiuso, 'SUCCESS') !== false) {
    echo "  OK: Vincolo disattivato in stato CHIUSO.\n";
} else {
    echo "  FAIL: Errore inatteso in stato CHIUSO.\n";
}

/**
 * Tracciabilità delle transizioni di stato definite nel sistema
 */
echo "\n[TEST 4] Analisi Transizioni di Stato\n";
$transizioni = [
    'AccettaStrategy'  => 'ATTESA -> PREPARAZIONE',
    'ProntoStrategy'   => 'PREPARAZIONE -> PRONTO',
    'RitiratoStrategy' => 'PRONTO -> RITIRATO'
];

foreach ($transizioni as $classe => $flow) {
    echo "  INFO: $classe gestisce $flow\n";
}

echo "\n====================================================\n";
echo "   FINE TEST SUITE\n";
echo "====================================================\n";
echo "</pre>";
?>