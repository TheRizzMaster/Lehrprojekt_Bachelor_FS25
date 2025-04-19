<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$token = $data['token'] ?? '';
$password = $data['password'] ?? '';

if (!$token || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Token oder Passwort fehlt"]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE password_reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültiger oder abgelaufener Link"]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ?, password_reset_token = NULL, password_reset_sent_at = NULL WHERE id = ?");
$stmt->execute([$hash, $user['id']]);

echo json_encode(["message" => "Passwort erfolgreich geändert"]);