<?php

// SVOLTO DA COLUCCI PASQUALE, MATR: 358141

/**
 * FACTORY: OrderStrategyFactory
 * Responsabilità: Creazione centralizzata delle strategie.
 * Pattern: Factory Method
 * * Path: src/api/storico_ordini/patterns/factory/OrderStrategyFactory.php
 */

require_once __DIR__ . '/../strategy/IOrderFetchStrategy.php';
require_once __DIR__ . '/../strategy/AllOrdersStrategy.php';
require_once __DIR__ . '/../strategy/PastOrdersStrategy.php';
require_once __DIR__ . '/../strategy/ActiveOrdersStrategy.php';

class OrderStrategyFactory {

    /**
     * Restituisce l'istanza della strategia corretta in base al filtro stringa.
     * * @param string $filter Il filtro richiesto ('past', 'active', 'all')
     * @return IOrderFetchStrategy L'oggetto strategia concreto
     */
    public static function getStrategy(string $filter): IOrderFetchStrategy {
        switch ($filter) {
            case 'past':
                // Strategia per ordini conclusi (Storico)
                return new PastOrdersStrategy();
            
            case 'active':
                // Strategia per ordini in corso
                return new ActiveOrdersStrategy();
            
            case 'all':
            default:
                // Default Case: Se il filtro è nullo o sconosciuto, ritorna tutti
                return new AllOrdersStrategy();
        }
    }
}
?>