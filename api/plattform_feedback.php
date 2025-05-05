<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Check Auth
if (!$user_id) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Token fehlt oder ungültig"]);
    exit;
}

// Parse JSON
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Ungültige JSON-Daten"]);
    exit;
}

// Validate required fields
$missing = [];
for ($i = 1; $i <= 16; $i++) {
    if (!isset($data["q$i"])) {
        $missing[] = "q$i";
    }
}
if (!empty($missing)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "error" => "Folgende Felder fehlen: " . implode(", ", $missing)
    ]);
    exit;
}

// Sanitize values
$values = [];
for ($i = 1; $i <= 16; $i++) {
    $values["q$i"] = max(1, min(6, intval($data["q$i"])));
}

$positive = trim($data["positive"] ?? '');
$suggestions = trim($data["suggestions"] ?? '');
$comments = trim($data["comments"] ?? '');

// Prepare SQL
try {
    $sql = "INSERT INTO platform_feedback (
                id, user_id, " . implode(", ", array_keys($values)) . ", positive, suggestions, comments
            ) VALUES (
                UUID(), ?, " . rtrim(str_repeat("?, ", count($values)), ", ") . ", ?, ?, ?
            )";

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        $user_id,
        ...array_values($values),
        $positive,
        $suggestions,
        $comments
    ]);

    echo json_encode(["success" => $success]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Fehler beim Speichern: " . $e->getMessage()
    ]);
}