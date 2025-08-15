<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';
header("Content-Type: application/json");

// Opcional: habilitar CORS para seu site
// header("Access-Control-Allow-Origin: https://stormclouds.com.br");
// header("Access-Control-Allow-Headers: Content-Type");
// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$name = trim($body['name'] ?? '');
$email = trim($body['email'] ?? '');
$message = trim($body['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios: name, email, message']);
    exit;
}

// Validação simples de e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'E-mail inválido']);
    exit;
}

// Variáveis de ambiente (configure no HostGator)
$smtpUser = getenv('SMTP_USER') ?: 'contato@stormclouds.com.br';
$smtpPass = getenv('SMTP_PASS') ?: '';
$toEmail = getenv('TO_EMAIL') ?: 'contato@stormclouds.com.br';
$fromName = getenv('FROM_NAME') ?: 'Formulário do Site - StormClouds';

$mail = new PHPMailer(true);

try {
    // SMTP Microsoft 365
    $mail->isSMTP();
    $mail->Host = 'smtp.office365.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;   // ex.: contato@stormclouds.com.br
    $mail->Password = $smtpPass;   // senha da caixa ou App Password (se MFA)
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    // Remetente precisa ser o mesmo usuário autenticado (M365 exige)
    $mail->setFrom($smtpUser, $fromName);

    // Destinatário (você/caixa de entrada)
    $mail->addAddress($toEmail, 'StormClouds Contato');

    // Responder ao visitante
    $mail->addReplyTo($email, $name);

    // Conteúdo
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = "Nova mensagem do site - {$name}";
    $mail->Body = "
        <strong>Nome:</strong> " . htmlspecialchars($name) . "<br>
        <strong>E-mail:</strong> " . htmlspecialchars($email) . "<br>
        <strong>Mensagem:</strong><br>" .
        nl2br(htmlspecialchars($message));
    $mail->AltBody = "Nome: {$name}\nE-mail: {$email}\nMensagem:\n{$message}";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $mail->ErrorInfo ?: 'Falha ao enviar']);
}
