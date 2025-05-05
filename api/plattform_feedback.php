<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!$user_id) {
    echo json_encode(["success" => false, "error" => "Token fehlt oder ungültig"]);
    exit;
}

$values = [];
for ($i = 1; $i <= 16; $i++) {
    $values["q$i"] = isset($data["q$i"]) ? intval($data["q$i"]) : null;
}

$positive = $data["positive"] ?? null;
$suggestions = $data["suggestions"] ?? null;
$comments = $data["comments"] ?? null;

$sql = "INSERT INTO platform_feedback (id, user_id, " .
       implode(", ", array_keys($values)) . ", positive, suggestions, comments)
        VALUES (UUID(), ?, " . rtrim(str_repeat("?, ", count($values)), ", ") . ", ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    $user_id,
    ...array_values($values),
    $positive,
    $suggestions,
    $comments
]);

echo json_encode(["success" => $success]);