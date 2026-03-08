<?php


require_once __DIR__ . "/../bootstrap.php";

if (!csrf_validate()) json_err("CSRF inválido", 403);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = isset($data['email']) ? trim((string)$data['email']) : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_err("E-mail inválido.", 422);
}

$conn = db();

// Não revela se existe ou não
$stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
  json_ok(["message" => "Se existir uma conta, enviamos um código."]);
}

$userId = (int)$row['id_usuarios'];

$send = send_password_reset_code($conn, $userId, $email);
if (!$send['ok']) {
  $extra = [];
  if (defined('APP_DEBUG') && APP_DEBUG && isset($send['error'])) {
    $extra['debug'] = $send['error'];
  }
  // Mantém resposta genérica para segurança, mas em debug você vê o motivo
  json_ok(["message" => "Se existir uma conta, enviamos um código."] + $extra);
}

json_ok(["message" => "Se existir uma conta, enviamos um código."]);
