<?php
// admin_module.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if (isset($_GET['action']) && $_GET['action'] === 'reorder' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    foreach ($data['order'] as $item) {
        $stmt = $pdo->prepare("UPDATE modules SET position = ? WHERE id = ?");
        $stmt->execute([$item['position'], $item['id']]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } elseif (isset($_GET['course_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY position ASC");
        $stmt->execute([$_GET['course_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO modules (id, course_id, title, description, position) VALUES (UUID(), ?, ?, ?, (SELECT COALESCE(MAX(position),0)+1 FROM modules WHERE course_id = ?))");
    $stmt->execute([$data['parent_id'], $data['title'], $data['description'], $data['parent_id']]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'PUT') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $stmt = $pdo->prepare("UPDATE modules SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$data['title'], $data['description'], $query['id']]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->execute([$query['id']]);
    echo json_encode(['success' => true]);
    exit;
}
