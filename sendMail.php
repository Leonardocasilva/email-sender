<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

header("Content-Type: application/json");

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

// Recebe dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['to']) || !isset($data['subject']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios: to, subject, message']);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.seudominio.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'usuario@seudominio.com';
    $mail->Password = 'sua_senha';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587; // ou 465 para SMTPS

    // Remetente e destinatário
    $mail->setFrom('usuario@seudominio.com', 'Seu Nome');
    $mail->addAddress($data['to']);

    // Conteúdo do e-mail
    $mail->isHTML(true);
    $mail->Subject = $data['subject'];
    $mail->Body = $data['message'];

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'E-mail enviado com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Erro ao enviar e-mail: {$mail->ErrorInfo}"]);
}