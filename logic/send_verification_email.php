<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendVerificationEmail($toEmail, $toName, $token, $url) {
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
        $mail->SMTPSecure = 'ssl'; // Verschlüsselung über SMTPS
        $mail->Port       = 465;

        // Absender & Empfänger
        $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($toEmail, $toName);

        // Inhalt
        $mail->isHTML(true);
        $mail->Subject = 'Bitte bestätige deine E-Mail-Adresse';

        $link = $_ENV['APP_URL'] . $url . urlencode($token);
        $mail->Body = "
            Hallo $toName,<br><br>
            Danke für deine Registrierung!<br>
            Bitte bestätige deine E-Mail-Adresse:<br><br>
            <a href=\"$link\">$link</a><br><br>
            Dein Selflearning Coach
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail konnte nicht gesendet werden: " . $mail->ErrorInfo);
        return false;
    }
}