<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Keine Daten empfangen."]);
    exit;
}

// Mapping A–D → 1–4
$answerMap = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

// Korrekte Antworten
$correct = [
    1 => 3,  // Beispiel: c
    2 => 2,
    3 => 4,
    4 => 1,
    5 => 3,
    6 => 2,
    7 => 1,
    8 => 3,
    9 => 2,
    10 => 4,
    11 => 2,
    12 => 3,
    13 => 1,
    14 => 4,
    15 => 2,
    16 => 1,
];

// Eingaben überprüfen und umwandeln
$questions = [];
for ($i = 1; $i <= 16; $i++) {
    $key = "q$i";
    if (!isset($data[$key])) {
        echo json_encode(["success" => false, "message" => "Frage $i fehlt."]);
        exit;
    }

    $value = strtolower($data[$key]);
    if (!isset($answerMap[$value])) {
        echo json_encode(["success" => false, "message" => "Ungültige Antwort bei Frage $i."]);
        exit;
    }

    $questions[$key] = $answerMap[$value];
}

// Punktzahl berechnen
$score = 0;
foreach ($questions as $i => $val) {
    $index = intval(substr($i, 1)); // aus q1 → 1
    if ($val === $correct[$index]) {
        $score++;
    }
}

// Prüfen, ob Eintrag existiert
$stmt = $pdo->prepare("SELECT id FROM quiz_results WHERE user_id = ?");
$stmt->execute([$user_id]);
$exists = $stmt->fetch();

if ($exists) {
    echo json_encode([
        "success" => false,
        "message" => "Du hast den Quiz bereits abgeschlossen. Ergebnis: $score / 16"
    ]);
    exit;
}

// Eintragen
$fields = implode(", ", array_keys($questions));
$placeholders = implode(", ", array_fill(0, count($questions), "?"));

$sql = "INSERT INTO quiz_results (id, user_id, submitted_at, $fields, score)
        VALUES (UUID(), ?, NOW(), $placeholders, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge([$user_id], array_values($questions), [$score]));

echo json_encode([
    "success" => true,
    "message" => "Vielen Dank für deinen Quiz! Du hast $score von 16 Punkten erreicht."
]);