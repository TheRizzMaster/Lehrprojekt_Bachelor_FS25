<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendVerificationEmail($toEmail, $toName, $token, $subject, $message, $path) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->CharSet = 'UTF-8';

        // Sender and recipient
        $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Link erstellen
        $fullLink = $_ENV['APP_URL'] . $path . urlencode($token);

        // Inhalt
        $mail->Body = "
            Hallo $toName,<br><br>
            $message<br><br>
            <a href=\"$fullLink\">$fullLink</a><br><br>
            Dein Selflearning Coach
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}