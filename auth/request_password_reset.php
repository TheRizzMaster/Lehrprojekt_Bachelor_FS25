<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../logic/send_verification_email.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["message" => "Falls ein Konto existiert, wurde eine Mail gesendet."]);
    exit;
}

$token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare("UPDATE users SET password_reset_token = ?, password_reset_sent_at = NOW() WHERE id = ?");
$stmt->execute([$token, $user['id']]);

$link = $_ENV['APP_URL'] . "/reset_form.html?token=" . urlencode($token);
$message = "klicke hier um dein Passwort zurückzusetzen:";

sendVerificationEmail($user['email'], $user['name'], $token, "Passwort zurücksetzen", $message, '/reset_form.html?token=');

echo json_encode(["message" => "Falls ein Konto existiert, wurde eine Mail gesendet."]);