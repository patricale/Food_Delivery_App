

  <?php
/**
 * Alessandro Di Stasi 358140
 * PROGETTO: Food Delivery Campus
 * MODULO: 2 - Catalogo & Ordini
 * FILE: Takeaway_Order.php
 * DESCRIZIONE: Implementazione concreta per ordini da asporto (genera codice ritiro).
 */

include_once 'Order_Product.php';

class Takeaway_Order extends Order_Product {
    private $codiceRitiro;

    protected function createOrderRecord() {
        
        $this->codiceRitiro = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        
        $query = "INSERT INTO ORDINE (id_cliente, id_esercente, totale, stato, codice_ritiro, note) 
                  VALUES (:uid, :eid, :tot, 'attesa', :code, :not)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":uid", $this->userId);
        $stmt->bindParam(":eid", $this->esercenteId);
        $stmt->bindParam(":tot", $this->total);
        $stmt->bindParam(":code", $this->codiceRitiro);
        $stmt->bindParam(":not", $this->note); 

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            throw new Exception("Errore SQL creazione ordine.");
        }
    }

    protected function getCodiceRitiro() {
        return $this->codiceRitiro;
    }
}
?>