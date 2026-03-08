<?php


/**
 * PHPMailer SMTP
 * Requer: composer require phpmailer/phpmailer
 * Arquivo autoload esperado: /vendor/autoload.php
 */

// Carrega autoload do Composer se existir
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload;
}

/**
 * Envia e-mail HTML via SMTP.
 * @return array{ok:bool,error?:string}
 */
function send_email(string $to, string $subject, string $html): array {
  // Verifica se PHPMailer existe
  if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    return ['ok' => false, 'error' => 'PHPMailer não encontrado. Rode: composer require phpmailer/phpmailer'];
  }

  // Verifica se SMTP foi configurado
  if (SMTP_HOST === '' || SMTP_USER === '' || SMTP_PASS === '' || SMTP_FROM === '') {
    return ['ok' => false, 'error' => 'SMTP não configurado no bootstrap.php (SMTP_HOST/USER/PASS/FROM).'];
  }

  $mail = new PHPMailer\PHPMailer\PHPMailer(true);

  try {
    // Debug útil em localhost
    if (defined('APP_DEBUG') && APP_DEBUG) {
      $mail->SMTPDebug = 0; // mude pra 2 se quiser ver verboso
    }

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE; // 'tls' ou 'ssl'
    $mail->Port       = (int)SMTP_PORT;

    $mail->CharSet = 'UTF-8';
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $html;

    $mail->send();
    return ['ok' => true];
  } catch (Throwable $e) {
    return ['ok' => false, 'error' => $e->getMessage()];
  }
}

/**
 * Gera e envia código (6 dígitos) e salva em email_codigos.
 * Banco (dump): email_codigos(usuario_id,email,tipo,codigo_hash,tentativas,expira_em,usado_em,criado_em)
 */
function send_verification_code(mysqli $conn, int $userId, string $email): array {
  $codigo = (string)random_int(100000, 999999);
  $hash   = hash('sha256', $codigo);
  $expira = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

  // Salva
  $tipo = 'verificar_email';
  $stmt = $conn->prepare(
    'INSERT INTO email_codigos (usuario_id, email, tipo, codigo_hash, tentativas, expira_em, usado_em, criado_em)
     VALUES (?, ?, ?, ?, 0, ?, NULL, NOW())'
  );
  $stmt->bind_param('issss', $userId, $email, $tipo, $hash, $expira);
  $stmt->execute();

  $html = "
    <div style='font-family: Arial, sans-serif; line-height:1.5'>
      <h2>Seu código de verificação</h2>
      <p>Use este código para confirmar seu e-mail:</p>
      <div style='font-size:28px;font-weight:bold;letter-spacing:4px;margin:12px 0'>{$codigo}</div>
      <p>Este código expira em 15 minutos.</p>
    </div>
  ";

  $r = send_email($email, 'Código de verificação - Connect Friends', $html);
  return $r;
}

/**
 * Envia código de redefinição de senha e grava em redefinicao_senha.
 */
function send_password_reset_code(mysqli $conn, int $userId, string $email): array {
  $codigo = (string)random_int(100000, 999999);
  $hash   = hash('sha256', $codigo);
  $expira = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

  $stmt = $conn->prepare(
    'INSERT INTO redefinicao_senha (id_usuario, token_hash, expira_em, usado_em, criado_em)
     VALUES (?, ?, ?, NULL, NOW())'
  );
  $stmt->bind_param('iss', $userId, $hash, $expira);
  $stmt->execute();

  $html = "
    <div style='font-family: Arial, sans-serif; line-height:1.5'>
      <h2>Código para redefinir sua senha</h2>
      <p>Use este código para redefinir sua senha:</p>
      <div style='font-size:28px;font-weight:bold;letter-spacing:4px;margin:12px 0'>{$codigo}</div>
      <p>Este código expira em 15 minutos.</p>
    </div>
  ";

  return send_email($email, 'Redefinição de senha - Connect Friends', $html);
}
