<?php

// SVILUPPATO DA COLUCCI PASQUALE, MATR: 358141

require_once __DIR__ . '/IOrderFetchStrategy.php';

/**
 * CLASSE: ActiveOrdersStrategy
 * Strategia: Recupera solo gli ordini ATTIVI (In corso).
 * Path: src/api/storico/patterns/strategy/ActiveOrdersStrategy.php
 */
class ActiveOrdersStrategy implements IOrderFetchStrategy {
    public function fetch(PDO $conn, int $userId): array {
        
        $sql = "SELECT 
                    o.id_ordine as id, 
                    o.data_ora, 
                    o.totale, 
                    o.stato, 
                    o.codice_ritiro,
                    o.note,
                    e.ragione_sociale as ristorante_nome,
                    GROUP_CONCAT(CONCAT(p.nome, ' (x', ro.quantita, ')') SEPARATOR ', ') as dettagli
                FROM ORDINE o
                JOIN ESERCENTE e ON o.id_esercente = e.id_utente
                JOIN RIGA_ORDINE ro ON o.id_ordine = ro.id_ordine
                JOIN PRODOTTO p ON ro.id_prodotto = p.id_prodotto
                WHERE o.id_cliente = :userId 
                  AND o.stato IN ('attesa', 'accettato', 'preparazione', 'pronto')
                GROUP BY o.id_ordine
                ORDER BY o.data_ora DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll();
    }
}
?>