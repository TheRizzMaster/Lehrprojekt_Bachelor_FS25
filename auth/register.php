<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../logic/send_verification_email.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Email und Passwort erforderlich"]);
    exit;
}

// Token generieren
$token = bin2hex(random_bytes(32));

// Passwort hashen
$hash = password_hash($password, PASSWORD_DEFAULT);

// User speichern
$stmt = $pdo->prepare("
    INSERT INTO users (name, email, password_hash, role, created_at, email_verified, email_verification_token)
    VALUES (?, ?, ?, 'student', NOW(), 0, ?)
");

try {
    $stmt->execute([$name, $email, $hash, $token]);

    // Mail versenden
    if (sendVerificationEmail($email, $name, $token, '/auth/verify_email.php?token=')) {
        echo json_encode(["success" => true, "message" => "Account erstellt – Bitte E-Mail bestätigen"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Fehler beim Mailversand"]);
    }

} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode(["error" => "Email bereits registriert", "message" => $e->getMessage()]);
}