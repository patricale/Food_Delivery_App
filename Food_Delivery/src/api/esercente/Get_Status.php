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

class ShopStatusController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getStatus() {
        $id_esercente = Auth_Helper::authenticate();

        try {
            $query = "SELECT stato_apertura, ragione_sociale FROM ESERCENTE WHERE id_utente = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id_esercente);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode([
                    "stato_apertura" => (bool)$row['stato_apertura'],
                    "ragione_sociale" => $row['ragione_sociale']
                ]);
            } else {
                echo json_encode(["stato_apertura" => false, "ragione_sociale" => "Locale Sconosciuto"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Errore Server"]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new ShopStatusController();
    $controller->getStatus();
}
?>