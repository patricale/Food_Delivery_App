<?php

// SVOLTO DA COLUCCI PASQUALE, MATR: 358141

// Percorso: src/api/utils/Database.php

class Database {
    // Parametri allineati con il docker-compose.yml
    private $host = "db"; 
    private $db_name = "food_delivery_db";
    private $username = "root";           
    private $password = "root_password";  
    
    public $conn;

    /**
     * Restituisce una connessione PDO al database.
     * Utilizza un approccio standard (non Singleton)
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Stringa di connessione
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // Opzioni per migliorare la sicurezza e la gestione errori
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lancia eccezioni in caso di errore SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Restituisce array associativi
                PDO::ATTR_EMULATE_PREPARES   => false,                // Usa prepared statements reali (sicurezza)
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

        } catch(PDOException $exception) {
            header("Access-Control-Allow-Origin: *");

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database Connection Error: " . $exception->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}
?>