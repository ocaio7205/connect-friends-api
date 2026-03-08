<?php


require_once __DIR__ . "/../bootstrap.php";

if (!csrf_validate()) json_err("CSRF inválido", 403);

start_session_secure();
$email  = (string)($_SESSION['pending_email'] ?? '');
$userId = (int)($_SESSION['pending_user_id'] ?? 0);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$codigo = isset($data['codigo']) ? preg_replace('/\D+/', '', (string)$data['codigo']) : '';

if ($email === '' || $userId <= 0) json_err("Sessão de verificação não encontrada.", 400);
if (strlen($codigo) !== 6) json_err("Código inválido.", 422);

$conn = db();

// Pega o último código válido (não usado e não expirado)
$tipo = 'verificar_email';
$stmt = $conn->prepare(
  "SELECT id, codigo_hash, tentativas, expira_em, usado_em
   FROM email_codigos
   WHERE usuario_id = ? AND email = ? AND tipo = ?
   ORDER BY id DESC
   LIMIT 1"
);
$stmt->bind_param("iss", $userId, $email, $tipo);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) json_err("Nenhum código encontrado. Reenvie o código.", 404);
if (!empty($row['usado_em'])) json_err("Este código já foi utilizado. Reenvie.", 409);

$agora  = new DateTime();
$expira = new DateTime((string)$row['expira_em']);
if ($agora > $expira) json_err("Código expirado. Reenvie.", 410);

$tentativas = (int)$row['tentativas'];
$max = 5; // você pode ajustar
if ($tentativas >= $max) json_err("Muitas tentativas. Reenvie o código.", 429);

$hashDigitado = hash('sha256', $codigo);
if (!hash_equals((string)$row['codigo_hash'], $hashDigitado)) {
  $id = (int)$row['id'];
  $stmt = $conn->prepare("UPDATE email_codigos SET tentativas = tentativas + 1 WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  json_err("Código incorreto.", 401);
}

// Marca verificado no usuário
$stmt = $conn->prepare("UPDATE usuarios SET email_verificado = 1, email_verificado_em = NOW() WHERE id_usuarios = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();

// Marca código como usado
$id = (int)$row['id'];
$stmt = $conn->prepare("UPDATE email_codigos SET usado_em = NOW() WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();

// Loga
$_SESSION['uid'] = $userId;
unset($_SESSION['pending_email'], $_SESSION['pending_user_id']);

json_ok(["message" => "E-mail verificado com sucesso."]);
