<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php'; // setzt $user_id

header('Content-Type: application/json');

$module_id = $_GET['modul_id'] ?? null;
if (!$module_id) {
    http_response_code(400);
    echo json_encode(["error" => "modul_id fehlt"]);
    exit;
}

// Modulinformationen holen
$modStmt = $pdo->prepare("SELECT title, description FROM modules WHERE id = ?");
$modStmt->execute([$module_id]);
$module = $modStmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    http_response_code(404);
    echo json_encode(["error" => "Modul nicht gefunden"]);
    exit;
}

// Lektionen laden
$lessonStmt = $pdo->prepare("
    SELECT l.id, l.title, l.description,
           p.completed_at IS NOT NULL AS completed
    FROM lessons l
    LEFT JOIN progress p ON p.lesson_id = l.id AND p.user_id = ?
    WHERE l.module_id = ?
    ORDER BY l.position ASC
");
$lessonStmt->execute([$user_id, $module_id]);
$lessons = $lessonStmt->fetchAll(PDO::FETCH_ASSOC);

// Modulinfo + Lektionen zurückgeben
echo json_encode([
    "module" => $module,
    "lessons" => $lessons
]);