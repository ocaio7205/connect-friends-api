<?php
require_once __DIR__ . "/../bootstrap.php";

if (!csrf_validate()) json_err("CSRF inválido", 403);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$email  = isset($data['email'])  ? trim((string)$data['email'])  : '';
$codigo = isset($data['codigo']) ? preg_replace('/\D+/', '', (string)$data['codigo']) : '';
$senha  = isset($data['senha'])  ? (string)$data['senha'] : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) json_err("E-mail inválido.", 422);
if (strlen($codigo) !== 6) json_err("Código inválido.", 422);
if (strlen($senha) < 8) json_err("A senha deve ter no mínimo 8 caracteres.", 422);

$conn = db();

/** Busca o usuário */
$stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$u = $res->fetch_assoc();

if (!$u) json_err("Código inválido ou expirado.", 401);
$userId = (int)$u['id_usuarios'];

/** Pega o último token não usado e não expirado */
$stmt = $conn->prepare("
  SELECT id, token_hash, expira_em, usado_em
  FROM redefinicao_senha
  WHERE id_usuario = ?
  ORDER BY criado_em DESC
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) json_err("Código inválido ou expirado.", 401);
if (!empty($row['usado_em'])) json_err("Código já utilizado. Solicite outro.", 409);

$agora = new DateTime();
$expira = new DateTime($row['expira_em']);
if ($agora > $expira) json_err("Código expirado. Solicite outro.", 410);

$hashDigitado = hash('sha256', $codigo);
if (!hash_equals((string)$row['token_hash'], $hashDigitado)) {
  json_err("Código incorreto.", 401);
}

/** Atualiza senha */
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id_usuarios = ? LIMIT 1");
$stmt->bind_param("si", $senhaHash, $userId);
$stmt->execute();

/** Marca token como usado */
$tokenId = (int)$row['id'];
$stmt = $conn->prepare("UPDATE redefinicao_senha SET usado_em = NOW() WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $tokenId);
$stmt->execute();

json_ok(["message" => "Senha redefinida com sucesso."]);