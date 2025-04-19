<?php
require_once __DIR__ . '/../bootstrap.php';
use Firebase\JWT\JWT;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Email und Passwort erforderlich"]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(["error" => "Ungültige Login-Daten"]);
    exit;
}

$payload = [
    "iss" => JWT_ISSUER,
    "iat" => time(),
    "exp" => time() + JWT_LIFETIME,
    "sub" => $user['id']
];

$jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
echo json_encode(["token" => $jwt]);