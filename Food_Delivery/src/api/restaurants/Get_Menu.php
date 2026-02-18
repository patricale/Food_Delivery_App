<?php
/**
 * Alessandro DI Stasi 358140
 * PROGETTO: Food Delivery Campus
 * MODULO: 2 - Catalogo & Ordini
 * FILE: Get-Menu.php
 * DESCRIZIONE: Restituisce i prodotti di un esercente.
 */

// --- 1. INTESTAZIONI (CORS & JSON) ---
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// --- 2. GESTIONE PREFLIGHT (Per il Token) ---
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- 3. LOGICA APPLICATIVA ---
include_once '../utils/Database.php';

// Verifica che ci sia l'ID dell'esercente
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["message" => "ID esercente mancante."]);
    exit;
}

$id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id_prodotto, nome, descrizione, prezzo, categoria, is_disponibile 
          FROM PRODOTTO 
          WHERE id_esercente = ? AND is_deleted = 0";

$stmt = $db->prepare($query);
$stmt->bindParam(1, $id);
$stmt->execute();

$products_arr = array();
$products_arr["records"] = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    extract($row);
    
    $item = array(
        "id" => $id_prodotto,
        "nome" => $nome,
        "descrizione" => $descrizione,
        "prezzo" => $prezzo,
        "categoria" => $categoria,
        "disponibile" => (bool)$is_disponibile
    );

    array_push($products_arr["records"], $item);
}

http_response_code(200);
echo json_encode($products_arr);
?>