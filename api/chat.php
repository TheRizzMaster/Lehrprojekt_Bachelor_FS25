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

    // Nachrichten holen
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

    // Nachricht des Users speichern
    $insert = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'user', ?)");
    $insert->execute([$chat_id, $message]);

    // Verlauf holen (letzte 10)
    $fetch = $pdo->prepare("SELECT sender, message FROM chat_messages WHERE chat_id = ? ORDER BY created_at ASC LIMIT 10");
    $fetch->execute([$chat_id]);
    $history = $fetch->fetchAll(PDO::FETCH_ASSOC);

    // GPT-Antwort generieren
    $ai_response = askOpenAI($history);

    // Antwort speichern
    $insert = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'ai', ?)");
    $insert->execute([$chat_id, $ai_response]);

    echo json_encode(["response" => $ai_response]);
    exit;
}

// === GPT-Funktion ===
function askOpenAI($history) {
    $apiKey = $_ENV['OPENAI_API_KEY'];
    $endpoint = "https://api.openai.com/v1/chat/completions";

    $messages = array_map(fn($m) => [
        "role" => $m["sender"] === "user" ? "user" : "assistant",
        "content" => $m["message"]
    ], $history);

    // System-Prompt
    array_unshift($messages, [
        "role" => "system",
        "content" => "Du bist ein geduldiger Lern-Coach, der dem Benutzer hilft, komplexe Probleme in kleine Schritte zu zerlegen. Stelle gezielte Rückfragen und motiviere zur Selbstlösung."
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
            "Authorization: Bearer {$apiKey}"
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    return $result["choices"][0]["message"]["content"] ?? "Leider konnte ich gerade keine Antwort generieren.";
}