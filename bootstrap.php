<?php
declare(strict_types=1);

<<<<<<< HEAD
/**
 * BOOTSTRAP ÚNICO DO PROJETO
 * - Conexão segura mysqli
 * - Sessão segura
 * - Headers de segurança
 * - CSRF
 * - Helpers JSON
 * - E-mail (PHPMailer SMTP)
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ========= AMBIENTE ========= */
// Em localhost, deixe true pra ver mensagens de erro mais úteis.
const APP_DEBUG = true;

/* ========= CONFIG DO BANCO (mude só aqui) ========= */
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'connect_friends';

/* ========= SMTP (mude só aqui) =========
 * Use Mailtrap (teste) ou Gmail (com Senha de App).
 * Se estiver vazio, o envio vai falhar.
 */
const SMTP_HOST = 'smtp.gmail.com';            // ex: smtp.gmail.com
const SMTP_USER = 'connectfriendsofc@gmail.com';            // ex: seuemail@gmail.com
const SMTP_PASS = 'ilmr gwzj pizb xcas';            // ex: senha de app (16 chars)
const SMTP_PORT = 587;           // 587 (TLS) ou 465 (SSL)
const SMTP_SECURE = 'tls';       // 'tls' ou 'ssl'
const SMTP_FROM = 'connectfriendsofc@gmail.com';            // ex: seuemail@gmail.com
const SMTP_FROM_NAME = 'Connect Friends';

/* ========= HEADERS DE SEGURANÇA ========= */
function security_headers(): void {
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: same-origin');
  header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
  header('X-Frame-Options: SAMEORIGIN');
  // CSP: mantenho simples para não quebrar CDN/Tailwind no seu front.
}

/* ========= SESSÃO SEGURA ========= */
function start_session_secure(): void {
  if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
      'httponly' => true,
      'secure'   => $isHttps,
      'samesite' => 'Lax',
    ]);
    session_start();
  }
}

/* ========= CONEXÃO ÚNICA ========= */
function db(): mysqli {
  static $conn = null;
  if ($conn instanceof mysqli) return $conn;

  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  $conn->set_charset('utf8mb4');
  return $conn;
}

/* ========= JSON helpers (pra endpoints) ========= */
function json_ok(array $data = [], int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
  exit;
}

function json_err(string $msg, int $code = 400, array $extra = []): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => false, 'error' => $msg] + $extra, JSON_UNESCAPED_UNICODE);
  exit;
}

/* ========= CSRF ========= */
function csrf_token(): string {
  start_session_secure();
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return (string)$_SESSION['csrf_token'];
}

function csrf_validate(?string $token = null): bool {
  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
  if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) return true;

  if ($token === null || $token === '') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if ($token === '' && strpos($ct, 'application/json') !== false) {
      $raw = file_get_contents('php://input');
      $data = json_decode($raw, true);
      if (is_array($data) && isset($data['csrf_token'])) {
        $token = (string)$data['csrf_token'];
      }
    }
  }

  start_session_secure();
  $sess = (string)($_SESSION['csrf_token'] ?? '');
  return $sess !== '' && $token !== '' && hash_equals($sess, (string)$token);
}

/* ========= LOGIN HELPERS ========= */
function require_login(): int {
  start_session_secure();

  $uid = (int)($_SESSION['uid'] ?? 0);
  if ($uid > 0) return $uid;

  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
  $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
  $isJson = (strpos($accept, 'application/json') !== false) || ($xhr === 'XMLHttpRequest');

  if ($isJson) json_err('Não autenticado', 401);

  header('Location: capa.php');
  exit;
}

/* ========= E-mail (PHPMailer) ========= */
require_once __DIR__ . '/mailer.php';

/* ========= Inicializa segurança automaticamente ========= */
security_headers();
start_session_secure();
=======
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
>>>>>>> 665cc278062e96d09826f42e686cf449116b9ab9
