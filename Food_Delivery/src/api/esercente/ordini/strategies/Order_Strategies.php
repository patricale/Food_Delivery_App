<?php

// SVOLTO DA Andrea Poccetti, MATR. 361127

interface OrderStrategy {
    public function esegui($id_ordine, $conn, $context = []);
}

class AccettaStrategy implements OrderStrategy {
    public function esegui($id_ordine, $conn, $context = []) {
        $query = "UPDATE ORDINE SET stato = 'preparazione' WHERE id_ordine = :id AND stato = 'attesa'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id_ordine);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

class RifiutaStrategy implements OrderStrategy {
    public function esegui($id_ordine, $conn, $context = []) {
        $query = "UPDATE ORDINE SET stato = 'rifiutato' WHERE id_ordine = :id AND stato = 'attesa'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id_ordine);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

class ProntoStrategy implements OrderStrategy {
    public function esegui($id_ordine, $conn, $context = []) {
        $query = "UPDATE ORDINE SET stato = 'pronto' WHERE id_ordine = :id AND stato = 'preparazione'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id_ordine);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

class RitiratoStrategy implements OrderStrategy {
    public function esegui($id_ordine, $conn, $context = []) {
        if (empty($context['codice_verifica'])) {
            throw new Exception("Codice di ritiro obbligatorio.");
        }
        
        $codice_inserito = trim($context['codice_verifica']);

        $checkQuery = "SELECT codice_ritiro FROM ORDINE WHERE id_ordine = :id AND stato = 'pronto'";
        $stmtCheck = $conn->prepare($checkQuery);
        $stmtCheck->bindParam(':id', $id_ordine);
        $stmtCheck->execute();
        
        $ordine = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$ordine) {
            throw new Exception("Ordine non trovato o non in stato 'Pronto'.");
        }

        if (strtoupper($ordine['codice_ritiro']) !== strtoupper($codice_inserito)) {
            throw new Exception("Codice errato! Verifica quello del cliente.");
        }

        $query = "UPDATE ORDINE SET stato = 'ritirato' WHERE id_ordine = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id_ordine);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}

class NonRitiratoStrategy implements OrderStrategy {
    public function esegui($id_ordine, $conn, $context = []) {
        $query = "UPDATE ORDINE SET stato = 'nonRitirato' WHERE id_ordine = :id AND stato = 'pronto'";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id_ordine);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}