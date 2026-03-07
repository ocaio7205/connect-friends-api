<?php
declare(strict_types=1);

require_once __DIR__ . "/../bootstrap.php";

if (!csrf_validate()) json_err("CSRF inválido", 403);

start_session_secure();
$email  = (string)($_SESSION['pending_email'] ?? '');
$userId = (int)($_SESSION['pending_user_id'] ?? 0);

if ($email === '' || $userId <= 0) {
  json_err("Sessão de verificação não encontrada. Volte e tente novamente.", 400);
}

$conn = db();

$send = send_verification_code($conn, $userId, $email);
if (!$send['ok']) {
  $extra = [];
  if (defined('APP_DEBUG') && APP_DEBUG && isset($send['error'])) {
    $extra['debug'] = $send['error'];
  }
  json_err("Falha ao enviar e-mail. Verifique seu SMTP.", 500, $extra);
}

json_ok(["message" => "Código reenviado com sucesso."]);
