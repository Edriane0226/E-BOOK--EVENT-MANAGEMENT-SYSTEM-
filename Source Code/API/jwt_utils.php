<?php
require_once "C:/xampp/htdocs/E-BOOK--EVENT-MANAGEMENT-SYSTEM-/vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = '7bb84e9d2e4a7ac29800ae0d3ca9a867f9555f1dc08ec4a166155bd07c35db061e05a09a2864020e5e2d5a6454e43650f426302b59948e5a95e6d1f0b7eefbe6d7306596551d1599a0b0de9ffeb0308376dd3d3e0c95d9cc1197fb46cd08535defb1c723a94ad483e21561f69abc7232d434fcaffcffee459b71c9e2dd39cbcb';

function generateJWT($id, $role = 'user') {
    global $secret_key;

    $payload = [
        'iss' => 'http://localhost', // issuer
        'aud' => 'http://localhost', // audience
        'iat' => time(),             // issued at
        'exp' => time() + (60 * 60),  // expiration 1 hour
        'data' => [
            'id' => $id,
            'role' => $role
        ]
    ];

    return JWT::encode($payload, $secret_key, 'HS256');
}

function verifyJWT($jwt) {
    global $secret_key;

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

        if (!isset($decoded->data->id) || !isset($decoded->data->role)) {
            return false;
        }

        return [
            'id' => $decoded->data->id,
            'role' => $decoded->data->role
        ];
    } catch (Exception $e) {
        return false;
    }
}
?>
