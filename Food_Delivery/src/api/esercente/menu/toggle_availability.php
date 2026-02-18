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

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/Auth_Helper.php';

class MenuAvailabilityController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function toggle($data) {
        $id_esercente = Auth_Helper::authenticate();

        if (empty($data['id_prodotto']) || !isset($data['is_disponibile'])) {
            http_response_code(400);
            echo json_encode(["message" => "Dati incompleti."]);
            return;
        }

        try {
            $query = "UPDATE PRODOTTO SET is_disponibile = :stato 
                      WHERE id_prodotto = :id AND id_esercente = :id_es";
            
            $stmt = $this->conn->prepare($query);
            
            $stato = $data['is_disponibile'] ? 1 : 0;
            
            $stmt->bindParam(":stato", $stato, PDO::PARAM_INT);
            $stmt->bindParam(":id", $data['id_prodotto']);
            $stmt->bindParam(":id_es", $id_esercente);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Disponibilità aggiornata."]);
            } else {
                throw new Exception("Errore aggiornamento.");
            }
        } catch (Exception $e) {
            http_response_code(503);
            echo json_encode(["message" => "Errore server."]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new MenuAvailabilityController();
    $controller->toggle($data);
}
?>
