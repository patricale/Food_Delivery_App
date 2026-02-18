<?php
// SVOLTO DA Andrea Poccetti, MATR. 321127

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/strategies/Strategy_Factory.php';
require_once __DIR__ . '/../../utils/Auth_Helper.php';

class OrderController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function updateStatus($input) {
        $id_esercente = Auth_Helper::authenticate();
        
        if (!isset($input['id_ordine']) || !isset($input['azione'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Parametri mancanti"]);
            return;
        }

        $id_ordine = (int)$input['id_ordine'];
        $azione = $input['azione'];

        try {
            $checkQuery = "SELECT id_ordine FROM ORDINE WHERE id_ordine = :id_ord AND id_esercente = :id_es";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":id_ord", $id_ordine);
            $checkStmt->bindParam(":id_es", $id_esercente);
            $checkStmt->execute();

            if ($checkStmt->rowCount() === 0) {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Non autorizzato: L'ordine non appartiene al tuo negozio."]);
                return;
            }

            $strategia = StrategyFactory::getStrategy($azione);

            if ($strategia->esegui($id_ordine, $this->conn, $input)) {
                echo json_encode(["success" => true, "message" => "Stato aggiornato con successo"]);
            } else {
                http_response_code(200); 
                echo json_encode(["success" => false, "message" => "Nessuna modifica effettuata o stato non valido."]);
            }
        } catch (Exception $e) {
            http_response_code(500); 
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $controller = new OrderController();
    $controller->updateStatus($input);
}
?>