<?php
/**
 * Alessandro Di Stasi 358140
 * PROGETTO: Food Delivery Campus
 * MODULO: 2 - Catalogo & Ordini
 * FILE: Order_Factory.php
 * DESCRIZIONE: Factory Pattern. Decide quale classe istanziare.
 */

include_once 'Takeaway_Order.php';

class Order_Factory {
    
    public static function create($type, $db, $userId, $esercenteId, $items, $total, $note = null) {
        if ($type === 'takeaway') {
           
            return new Takeaway_Order($db, $userId, $esercenteId, $items, $total, $note);
        }
        throw new Exception("Tipo ordine non gestito.");
    }
}
?>