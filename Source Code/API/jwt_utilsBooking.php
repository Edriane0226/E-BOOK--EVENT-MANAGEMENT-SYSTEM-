<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTUtils {
    private const JWT_SECRET_KEY = 'your-256-bit-secret';
    private const JWT_ALGORITHM = 'HS256';
    private const JWT_EXPIRY_TIME = 3600;

    public static function generateToken(array $payload): string {
        $issuedAt = time();
        $expirationTime = $issuedAt + self::JWT_EXPIRY_TIME;

        $token_payload = [
            'iss' => 'http://localhost',
            'aud' => 'http://localhost',
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $payload
        ];

        return JWT::encode($token_payload, self::JWT_SECRET_KEY, self::JWT_ALGORITHM);
    }

    public static function verifyToken(string $jwt): array|false {
        try {
            $decoded = JWT::decode($jwt, new Key(self::JWT_SECRET_KEY, self::JWT_ALGORITHM));
            return (array) $decoded->data;
        } catch (Exception $e) {
            error_log("JWT Error: " . $e->getMessage());
            return false;
        }
    }
}
