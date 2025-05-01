<?php
// admin_lesson.php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if (isset($_GET['action']) && $_GET['action'] === 'reorder' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    foreach ($data['order'] as $item) {
        $stmt = $pdo->prepare("UPDATE lessons SET position = ? WHERE id = ?");
        $stmt->execute([$item['position'], $item['id']]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } elseif (isset($_GET['module_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY position ASC");
        $stmt->execute([$_GET['module_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, description, theory_content, chat_config, ct_phase, position) VALUES (UUID(), ?, ?, ?, '[]', '{}', '', (SELECT COALESCE(MAX(position),0)+1 FROM lessons WHERE module_id = ?))");
    $stmt->execute([$data['parent_id'], $data['title'], $data['description'], $data['parent_id']]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'PUT') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $stmt = $pdo->prepare("UPDATE lessons SET title = ?, description = ?, theory_content = ?, chat_config = ?, ct_phase = ? WHERE id = ?");
    $stmt->execute([
        $data['title'],
        $data['description'],
        json_encode($data['theory_content']),
        json_encode($data['chat_config']),
        $data['ct_phase'],
        $query['id']
    ]);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->execute([$query['id']]);
    echo json_encode(['success' => true]);
    exit;
}
