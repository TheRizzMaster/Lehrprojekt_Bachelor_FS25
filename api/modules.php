<?php
ob_clean();
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php'; // setzt $user_id

header('Content-Type: application/json');

// Hole alle Kurse
$courseStmt = $pdo->query("SELECT id, title, description FROM courses");
$courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($courses as &$course) {
    // Module dieses Kurses + Fortschritt laden
    $modStmt = $pdo->prepare("
        SELECT 
            m.id, m.title, m.description, m.position,
            (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) AS total_lessons,
            COALESCE((
                SELECT COUNT(*) FROM progress p
                JOIN lessons l ON p.lesson_id = l.id
                WHERE l.module_id = m.id AND p.user_id = ? AND p.completed_at IS NOT NULL
            ), 0) AS completed_lessons
        FROM modules m
        WHERE m.course_id = ?
        ORDER BY m.position ASC
    ");
    $modStmt->execute([$user_id, $course['id']]);
    $modules = $modStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fortschritt berechnen
    foreach ($modules as &$mod) {
        $mod['completed_lessons'] = (int) ($mod['completed_lessons'] ?? 0);
        $mod['total_lessons'] = (int) ($mod['total_lessons'] ?? 0);
        $mod['progress'] = $mod['total_lessons'] > 0
            ? round(100 * $mod['completed_lessons'] / $mod['total_lessons'])
            : 0;
    }

    $course['modules'] = $modules;
}

echo json_encode($courses);
exit;