<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Nur POST erlaubt']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$helpful = $data['helpful'] ?? null;
$reasons = $data['reasons'] ?? null;
$improved = $data['improved'] ?? null;
$learn_effective = $data['learn_effective'] ?? null;
$general_feedback = $data['general_feedback'] ?? null;

if (!$helpful || !$learn_effective) {
  http_response_code(400);
  echo json_encode(['error' => 'Pflichtfelder fehlen']);
  exit;
}

$stmt = $pdo->prepare("INSERT INTO plattform_feedback (
    id, user_id, helpful, reasons, improved, learn_effective, general_feedback
) VALUES (
    UUID(), ?, ?, ?, ?, ?, ?
)");
$stmt->execute([
  $user_id,
  $helpful,
  $reasons,
  $improved,
  $learn_effective,
  $general_feedback
]);

echo json_encode(['success' => true]);