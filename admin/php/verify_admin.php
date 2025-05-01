<?php
// verify.php – Zugriffsschutz für Admins
require_once __DIR__ . '/../bootstrap.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/Bearer\s+(.*)/i', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Token fehlt oder ungültig']);
    exit;
}

$token = $matches[1];

try {
    $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($_ENV['JWT_SECRET'], 'HS256'));
    $user_id = $decoded->sub;

    // Admin-Check (optional):
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();

    if ($role !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Nur Admins erlaubt']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Ungültiges Token', 'details' => $e->getMessage()]);
    exit;
}
