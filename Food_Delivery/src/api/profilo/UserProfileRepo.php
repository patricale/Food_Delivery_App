<?php

//SVOLTO DA COLUCCI PASQUALE, MATR: 358141

/**
 * File: UserProfileRepo.php
 * Componente: Model
 * Descrizione: Implementazione della classe definita nel Diagramma delle Classi.
 * Gestisce l'accesso ai dati per il modulo Profilo.
 */

require_once __DIR__ . '/../utils/Database.php';

class UserProfileRepository {
    private $conn;

    // Costruttore: Istanzia il Database come da relazione di dipendenza UML
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Metodo: getUserById(id: int): Array
     * Corrispondenza UML: getUserById(id: int): Array
     * Descrizione: Recupera i dati dell'utente unendo le tabelle tramite JOIN.
     */
    public function getUserById($id) {
        // Query ottimizzata per recuperare tutto in una sola chiamata
        // LEFT JOIN permette di ottenere i dati specifici se esistono (Cliente o Esercente)
        $sql = "SELECT u.id_utente as id, u.email, u.ruolo,
                       c.nome, c.cognome, c.matricola,
                       e.ragione_sociale, e.p_iva, e.indirizzo_ritiro, e.descrizione
                FROM UTENTE u
                LEFT JOIN CLIENTE_UNIPR c ON u.id_utente = c.id_utente
                LEFT JOIN ESERCENTE e ON u.id_utente = e.id_utente
                WHERE u.id_utente = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Metodo: updateAccountPassword
     * Corrispondenza UML: updateAccountPassword(id: int, pass: string): bool
     * Descrizione: Aggiorna la password nella tabella padre UTENTE.
     */
    public function updateAccountPassword($id, $password) {
        $sql = "UPDATE UTENTE SET password = :pwd WHERE id_utente = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pwd', $password);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Metodo: updateClienteData
     * Corrispondenza UML: updateClienteData(id: int, nome: string, cognome: string): bool
     * Descrizione: Aggiorna i dati anagrafici.
     * VINCOLO DI ANALISI: La matricola NON è presente nella query (Immutabile).
     */
    public function updateClienteData($id, $nome, $cognome) {
        $sql = "UPDATE CLIENTE_UNIPR SET nome = :nom, cognome = :cog WHERE id_utente = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom', $nome);
        $stmt->bindParam(':cog', $cognome);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Metodo: updateEsercenteData
     * Corrispondenza UML: updateEsercenteData(...)
     * Descrizione: Aggiorna i dati business dell'esercente.
     */
    public function updateEsercenteData($id, $ragione, $piva, $indirizzo, $desc) {
        $sql = "UPDATE ESERCENTE 
                SET ragione_sociale = :rag, 
                    p_iva = :piva, 
                    indirizzo_ritiro = :ind, 
                    descrizione = :desc 
                WHERE id_utente = :id";
        
        $stmt = $this->conn->prepare($sql);
        // Binding dei parametri
        $stmt->execute([
            ':rag' => $ragione,
            ':piva' => $piva,
            ':ind' => $indirizzo,
            ':desc' => $desc,
            ':id' => $id
        ]);
        
        return $stmt->rowCount() >= 0; // Ritorna true se query eseguita (anche se 0 righe modificate)
    }
}
?>