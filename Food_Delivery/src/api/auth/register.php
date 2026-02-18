<?php
// FILE: src/api/auth/register.php
// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/JwtUtils.php';

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->nome) && !empty($data->cognome) &&
    !empty($data->email) && !empty($data->password)
) {
    $database = new Database();
    $db = $database->getConnection();

    try {
        $db->beginTransaction(); 


        $ruolo = 'docente'; 
        $matricola = null;

        if (isset($data->ruolo) && $data->ruolo === 'esercente') {
            $ruolo = 'esercente';
        } elseif (!empty($data->matricola)) {
            $ruolo = 'studente';
            $matricola = $data->matricola;
        }

        $query = "INSERT INTO UTENTE (email, password, ruolo) VALUES (:email, :password, :ruolo)";
        $stmt = $db->prepare($query);

        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":ruolo", $ruolo);

        $stmt->execute();
        $last_id = $db->lastInsertId();

        if ($ruolo == 'studente' || $ruolo == 'docente') {
            $q2 = "INSERT INTO CLIENTE_UNIPR (id_utente, nome, cognome, matricola) VALUES (:id, :nm, :cg, :mat)";
            $stmt2 = $db->prepare($q2);
            $stmt2->execute([
                'id' => $last_id, 
                'nm' => $data->nome, 
                'cg' => $data->cognome, 
                'mat' => $matricola 
            ]);

        } elseif ($ruolo == 'esercente') {
            $q3 = "INSERT INTO ESERCENTE (id_utente, ragione_sociale, p_iva, indirizzo_ritiro, descrizione) VALUES (:id, :rs, :pi, :ind, :desc)";
            $stmt3 = $db->prepare($q3);
            $stmt3->execute([
                'id' => $last_id, 
                'rs' => $data->ragione_sociale ?? '', 
                'pi' => $data->p_iva ?? '', 
                'ind' => $data->indirizzo_ritiro ?? '', 
                'desc' => $data->descrizione ?? ''
            ]);
        }

        $db->commit(); 

        $payload = [
            "id" => $last_id,
            "ruolo" => $ruolo, 
            "email" => $data->email
        ];
        
        $jwt = JwtUtils::generateToken($payload);

        http_response_code(201);
        echo json_encode(array(
            "message" => "Registrazione completata.",
            "token" => $jwt,
            "id" => $last_id,
            "ruolo" => $ruolo
        ));

    } catch (Exception $e) {
        $db->rollBack(); 
        
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
             http_response_code(409); // Conflict
             echo json_encode(array("message" => "Email già registrata."));
        } else {
             http_response_code(500);
             echo json_encode(array("message" => "Errore Server: " . $e->getMessage()));
        }
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Dati incompleti. Compilare tutti i campi obbligatori."));
}
?>