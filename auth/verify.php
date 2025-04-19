<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json');

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Authorization-Header fehlt"]);
    exit;
}

$authHeader = $headers['Authorization'];
if (!preg_match('/Bearer\\s+(.*)$/i', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Ungültiger Authorization-Header"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
    $user_id = $decoded->sub;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Token ungültig oder abgelaufen"]);
    exit;
}