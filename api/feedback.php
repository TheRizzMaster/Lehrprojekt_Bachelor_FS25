<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$lesson_id = $data['lesson_id'] ?? null;

if (!$lesson_id || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Fehlende oder ungültige Daten"]);
    exit;
}

// Alle Antworten extrahieren (Likert-Skala 1–6)
$q1 = intval($data['q1'] ?? 0);
$q2 = intval($data['q2'] ?? 0);
$q3 = intval($data['q3'] ?? 0);
$q4 = intval($data['q4'] ?? 0);
$q5 = intval($data['q5'] ?? 0);

// Eintrag speichern
$stmt = $pdo->prepare("
    INSERT INTO lesson_feedback 
    (user_id, lesson_id, submitted_at, q1, q2, q3, q4, q5)
    VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)
");
$success = $stmt->execute([
    $user_id,
    $lesson_id,
    $q1, $q2, $q3, $q4, $q5
]);

echo json_encode(["success" => $success]);