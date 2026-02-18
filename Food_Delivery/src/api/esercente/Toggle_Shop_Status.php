<?php
// SVOLTO DA Andrea Poccetti, MATR. 361127

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/Auth_Helper.php';

class ShopController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function toggleStatus($data) {
        $id_esercente = Auth_Helper::authenticate();

        if(!isset($data->stato_apertura)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Dati incompleti."]);
            return;
        }

        try {
            $query = "UPDATE ESERCENTE SET stato_apertura = :stato WHERE id_utente = :id";
            $stmt = $this->conn->prepare($query);

            $stato = $data->stato_apertura ? 1 : 0;

            $stmt->bindParam(":stato", $stato, PDO::PARAM_INT);
            $stmt->bindParam(":id", $id_esercente, PDO::PARAM_INT);

            if($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Stato aggiornato."]);
            } else {
                http_response_code(503);
                echo json_encode(["success" => false, "message" => "Impossibile aggiornare lo stato."]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Errore del server"]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $controller = new ShopController();
    $controller->toggleStatus($data);
}
?>