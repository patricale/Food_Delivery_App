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

class OrderListController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getOrders() {
        $id_esercente = Auth_Helper::authenticate();

        try {
            $query = "SELECT 
                        O.id_ordine, O.data_ora, O.stato, O.note, O.totale, 
                        P.nome AS nome_prodotto, R.quantita, R.prezzo_storico
                      FROM ORDINE O
                      JOIN RIGA_ORDINE R ON O.id_ordine = R.id_ordine
                      JOIN PRODOTTO P ON R.id_prodotto = P.id_prodotto
                      WHERE O.id_esercente = :id 
                        AND O.stato NOT IN ('ritirato', 'rifiutato') 
                      ORDER BY O.data_ora DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id_esercente);
            $stmt->execute();
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $ordini = [];
            foreach ($rows as $row) {
                $id = $row['id_ordine'];
                if (!isset($ordini[$id])) {
                    $ordini[$id] = [
                        'id_ordine' => $id,
                        'data_ora' => $row['data_ora'],
                        'stato' => $row['stato'],
                        'totale' => $row['totale'],
                        'note' => $row['note'] ?? "",
                        'articoli' => []
                    ];
                }
                $ordini[$id]['articoli'][] = [
                    'nome' => $row['nome_prodotto'],
                    'quantita' => $row['quantita'],
                    'prezzo_storico' => $row['prezzo_storico']
                ];
            }
            echo json_encode(array_values($ordini));

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Errore recupero ordini"]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new OrderListController();
    $controller->getOrders();
}