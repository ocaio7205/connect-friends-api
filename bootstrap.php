<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST') ?: '';
$port = (int)(getenv('DB_PORT') ?: 3306);
$db   = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

if ($host === '' || $db === '' || $user === '') {
    http_response_code(500);
    exit('Configuração de banco incompleta.');
}

try {
    $conexao = new mysqli($host, $user, $pass, $db, $port);
    $conexao->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Erro de conexão com banco: ' . $e->getMessage());
    http_response_code(500);
    exit('Erro ao conectar.');
} 
session_start();

function require_login(): int {
  if (!isset($_SESSION['user_id'])) {
    header("Location: /capa.php");
    exit;
  }
  return (int)$_SESSION['user_id'];
}

function json_ok(array $data = []) {
  header('Content-Type: application/json');
  echo json_encode(['ok' => true] + $data);
  exit;
}

function json_err(string $msg, int $code = 400) {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => $msg]);
  exit;
}

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}
