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

class MenuDeleteController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function delete($data) {
        $id_esercente = Auth_Helper::authenticate();

        if (empty($data['id_prodotto'])) {
            http_response_code(400);
            echo json_encode(["message" => "ID prodotto mancante."]);
            return;
        }

        try {
            $checkQuery = "SELECT stato_apertura FROM ESERCENTE WHERE id_utente = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":id", $id_esercente);
            $checkStmt->execute();
            $statusRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($statusRow && $statusRow['stato_apertura'] == 1) {
                http_response_code(403);
                echo json_encode([
                    "success" => false, 
                    "message" => "IMPOSSIBILE ELIMINARE: Il locale è APERTO. Chiudilo prima di modificare il menu."
                ]);
                return;
            }

            $query = "UPDATE PRODOTTO SET is_deleted = 1 
                      WHERE id_prodotto = :id AND id_esercente = :id_es";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $data['id_prodotto']);
            $stmt->bindParam(":id_es", $id_esercente);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Prodotto eliminato."]);
            } else {
                throw new Exception("Errore query.");
            }
        } catch (Exception $e) {
            http_response_code(503);
            echo json_encode(["success" => false, "message" => "Impossibile eliminare."]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new MenuDeleteController();
    $controller->delete($data);
}
?>