<?php
// admin_course.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO courses (title, description, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$data['title'], $data['description']]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'PUT') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$data['title'], $data['description'], $query['id']]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$query['id']]);
    echo json_encode(['success' => true]);
    exit;
}
