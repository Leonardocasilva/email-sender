<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$to = $body['to'] ?? null;
$subject = $body['subject'] ?? null;
$message = $body['message'] ?? null;

if (!$to || !$subject || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios: to, subject, message']);
    exit;
}

$mail = new PHPMailer(true);

try {
    // SMTP Microsoft 365
    $mail->isSMTP();
    $mail->Host = 'smtp.office365.com';    // Microsoft 365
    $mail->SMTPAuth = true;
    $mail->Username = 'contato@seudominio.com'; // sua caixa do M365
    $mail->Password = 'SUA_SENHA_OU_APP_PASSWORD';
    $mail->Port = 587;                      // Submission
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // STARTTLS obrigatório

    // Remetente precisa ser a mesma caixa (ou alguém com Send As/Send on behalf)
    $mail->setFrom('contato@seudominio.com', 'Seu Nome/Empresa');
    $mail->addAddress($to);

    // Conteúdo
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->AltBody = strip_tags($message);

    // (Opcional) Reply-To diferente
    // $mail->addReplyTo('suporte@seudominio.com', 'Suporte');

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'E-mail enviado']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $mail->ErrorInfo]);
}
