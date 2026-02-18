<?php
// FILE: src/api/utils/JwtUtils.php
// SVOLTO DA: SALE MARIO (Integrazione validazione per Storico/Profilo)
// MATRICOLA: 364432

class JwtUtils {
    private static $secret_key = 'ChiaveSegretaSuperSicuraPerEsameFoodDelivery';
    private static $algorithm = 'HS256';

    public static function generateToken($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);

        $jwtPayload = [
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60), 
            'data' => $payload
        ];

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($jwtPayload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validateToken($jwt) {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) != 3) {
            throw new Exception("Formato token non valido.");
        }

        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signatureProvided = $tokenParts[2];

        $validSignature = hash_hmac('sha256', $header . "." . $payload, self::$secret_key, true);
        $base64UrlValidSignature = self::base64UrlEncode($validSignature);

        if ($base64UrlValidSignature !== $signatureProvided) {
            throw new Exception("Firma del token non valida (Token manipolato).");
        }

        $jsonPayload = self::base64UrlDecode($payload);
        $decodedPayload = json_decode($jsonPayload);

        if (!$decodedPayload) {
            throw new Exception("Impossibile decodificare il payload JSON.");
        }

        if (isset($decodedPayload->exp) && $decodedPayload->exp < time()) {
            throw new Exception("Token scaduto.");
        }

        return $decodedPayload;
    }


    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }


    private static function base64UrlDecode($data) {
        $urlUnsafeData = str_replace(['-', '_'], ['+', '/'], $data);
        $remainder = strlen($urlUnsafeData) % 4;
        if ($remainder) {
            $padLength = 4 - $remainder;
            $urlUnsafeData .= str_repeat('=', $padLength);
        }
        return base64_decode($urlUnsafeData);
    }
}
?>