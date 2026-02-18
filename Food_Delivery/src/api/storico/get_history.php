<?php

// SVOLTO DA COLUCCI PASQUALE, MATR: 358141

/**
 * API: Get_History.php
 * Unione dell'architettura a pattern (Strategy/Factory) con il frontend esistente.
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/JwtUtils.php';
// Inclusione componenti dell'amico
require_once __DIR__ . '/../storico/OrderHistoryRepository.php';
require_once __DIR__ . '/../storico/patterns/factory/OrderStrategyFactory.php';

try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (empty($authHeader) && function_exists('getallheaders')) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    
    $token = str_replace('Bearer ', '', $authHeader);
    $decoded = JwtUtils::validateToken($token);
    
    $userId = $decoded->data->id; 

    $database = new Database();
    $repo = new OrderHistoryRepository($database);
    
    $filter = $_GET['filter'] ?? 'all';
    $strategy = OrderStrategyFactory::getStrategy($filter);
    
    $rawOrders = $repo->getOrders($strategy, $userId);

    $mapped = [];
    foreach ($rawOrders as $row) {
        $mapped[] = [
            'data'          => $row['data_ora'],        
            'ristorante'    => $row['ristorante_nome'], 
            'dettagli'      => $row['dettagli'],
            'totale'        => $row['totale'],
            'stato'         => $row['stato'],
            'codice_ritiro' => $row['codice_ritiro'] ?? '',
            'note'          => $row['note'] ?? ''
        ];
    }

    echo json_encode(["records" => $mapped]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => $e->getMessage(), "records" => []]);
}