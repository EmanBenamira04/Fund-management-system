<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

const JWT_SECRET = 'uQbpXa6jm7jNrj1HO6cMR9ri8UMMc6KOtoiFA6xrzMA='; // 🔒 CHANGE THIS!

function generate_jwt($payload) {
    $issuedAt = time();
    $expire = $issuedAt + (60 * 60); // 1 hour

    return JWT::encode([
        'iat' => $issuedAt,
        'exp' => $expire,
        'data' => $payload
    ], JWT_SECRET, 'HS256');
}

function validate_jwt($token) {
    $logFile = __DIR__ . '/../debug.log'; // log file path

    try {
        file_put_contents($logFile, "Token received: " . $token . PHP_EOL, FILE_APPEND); // Log token

        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        file_put_contents($logFile, "Token decoded successfully." . PHP_EOL, FILE_APPEND); // Log success

        return (array)$decoded->data;
    } catch (Exception $e) {
        file_put_contents($logFile, "Token validation failed: " . $e->getMessage() . PHP_EOL, FILE_APPEND); // Log error

        return false;
    }
}

function get_bearer_token() {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) return null;
    if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        return $matches[1];
    }
    return null;
}
