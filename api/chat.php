<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth/verify.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $lesson_id = $_GET['lesson_id'] ?? null;
    if (!$lesson_id) {
        http_response_code(400);
        echo json_encode(["error" => "lesson_id fehlt"]);
        exit;
    }

    // Chat finden oder erstellen
    $stmt = $pdo->prepare("SELECT id FROM chats WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $lesson_id]);
    $chat = $stmt->fetch();

    if (!$chat) {
        $chat_id = bin2hex(random_bytes(16));
        $insert = $pdo->prepare("INSERT INTO chats (id, user_id, lesson_id) VALUES (?, ?, ?)");
        $insert->execute([$chat_id, $user_id, $lesson_id]);
    } else {
        $chat_id = $chat['id'];
    }

    // Verlauf laden
    $msgStmt = $pdo->prepare("SELECT sender, message, created_at FROM chat_messages WHERE chat_id = ? ORDER BY created_at ASC");
    $msgStmt->execute([$chat_id]);
    $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["chat_id" => $chat_id, "messages" => $messages]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $chat_id = $data['chat_id'] ?? null;
    $message = trim($data['message'] ?? '');

    if (!$chat_id || !$message) {
        http_response_code(400);
        echo json_encode(["error" => "chat_id und message erforderlich"]);
        exit;
    }

    // Nachricht speichern
    $insert = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'user', ?)");
    $insert->execute([$chat_id, $message]);

    // Chat-Konfiguration der Lektion laden
    $lessonStmt = $pdo->prepare("SELECT lesson_id FROM chats WHERE id = ?");
    $lessonStmt->execute([$chat_id]);
    $lesson_id = $lessonStmt->fetchColumn();

    $configStmt = $pdo->prepare("SELECT chat_config FROM lessons WHERE id = ?");
    $configStmt->execute([$lesson_id]);
    $lesson = $configStmt->fetch();
    $config = json_decode($lesson['chat_config'] ?? '{}', true);

    // Verlauf holen
    $fetch = $pdo->prepare("SELECT sender, message FROM chat_messages WHERE chat_id = ? ORDER BY created_at ASC LIMIT 15");
    $fetch->execute([$chat_id]);
    $history = $fetch->fetchAll(PDO::FETCH_ASSOC);

    // GPT-Antwort generieren
    $ai_response = askOpenAI($history, $config);

    // Falls JSON zurückkommt: Erfolg erkannt
    $decoded = json_decode($ai_response, true);
    if (is_array($decoded) && isset($decoded["success"]) && $decoded["success"] === true) {
        $final_msg = $decoded["message"] ?? "🎉 Ziel erreicht!";
        // Fortschritt setzen
        $check = $pdo->prepare("SELECT * FROM progress WHERE user_id = ? AND lesson_id = ?");
        $check->execute([$user_id, $lesson_id]);

        if ($check->rowCount() > 0) {
            $update = $pdo->prepare("UPDATE progress SET completed_at = NOW(), status = 'completed' WHERE user_id = ? AND lesson_id = ?");
            $update->execute([$user_id, $lesson_id]);
        } else {
            $insert = $pdo->prepare("INSERT INTO progress (user_id, lesson_id, status, completed_at) VALUES (?, ?, 'completed', NOW())");
            $insert->execute([$user_id, $lesson_id]);
        }
    } else {
        $final_msg = $ai_response;
    }

    // Antwort speichern
    $insert = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'ai', ?)");
    $insert->execute([$chat_id, $final_msg]);

    echo json_encode(["response" => $final_msg]);
    exit;
}

// GPT-Funktion
function askOpenAI(array $history, array $config): string {
    $apiKey = $_ENV['OPENAI_API_KEY'];
    $endpoint = "https://api.openai.com/v1/chat/completions";

    $messages = array_map(fn($m) => [
        "role" => $m["sender"] === "user" ? "user" : "assistant",
        "content" => $m["message"]
    ], $history);

    $goal = $config['goal'] ?? "Der Lernende soll ein Verständnisziel erreichen.";
    $style = $config['style'] ?? "Motivierend und schrittweise.";
    $success = $config['success_condition'] ?? "Das Ziel ist vollständig erfüllt.";
    $role = $config['role'] ?? "Du bist ein Lern-Coach.";

    $system_prompt = "$role Ziel: $goal. Stil: $style. Wenn das Ziel erreicht ist ($success), antworte mit folgendem JSON: { \"success\": true, \"message\": \"...\" }";

    array_unshift($messages, [
        "role" => "system",
        "content" => $system_prompt
    ]);

    $data = [
        "model" => "gpt-4",
        "messages" => $messages,
        "temperature" => 0.7
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey"
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    return $result["choices"][0]["message"]["content"] ?? "Ich konnte gerade keine Antwort generieren.";
}