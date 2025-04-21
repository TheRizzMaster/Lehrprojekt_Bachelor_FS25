<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../logic/send_verification_email.php';

header('Content-Type: application/json');

// Step 1: Parse and validate incoming data
$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Name, E-Mail und Passwort sind erforderlich."]);
    exit;
}

// Step 2: Hash password and generate verification token
$hash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("
    INSERT INTO users (
        name, email, password_hash, role, created_at,
        email_verified, email_verification_token
    )
    VALUES (?, ?, ?, 'student', NOW(), 0, ?)
");

try {
    $stmt->execute([$name, $email, $hash, $token]);

    $message = "Bitte bestätige deine E-Mail-Adresse durch Klick auf folgenden Link:";

    // Step 3: Send verification email
    $success = sendVerificationEmail(
        $email,
        $name,
        $token,
        "E-Mail-Bestätigung",
        $message,
        "/auth/verify_email.php?token="
    );

    if ($success) {
        echo json_encode(["success" => true, "message" => "Account erstellt – bitte E-Mail bestätigen."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Account erstellt, aber E-Mail konnte nicht gesendet werden."]);
    }

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate email
        http_response_code(409);
        echo json_encode(["error" => "Diese E-Mail ist bereits registriert."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Fehler beim Erstellen des Accounts."]);
    }
}