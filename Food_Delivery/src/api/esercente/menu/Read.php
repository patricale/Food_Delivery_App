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

class MenuReadController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getMenu() {
        $id_esercente = Auth_Helper::authenticate();

        try {
            $query = "SELECT * FROM PRODOTTO 
                      WHERE id_esercente = :id AND is_deleted = 0 
                      ORDER BY categoria, nome";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id_esercente);
            $stmt->execute();

            $prodotti = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($prodotti);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Errore Database: " . $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new MenuReadController();
    $controller->getMenu();
}
?>