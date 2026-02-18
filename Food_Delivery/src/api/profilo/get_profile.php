<?php
// SVOLTO DA COLUCCI PASQUALE, MATR. 358141

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

    // Validazione token
    $jwt = $matches[1];
    $decoded = JwtUtils::validateToken($jwt);
    $userId = $decoded->data->id;

    // --- B. RECUPERO DATI ---
    
    $repo = new UserProfileRepository();
    $userProfile = $repo->getUserById($userId);

    if (!$userProfile) {
        throw new Exception("Utente non trovato.", 404);
    }

    http_response_code(200);
    echo json_encode($userProfile);

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 100 || $code > 599) $code = 500;
    http_response_code($code);
    echo json_encode(["message" => $e->getMessage()]);
}
?>