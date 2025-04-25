<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$module_id = $data['module_id'] ?? null;
$lesson_id = $data['lesson_id'] ?? null;

if (!$module_id || !$lesson_id) {
  http_response_code(400);
  echo json_encode(["error" => "Fehlende Parameter"]);
  exit;
}

// Likert-Antworten sammeln
$answers = [
  "konzentration" => $data['q1'] ?? null,
  "verstehen"     => $data['q2'] ?? null,
  "transfer"      => $data['q3'] ?? null,
  "aktiv"         => $data['q4'] ?? null,
  "merken"        => $data['q5'] ?? null,
];

// JSON zusammenbauen
$feedback_json = json_encode($answers);

$stmt = $pdo->prepare("INSERT INTO module_feedback (user_id, module_id, submitted_at, feedback_json) VALUES (?, ?, NOW(), ?)");
$success = $stmt->execute([$user_id, $module_id, $feedback_json]);

echo json_encode(["success" => $success]);