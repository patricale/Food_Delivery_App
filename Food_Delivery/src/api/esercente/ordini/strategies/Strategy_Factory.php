<?php

// SVOLTO DA Andrea Poccetti, MATR. 321127

require_once __DIR__ . '/Order_Strategies.php';

class StrategyFactory {
    
    /**
     * Restituisce l'istanza della strategia corrispondente all'azione richiesta.
     * @param string $azione
     * @return OrderStrategy
     * @throws Exception
     */
    public static function getStrategy($azione) {
        switch ($azione) {
            case 'accetta':
                return new AccettaStrategy();
            case 'rifiuta':
                return new RifiutaStrategy();
            case 'pronto':
                return new ProntoStrategy();
            case 'ritirato':
                return new RitiratoStrategy();
            case 'nonRitirato':
                return new NonRitiratoStrategy();
            default:
                throw new Exception("Azione non supportata");
        }
    }
}