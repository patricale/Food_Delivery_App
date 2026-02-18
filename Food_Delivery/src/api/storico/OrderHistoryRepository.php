<?php

// SVOLTO DA COLUCCI PASQUALE, MATR: 358141

require_once __DIR__ . '/../utils/Database.php';

require_once __DIR__ . '/patterns/strategy/IOrderFetchStrategy.php';

/**
 * CLASSE: OrderHistoryRepository
 * Pattern: Context
 * Responsabilità: Esecuzione della strategia scelta.
 */
class OrderHistoryRepository {
    private $db; 

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getOrders(IOrderFetchStrategy $strategy, int $userId) {
        $pdo = $this->db->getConnection(); 
        return $strategy->fetch($pdo, $userId);
    }
}