<?php

// Autore: Andrea Poccetti, Matricola: 361127

require_once __DIR__ . '/JwtUtils.php';

class Auth_Helper {
    
    public static function authenticate() {
        // 1. Recupera gli header della richiesta
        $headers = null;
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = getallheaders();
        }
        
        // 2. Cerca l'header Authorization (case insensitive)
        $authHeader = null;
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }

        // 3. Verifica formato "Bearer <token>"
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            self::sendUnauthorized("Token non fornito o formato errato.");
        }

        $jwt = $matches[1];

        try {
            $payload = JwtUtils::validateToken($jwt);
            
            $payloadData = is_object($payload) ? ($payload->data ?? $payload) : ($payload['data'] ?? $payload);

            $userId = null;
            if (is_object($payloadData)) {
                $userId = $payloadData->id ?? $payloadData->id_utente ?? $payloadData->sub ?? null;
            } elseif (is_array($payloadData)) {
                $userId = $payloadData['id'] ?? $payloadData['id_utente'] ?? $payloadData['sub'] ?? null;
            }

            if (!$userId) {
                throw new Exception("ID utente non trovato nel payload del token.");
            }

            return (int)$userId;

        } catch (Exception $e) {
            self::sendUnauthorized("Token non valido: " . $e->getMessage());
        }
    }

    private static function sendUnauthorized($msg) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => $msg]);
        exit();
    }
}
?>