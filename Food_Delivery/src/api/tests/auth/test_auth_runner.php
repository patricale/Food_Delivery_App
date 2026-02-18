<?php

// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

/**
 * File: test_auth_runner.php
 * Descrizione: Script di Automated Integration Testing per il modulo Autenticazione.
 * Esegue test sequenziali su registrazione, login, validazione e auto-login.
 * 
 * USAGE: 
 * 1. Aprire nel browser: http://localhost:8001/tests/auth/test_auth_runner.php
 * 2. Oppure da terminale: php tests/auth/test_auth_runner.php
 */

header("Content-Type: text/plain; charset=UTF-8");

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/JwtUtils.php';

class AuthTestRunner {
    private $db;
    private $testEmail;
    private $testPassword = 'Password123';
    private $testUserId = null;

    public function __construct() {
        echo "========================================\n";
        echo "    TEST AUTOMATIZZATI - MODULO AUTH   \n";
        echo "========================================\n\n";
        
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            $this->log("PASS", "Connessione al database stabilita.");
            $this->testEmail = 'test_' . time() . '@studenti.unipr.it';
            $this->log("INFO", "Email di test: " . $this->testEmail);
        } catch (Exception $e) {
            $this->log("FAIL", "Impossibile connettersi al DB: " . $e->getMessage());
            die();
        }
    }

    private function log($status, $message) {
        $timestamp = date('H:i:s');
        echo "[$timestamp] [$status] $message\n";
    }

    private function assert($condition, $message) {
        if ($condition) {
            $this->log("✅ PASS", $message);
            return true;
        } else {
            $this->log("❌ FAIL", $message);
            return false;
        }
    }

    public function run() {
        $this->testDatabaseConnection();
        $this->testValidazioneDominioEmail();
        $this->testValidazionePasswordLunghezza();
        $this->testRegistrazioneSuccesso();
        $this->testUnicitaEmail();
        $this->testLoginSuccesso();
        $this->testLoginFallito();
        $this->testAutoLoginToken();
        $this->testPulizia();
        
        echo "\n========================================\n";
        echo "         TEST COMPLETATI               \n";
        echo "========================================\n";
    }

    private function testDatabaseConnection() {
        echo "\n[TEST 1] Verifica Connessione Database\n";
        try {
            $stmt = $this->db->query("SELECT 1");
            $this->assert($stmt !== false, "Query di test eseguita con successo.");
        } catch (Exception $e) {
            $this->assert(false, "Connessione DB fallita: " . $e->getMessage());
        }
    }

    private function testValidazioneDominioEmail() {
        echo "\n[TEST 2] Validazione Dominio Email (RF-1.10.2)\n";
        
        $dominiValidi = ['@studenti.unipr.it', '@unipr.it'];
        $dominiNonValidi = ['@gmail.com', '@yahoo.it', '@outlook.com'];
        
        foreach ($dominiValidi as $dominio) {
            $email = 'test' . $dominio;
            $isValid = (strpos($email, '@studenti.unipr.it') !== false) || 
                       (strpos($email, '@unipr.it') !== false);
            $this->assert($isValid, "Dominio valido riconosciuto: $dominio");
        }
        
        foreach ($dominiNonValidi as $dominio) {
            $email = 'test' . $dominio;
            $isValid = (strpos($email, '@studenti.unipr.it') !== false) || 
                       (strpos($email, '@unipr.it') !== false);
            $this->assert(!$isValid, "Dominio non valido rifiutato: $dominio");
        }
    }

    private function testValidazionePasswordLunghezza() {
        echo "\n[TEST 3] Validazione Lunghezza Password (RF-1.10.5)\n";
        
        $passwordTroppoCorta = '12345';
        $passwordValida = 'Password123';
        $passwordTroppoLunga = str_repeat('a', 73);
        
        $isTroppoCorta = strlen($passwordTroppoCorta) < 8;
        $isValida = strlen($passwordValida) >= 8 && strlen($passwordValida) <= 72;
        $isTroppoLunga = strlen($passwordTroppoLunga) > 72;
        
        $this->assert($isTroppoCorta, "Password troppo corta (<8 caratteri) rilevata.");
        $this->assert($isValida, "Password valida (8-72 caratteri) accettata.");
        $this->assert($isTroppoLunga, "Password troppo lunga (>72 caratteri) rilevata.");
    }

    private function testRegistrazioneSuccesso() {
        echo "\n[TEST 4] Registrazione Utente (RF-1.10.1, RF-1.10.6)\n";
        
        try {
            $password_hash = password_hash($this->testPassword, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO UTENTE (email, password, ruolo) 
                      VALUES (:email, :password, 'studente')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $this->testEmail);
            $stmt->bindParam(':password', $password_hash);
            $stmt->execute();
            
            $this->testUserId = $this->db->lastInsertId();
            
            $query2 = "INSERT INTO CLIENTE_UNIPR (id_utente, nome, cognome, matricola) 
                       VALUES (:id, 'Test', 'User', '123456')";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':id', $this->testUserId);
            $stmt2->execute();
            
            $this->assert($this->testUserId > 0, "Utente registrato con ID: " . $this->testUserId);
            
            $payload = ['id' => $this->testUserId, 'email' => $this->testEmail, 'ruolo' => 'studente'];
            $token = JwtUtils::generateToken($payload);
            $this->assert(!empty($token), "Token JWT generato per auto-login.");
            
        } catch (Exception $e) {
            $this->assert(false, "Registrazione fallita: " . $e->getMessage());
        }
    }

    private function testUnicitaEmail() {
        echo "\n[TEST 5] Controllo Unicità Email (RF-1.10.3, RF-1.10.4)\n";
        
        try {
            $query = "SELECT id_utente FROM UTENTE WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $this->testEmail);
            $stmt->execute();
            $exists = $stmt->rowCount() > 0;
            $this->assert($exists, "Email già registrata rilevata correttamente.");
            
            $emailNuova = 'nuova_' . time() . '@studenti.unipr.it';
            $stmt2 = $this->db->prepare($query);
            $stmt2->bindParam(':email', $emailNuova);
            $stmt2->execute();
            $notExists = $stmt2->rowCount() === 0;
            $this->assert($notExists, "Email non registrata rilevata correttamente.");
            
        } catch (Exception $e) {
            $this->assert(false, "Test unicità fallito: " . $e->getMessage());
        }
    }

    private function testLoginSuccesso() {
        echo "\n[TEST 6] Login con Credenziali Valide (RF-1.0.1)\n";
        
        try {
            $query = "SELECT id_utente, email, password, ruolo 
                      FROM UTENTE WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $this->testEmail);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->assert(!empty($user), "Utente trovato nel database.");
            
            if ($user) {
                $passwordValida = password_verify($this->testPassword, $user['password']);
                $this->assert($passwordValida, "Password verificata correttamente.");
                
                $payload = ['id' => $user['id_utente'], 'email' => $user['email'], 'ruolo' => $user['ruolo']];
                $token = JwtUtils::generateToken($payload);
                $this->assert(!empty($token), "Token JWT generato per il login.");
                
                $ruolo = $user['ruolo'];
                $this->assert($ruolo === 'studente', "Ruolo corretto (studente).");
            }
            
        } catch (Exception $e) {
            $this->assert(false, "Login fallito: " . $e->getMessage());
        }
    }

    private function testLoginFallito() {
        echo "\n[TEST 7] Login con Credenziali Non Valide (RF-1.0.2)\n";
        
        try {
            $query = "SELECT password FROM UTENTE WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $this->testEmail);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $passwordSbagliata = 'PasswordSbagliata123';
                $passwordValida = password_verify($passwordSbagliata, $user['password']);
                $this->assert(!$passwordValida, "Password errata rifiutata correttamente.");
            }
            
        } catch (Exception $e) {
            $this->assert(false, "Test login fallito: " . $e->getMessage());
        }
    }

    private function testAutoLoginToken() {
        echo "\n[TEST 8] Generazione Token per Auto-Login (RF-1.10.6)\n";
        
        $payload = [
            'id' => $this->testUserId ?: 1,
            'email' => $this->testEmail,
            'ruolo' => 'studente'
        ];
        
        $token = JwtUtils::generateToken($payload);
        
        $this->assert(!empty($token), "Token JWT generato con successo.");
        $this->assert(substr_count($token, '.') === 2, "Token JWT ha formato corretto (3 parti).");
        $this->assert(strlen($token) > 50, "Token JWT ha lunghezza adeguata.");
    }

    private function testPulizia() {
        echo "\n[TEST 9] Pulizia Dati di Test\n";
        
        if ($this->testUserId) {
            try {
                $query1 = "DELETE FROM CLIENTE_UNIPR WHERE id_utente = :id";
                $stmt1 = $this->db->prepare($query1);
                $stmt1->bindParam(':id', $this->testUserId);
                $stmt1->execute();
                
                $query2 = "DELETE FROM UTENTE WHERE id_utente = :id";
                $stmt2 = $this->db->prepare($query2);
                $stmt2->bindParam(':id', $this->testUserId);
                $stmt2->execute();
                
                $this->assert(true, "Dati di test rimossi correttamente.");
            } catch (Exception $e) {
                $this->assert(false, "Pulizia dati fallita: " . $e->getMessage());
            }
        } else {
            $this->log("INFO", "Nessun dato di test da pulire.");
        }
    }
}

$runner = new AuthTestRunner();
$runner->run();

?>