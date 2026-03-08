<?php


require_once __DIR__ . "/../bootstrap.php";

if (!csrf_validate()) json_err("CSRF inválido", 403);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$email = trim((string)($data['email'] ?? ''));
$senha = (string)($data['senha'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) json_err("E-mail inválido.", 422);
if ($senha === '') json_err("Senha obrigatória.", 422);

$conn = db();

$stmt = $conn->prepare("SELECT id_usuarios, senha, email_verificado FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) json_err("E-mail ou senha incorretos.", 401);

$hash = (string)$row['senha'];
if (!password_verify($senha, $hash)) json_err("E-mail ou senha incorretos.", 401);

$uid = (int)$row['id_usuarios'];
$verificado = (int)$row['email_verificado'] === 1;

start_session_secure();

if (!$verificado) {
  // Salva sessão pendente
  $_SESSION['pending_user_id'] = $uid;
  $_SESSION['pending_email']   = $email;

  // Melhor UX: envia código automaticamente
  $send = send_verification_code($conn, $uid, $email);
  $msg = $send['ok']
    ? "Enviamos um código para seu e-mail."
    : "Não foi possível enviar agora. Clique em 'Reenviar código'.";

  $extra = [];
  if (defined('APP_DEBUG') && APP_DEBUG && !$send['ok'] && isset($send['error'])) {
    $extra['debug'] = $send['error'];
  }

  json_ok(["needs_verification" => true, "message" => $msg] + $extra);
}

// login ok
$_SESSION['uid'] = $uid;
json_ok(["message" => "Login OK"]);
