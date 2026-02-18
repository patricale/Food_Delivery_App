<?php

// SVOLTO DA COLUCCI PASQUALE, MATR. 358141

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/JwtUtils.php';
require_once __DIR__ . '/UserProfileRepo.php';

try {
    // --- A. AUTENTICAZIONE ---
    $headers = getallheaders();
    if (!$headers && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    }
    
    $authHeader = null;
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authHeader = $value;
            break;
        }
    }

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        throw new Exception("Token mancante o non valido.", 401);
    }

    $jwt = $matches[1];
    $decoded = JwtUtils::validateToken($jwt);
    
    $userId = $decoded->data->id;
    $userRole = strtolower($decoded->data->ruolo ?? ''); 

    // --- B. INPUT ---
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data)) {
        throw new Exception("Dati mancanti.", 400);
    }

    // --- C. AGGIORNAMENTO ---
    
    $repo = new UserProfileRepository();
    $successProfile = false;
    $successPassword = true; // Default true (se non c'è password da cambiare)

    // 1. GESTIONE PASSWORD 
    if (!empty($data['password'])) {
        $hashedPwd = password_hash($data['password'], PASSWORD_BCRYPT);

        if (!$repo->updateAccountPassword($userId, $hashedPwd)) {
            $successPassword = false;
        }
    }

    // 2. GESTIONE PROFILO 
    if ($userRole === 'studente' || $userRole === 'docente') {
        $nome = $data['nome'] ?? '';
        $cognome = $data['cognome'] ?? '';
        
        $successProfile = $repo->updateClienteData($userId, $nome, $cognome);

    } elseif ($userRole === 'esercente') {
        $ragione = $data['ragione_sociale'] ?? '';
        $piva = $data['p_iva'] ?? '';
        $indirizzo = $data['indirizzo_ritiro'] ?? '';
        $desc = $data['descrizione'] ?? '';

        $successProfile = $repo->updateEsercenteData($userId, $ragione, $piva, $indirizzo, $desc);
    } else {
        throw new Exception("Ruolo utente non riconosciuto.", 400);
    }

    // --- D. RISPOSTA ---
    if ($successProfile && $successPassword) {
        http_response_code(200);
        echo json_encode(["message" => "Profilo aggiornato con successo."]);
    } else {
        throw new Exception("Errore durante l'aggiornamento.", 500);
    }

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 100 || $code > 599) $code = 500;
    http_response_code($code);
    echo json_encode(["message" => $e->getMessage()]);
}
?>