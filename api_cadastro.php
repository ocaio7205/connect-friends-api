<?php
declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";

// Só aceita POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  json_err("Método inválido", 405);
}

// CSRF
if (!csrf_validate()) {
  json_err("CSRF inválido", 419);
}

// Lê JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
  json_err("JSON inválido", 400);
}

$username = trim((string)($data['username'] ?? ''));
$email    = trim((string)($data['email'] ?? ''));
$senha    = (string)($data['senha'] ?? '');
$nasc     = trim((string)($data['data_nascimento'] ?? ''));

// Validações básicas
if ($username === '' || mb_strlen($username) < 2) {
  json_err("Informe um nome válido.", 422);
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_err("E-mail inválido.", 422);
}

// Mesma regra do front: 8+, número e símbolo
if (!preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/', $senha)) {
  json_err("Senha fraca. Use 8+ caracteres, número e símbolo.", 422);
}

if ($nasc === '') {
  json_err("Informe sua data de nascimento.", 422);
}

// Valida idade >= 18
try {
  $dn = new DateTime($nasc);
  $hoje = new DateTime();
  $idade = $hoje->diff($dn)->y;
  if ($idade < 18) json_err("Acesso negado: mínimo 18 anos.", 403);
} catch (Throwable $e) {
  json_err("Data de nascimento inválida.", 422);
}

$conn = db();

// Evita e-mail duplicado
$stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
  json_err("Este e-mail já está cadastrado.", 409);
}

// Hash da senha
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere usuário (sua tabela usa coluna senha)
$stmt = $conn->prepare("INSERT INTO usuarios (username, email, senha, data_nascimento) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $hash, $nasc);
$stmt->execute();

$userId = (int)$conn->insert_id;

// Sessão pendente para confirmação
start_session_secure();
$_SESSION['pending_email'] = $email;
$_SESSION['pending_user_id'] = $userId;

// Melhor UX: envia automaticamente o código
$send = send_verification_code($conn, $userId, $email);
if (!$send['ok']) {
  // Cadastro criado, mas sem e-mail. Usuário ainda pode clicar "Reenviar".
  $extra = [];
  if (defined('APP_DEBUG') && APP_DEBUG && isset($send['error'])) {
    $extra['debug'] = $send['error'];
  }
  json_ok([
    "needs_verification" => true,
    "message" => "Cadastro criado, mas não foi possível enviar o e-mail agora. Use 'Reenviar código'.",
  ] + $extra, 200);
}

json_ok([
  "needs_verification" => true,
  "message" => "Cadastro criado. Enviamos um código para seu e-mail.",
]);
