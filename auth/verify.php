<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../bootstrap.php';

function getAuthorizationHeader() {
    if (isset($_SERVER['Authorization'])) return trim($_SERVER["Authorization"]);
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) return trim($_SERVER["HTTP_AUTHORIZATION"]);
    if (function_exists('apache_request_headers')) {
        foreach (apache_request_headers() as $k => $v) {
            if (strtolower($k) === 'authorization') return trim($v);
        }
    }
    return null;
}

$authHeader = getAuthorizationHeader();

if (!$authHeader || !preg_match('/Bearer\\s+(.*)$/i', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Authorization-Header fehlt oder ungültig"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
    $user_id = $decoded->sub; // 🔑 Bereit zur Weiterverwendung
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Token ungültig oder abgelaufen"]);
    exit;
}