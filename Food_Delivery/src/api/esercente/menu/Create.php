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

class MenuCreateController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create($data) {
        $id_esercente = Auth_Helper::authenticate();

        if (empty($data['nome']) || empty($data['prezzo'])) {
            http_response_code(400);
            echo json_encode(["message" => "Dati incompleti (Nome e Prezzo obbligatori)."]);
            return;
        }

        try {
            // Controllo Stato: Il negozio deve essere CHIUSO per modificare il menu
            $checkQuery = "SELECT stato_apertura FROM ESERCENTE WHERE id_utente = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":id", $id_esercente);
            $checkStmt->execute();
            $statusRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($statusRow && $statusRow['stato_apertura'] == 1) {
                http_response_code(403); // Forbidden
                echo json_encode([
                    "success" => false, 
                    "message" => "IMPOSSIBILE AGGIUNGERE: Il locale è APERTO. Chiudilo prima di modificare il menu."
                ]);
                return;
            }

            // Inserimento Prodotto
            $query = "INSERT INTO PRODOTTO (id_esercente, nome, descrizione, prezzo, categoria) 
                      VALUES (:id_esercente, :nome, :descrizione, :prezzo, :categoria)";
            
            $stmt = $this->conn->prepare($query);
            
            $nome = htmlspecialchars(strip_tags($data['nome']));
            $desc = htmlspecialchars(strip_tags($data['descrizione'] ?? ''));
            $cat = htmlspecialchars(strip_tags($data['categoria'] ?? 'Generale'));

            $stmt->bindParam(":id_esercente", $id_esercente);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":descrizione", $desc);
            $stmt->bindParam(":prezzo", $data['prezzo']);
            $stmt->bindParam(":categoria", $cat);

            if ($stmt->execute()) {
                http_response_code(201); // Created
                echo json_encode(["success" => true, "message" => "Prodotto creato con successo."]);
            } else {
                throw new Exception("Errore durante l'inserimento nel database.");
            }
        } catch (Exception $e) {
            http_response_code(503); // Service Unavailable
            echo json_encode(["success" => false, "message" => "Errore server: " . $e->getMessage()]);
        }
    }
}

// Gestione della richiesta POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new MenuCreateController();
    $controller->create($data);
}
?>