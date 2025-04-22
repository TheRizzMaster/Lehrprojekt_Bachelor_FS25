<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php'; // $user_id

header('Content-Type: application/json');

$module_id = $_GET['modul_id'] ?? null;
if (!$module_id) {
    http_response_code(400);
    echo json_encode(["error" => "modul_id fehlt"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT l.id, l.title, l.description,
        p.completed_at IS NOT NULL AS completed
    FROM lessons l
    LEFT JOIN progress p ON p.lesson_id = l.id AND p.user_id = ?
    WHERE l.module_id = ?
    ORDER BY l.id ASC
");
$stmt->execute([$user_id, $module_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($lessons);