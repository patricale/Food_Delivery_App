<?php
/**
 * Alessandro Di Stasi 358140
 * MODULO: 2 - Catalogo & Ordini
 * FILE: Create_Order.php
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include_once '../utils/Database.php';
include_once '../utils/JwtUtils.php';
include_once 'classes/Order_Factory.php';

// 1. RECUPERO TOKEN ROBUSTO (Compatibile con Apache/Docker)
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (empty($authHeader) && function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
}
$token = str_replace('Bearer ', '', $authHeader);

try {
    $decoded = JwtUtils::validateToken($token);
    
    // CORREZIONE: Nel login.php il campo è 'id', non 'id_utente'
    $id_utente_verificato = $decoded->data->id; 
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Sessione non valida"]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// 2. RICEZIONE E VALIDAZIONE DATI JSON
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_esercente) || !isset($data->items) || empty($data->items)) {
    http_response_code(400);
    echo json_encode(["message" => "Dati incompleti (Manca esercente o prodotti)"]);
    exit;
}

// Note opzionali (max 250 caratteri)
$nota = (isset($data->note) && !empty(trim($data->note))) ? substr(trim($data->note), 0, 250) : null;

// 3. RICALCOLO TOTALE E VALIDAZIONE PRODOTTI
$serverTotal = 0;
$cleanItems = [];

foreach ($data->items as $item) {
    $q = "SELECT prezzo, is_disponibile FROM PRODOTTO WHERE id_prodotto = ? AND id_esercente = ?";
    $stmtP = $db->prepare($q);
    $stmtP->execute([$item->id, $data->id_esercente]);
    
    if ($prodRow = $stmtP->fetch(PDO::FETCH_ASSOC)) {
        if ($prodRow['is_disponibile'] == 1) {
            $qty = intval($item->qty);
            $price = floatval($prodRow['prezzo']);
            $serverTotal += ($price * $qty);
            $cleanItems[] = ['id' => $item->id, 'qty' => $qty, 'price' => $price];
        }
    }
}

if (empty($cleanItems)) {
    http_response_code(400);
    echo json_encode(["message" => "Il carrello contiene prodotti non validi o non disponibili"]);
    exit;
}

// 4. CREAZIONE ORDINE (Factory Pattern)
try {
    // Usiamo $id_utente_verificato estratto dal Token
    $order = Order_Factory::create(
        'takeaway', 
        $db, 
        $id_utente_verificato, 
        $data->id_esercente, 
        $cleanItems, 
        $serverTotal, 
        $nota
    );
    
    $result = $order->process();

    if ($result['success']) {
        http_response_code(201);
        echo json_encode([
            "message" => "Ordine creato con successo",
            "codice_ritiro" => $result['codice_ritiro']
        ]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Errore durante la creazione: " . $result['message']]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Eccezione Server: " . $e->getMessage()]);
}
?>