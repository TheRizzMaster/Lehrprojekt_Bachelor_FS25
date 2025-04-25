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

    $stmt = $pdo->prepare("SELECT id FROM chats WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $lesson_id]);
    $chat = $stmt->fetch();

    if (!$chat) {
        $chat_id = bin2hex(random_bytes(16));
        $pdo->prepare("INSERT INTO chats (id, user_id, lesson_id) VALUES (?, ?, ?)")->execute([$chat_id, $user_id, $lesson_id]);

        $descStmt = $pdo->prepare("SELECT description FROM lessons WHERE id = ?");
        $descStmt->execute([$lesson_id]);
        $desc = $descStmt->fetchColumn();
        $systemMsg = "In dieser Lektion: " . ($desc ?: "Lerneinheit starten.");
        $pdo->prepare("INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'system', ?)")->execute([$chat_id, $systemMsg]);
    } else {
        $chat_id = $chat['id'];
    }

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

    $pdo->prepare("INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'user', ?)")->execute([$chat_id, $message]);
    $pdo->prepare("UPDATE chats SET user_turns = user_turns + 1 WHERE id = ?")->execute([$chat_id]);

    $lesson_id = $pdo->prepare("SELECT lesson_id FROM chats WHERE id = ?");
    $lesson_id->execute([$chat_id]);
    $lesson_id = $lesson_id->fetchColumn();

    $stmt = $pdo->prepare("SELECT chat_config FROM lessons WHERE id = ?");
    $stmt->execute([$lesson_id]);
    $config = json_decode($stmt->fetchColumn() ?? '{}', true);

    $fetch = $pdo->prepare("SELECT sender, message FROM chat_messages WHERE chat_id = ? ORDER BY created_at ASC LIMIT 15");
    $fetch->execute([$chat_id]);
    $history = $fetch->fetchAll(PDO::FETCH_ASSOC);

    $ai_response = askOpenAI($history, $config);

    $success_detected = false;
    $final_msg = $ai_response;

    $json_start = strpos($ai_response, '{');
    if ($json_start !== false) {
        $maybe_json = substr($ai_response, $json_start);
        $decoded = json_decode($maybe_json, true);
        if (is_array($decoded) && ($decoded['success'] ?? false) === true) {
            $success_detected = true;
            $final_msg = $decoded['message'] ?? '🎉 Ziel erreicht!';
        }
    }

    if ($success_detected) {
        $pdo->prepare("UPDATE chats SET success = 1, ended_at = NOW() WHERE id = ?")->execute([$chat_id]);
        $check = $pdo->prepare("SELECT * FROM progress WHERE user_id = ? AND lesson_id = ?");
        $check->execute([$user_id, $lesson_id]);

        if ($check->rowCount() > 0) {
            $pdo->prepare("UPDATE progress SET completed_at = NOW(), status = 'completed' WHERE user_id = ? AND lesson_id = ?")->execute([$user_id, $lesson_id]);
        } else {
            $pdo->prepare("INSERT INTO progress (user_id, lesson_id, status, completed_at) VALUES (?, ?, 'completed', NOW())")->execute([$user_id, $lesson_id]);
        }
    }

    $approx_tokens = intval((strlen($message) + strlen($final_msg)) / 4);
    $pdo->prepare("UPDATE chats SET token_usage = token_usage + ? WHERE id = ?")->execute([$approx_tokens, $chat_id]);

    $pdo->prepare("INSERT INTO chat_messages (chat_id, sender, message) VALUES (?, 'ai', ?)")->execute([$chat_id, $final_msg]);

    echo json_encode(["response" => $final_msg, "success" => $success_detected], JSON_UNESCAPED_UNICODE);
    exit;
}

function askOpenAI(array $history, array $config) {
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

    $system_prompt = "$role Ziel: $goal. Stil: $style. Wenn das Ziel erreicht ist ($success), antworte ausschließlich mit diesem JSON (ohne Einleitung oder Text davor): { \"success\": true, \"message\": \"...\" }";

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

    file_put_contents(__DIR__ . "/gpt_debug.json", $response);

    return $result["choices"][0]["message"]["content"] ?? "Ich konnte gerade keine Antwort generieren.";
}