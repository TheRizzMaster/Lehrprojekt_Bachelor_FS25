<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json');

// 🔒 Sicherer Fallback für Authorization-Header
function getAuthorizationHeader() {
    if (isset($_SERVER['Authorization'])) {
        return trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        foreach ($requestHeaders as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return trim($value);
            }
        }
    }
    return null;
}

$authHeader = getAuthorizationHeader();

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["error" => "Authorization-Header fehlt"]);
    exit;
}

if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Ungültiger Authorization-Header"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
    $user_id = $decoded->sub;

    // ✅ Optional: zurückgeben für weitere Nutzung
    echo json_encode([
        "valid" => true,
        "user_id" => $user_id
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Token ungültig oder abgelaufen"]);
}