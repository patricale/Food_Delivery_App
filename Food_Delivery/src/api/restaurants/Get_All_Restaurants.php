<?php
/**
 * Alessandro DI Stasi 358140
 * PROGETTO: Food Delivery Campus
 * MODULO: 2 - Catalogo & Ordini
 * FILE: Get-All-Restaurants.php
 * DESCRIZIONE: Restituisce la lista esercenti.
 */

// 1. GESTIONE CORS E AUTORIZZAZIONE
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Rispondi subito OK se il browser fa il controllo di sicurezza
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. LOGICA APPLICATIVA
include_once '../utils/Database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id_utente, ragione_sociale, descrizione, indirizzo_ritiro, stato_apertura 
          FROM ESERCENTE";

$stmt = $db->prepare($query);
$stmt->execute();

$esercenti_arr = array();
$esercenti_arr["records"] = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    extract($row);
    
    $item = array(
        "id" => $id_utente,
        "nome" => $ragione_sociale,
        "descrizione" => $descrizione,
        "indirizzo" => $indirizzo_ritiro,
        "aperto" => (bool)$stato_apertura,
        "immagine_url" => "https://placehold.co/600x400?text=" . urlencode($ragione_sociale)
    );

    array_push($esercenti_arr["records"], $item);
}

http_response_code(200);
echo json_encode($esercenti_arr);
?>