<?php
/**
 * Alessandro Di Stasi 358140
 * PROGETTO: Food Delivery Campus
 * MODULO: 2 - Catalogo & Ordini
 * FILE: Order_Product.php
 * DESCRIZIONE: Classe astratta base (Template Method Pattern).
 */

abstract class Order_Product {
    protected $db;
    public $userId;
    public $esercenteId;
    public $items; 
    public $total;
    public $note;

    public function __construct($db, $userId, $esercenteId, $items, $total, $note = null) {
        $this->db = $db;
        $this->userId = $userId;
        $this->esercenteId = $esercenteId;
        $this->items = $items;
        $this->total = $total;
        $this->note = $note;
    }

    public function process() {
        try {
            $this->db->beginTransaction();

            
            if(!$this->checkEsercenteAperto()) {
                throw new Exception("L'esercente risulta chiuso al momento dell'ordine.");
            }

            $orderId = $this->createOrderRecord();
            $this->createOrderDetails($orderId);

            $this->db->commit();
            
            return [
                "success" => true, 
                "order_id" => $orderId, 
                "codice_ritiro" => $this->getCodiceRitiro()
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    protected function checkEsercenteAperto() {
        $query = "SELECT stato_apertura FROM ESERCENTE WHERE id_utente = :eid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":eid", $this->esercenteId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row && $row['stato_apertura'] == 1);
    }

    protected function createOrderDetails($orderId) {
        $query = "INSERT INTO RIGA_ORDINE (id_ordine, id_prodotto, quantita, prezzo_storico) 
                  VALUES (:id_ord, :id_prod, :qty, :price)";
        $stmt = $this->db->prepare($query);

        foreach ($this->items as $item) {
            $stmt->bindParam(":id_ord", $orderId);
            $stmt->bindParam(":id_prod", $item['id']);
            $stmt->bindParam(":qty", $item['qty']);
            $stmt->bindParam(":price", $item['price']); // Prezzo fissato al momento dell'ordine
            
            if (!$stmt->execute()) {
                throw new Exception("Errore nel salvataggio righe ordine.");
            }
        }
    }

    abstract protected function createOrderRecord();
    abstract protected function getCodiceRitiro();
}
?>