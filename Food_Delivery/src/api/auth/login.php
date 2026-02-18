<?php
// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../utils/Database.php';
include_once '../utils/JwtUtils.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->email) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Dati incompleti: inserire email e password."]);
        exit();
    }

    $query = "SELECT id_utente, email, password, ruolo 
              FROM UTENTE 
              WHERE email = :email 
              LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data->email);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        http_response_code(401);
        echo json_encode(["message" => "Nessun account trovato con questa email."]);
        exit();
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($data->password, $row['password'])) {
        http_response_code(401);
        echo json_encode(["message" => "Password errata."]);
        exit();
    }

    $payload = [
        'id' => $row['id_utente'],
        'email' => $row['email'],
        'ruolo' => $row['ruolo']
    ];

    $token = JwtUtils::generateToken($payload);

    http_response_code(200);
    echo json_encode([
        "message" => "Login effettuato con successo.",
        "token" => $token,
        "ruolo" => $row['ruolo'],
        "id_utente" => $row['id_utente']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Errore Login: " . $e->getMessage()]);
}
?>
