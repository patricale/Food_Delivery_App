<?php

// SVILUPPATO DA COLUCCI PASQUALE, MATR: 358141

/**
 * INTERFACCIA: IOrderFetchStrategy
 * Contratto per le strategie di recupero ordini.
 * Path: src/api/storico/patterns/strategy/IOrderFetchStrategy.php
 */

interface IOrderFetchStrategy {
    /**
     * Esegue la query per recuperare gli ordini.
     * @param PDO $conn Connessione al database
     * @param int $userId ID dell'utente
     * @return array
     */
    public function fetch(PDO $conn, int $userId): array;
}
?>