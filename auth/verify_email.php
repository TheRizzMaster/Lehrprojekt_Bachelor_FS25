<?php
require_once __DIR__ . '/../bootstrap.php';

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email_verification_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
    echo "Email erfolgreich bestätigt!";
} else {
    echo "Ungültiger oder abgelaufener Token.";
}