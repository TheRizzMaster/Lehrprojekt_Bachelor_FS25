<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');

$lesson_id = $_GET['lesson_id'] ?? null;
if (!$lesson_id) {
    http_response_code(400);
    echo json_encode(["error" => "lesson_id fehlt"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, module_id, title, description, theory_content FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    http_response_code(404);
    echo json_encode(["error" => "Lektion nicht gefunden"]);
    exit;
}

$lesson['theory_content'] = json_decode($lesson['theory_content'], true);
echo json_encode($lesson);