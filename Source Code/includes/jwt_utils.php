<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// JWT Configuration
define('JWT_SECRET_KEY', '7bb84e9d2e4a7ac29800ae0d3ca9a867f9555f1dc08ec4a166155bd07c35db061e05a09a2864020e5e2d5a6454e43650f426302b59948e5a95e6d1f0b7eefbe6d7306596551d1599a0b0de9ffeb0308376dd3d3e0c95d9cc1197fb46cd08535defb1c723a94ad483e21561f69abc7232d434fcaffcffee459b71c9e2dd39cbcb');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY_TIME', 3600); // 1 hour

/**
 * Generate JWT Token (Updated to include more user data)
 */
function generateJWT($payload) {
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRY_TIME;
    
    $token_payload = [
        'iss' => 'http://localhost', // issuer
        'aud' => 'http://localhost', // audience
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'data' => $payload
    ];
    
    return JWT::encode($token_payload, JWT_SECRET_KEY, JWT_ALGORITHM);
}

/**
 * Legacy function - kept for backward compatibility
 */
function generateJWTLegacy($id, $role = 'user') {
    $payload = [
        'id' => $id,
        'role' => $role
    ];
    
    return generateJWT($payload);
}

/**
 * Verify JWT Token
 */
function verifyJWT($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));
        
        if (!isset($decoded->data)) {
            return false;
        }
        
        return (array) $decoded->data;
    } catch (Exception $e) {
        error_log("JWT Verification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get JWT from session or header
 */
function getJWTFromRequest() {
    // First check session
    if (isset($_SESSION['jwt_token'])) {
        return $_SESSION['jwt_token'];
    }
    
    // Then check Authorization header for API requests
    $headers = getallheaders();
    if ($headers && isset($headers['Authorization'])) {
        $auth_header = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Authenticate user and return user data
 */
function authenticateUser() {
    $token = getJWTFromRequest();
    
    if (!$token) {
        return false;
    }
    
    $user_data = verifyJWT($token);
    
    if (!$user_data) {
        return false;
    }
    
    return $user_data;
}

/**
 * Check if JWT token is expired
 */
function isJWTExpired($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));
        return $decoded->exp < time();
    } catch (Exception $e) {
        return true;
    }
}

/**
 * Refresh JWT token if needed
 */
function refreshJWTIfNeeded($jwt, $user_data) {
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));
        
        // If token expires in less than 15 minutes, refresh it
        if ($decoded->exp - time() < 900) {
            return generateJWT($user_data);
        }
        
        return $jwt;
    } catch (Exception $e) {
        return false;
    }
}
?>