<?php
declare(strict_types=1);

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
/**
 * Connect Friends - bootstrap.php
 * Render + Railway MySQL
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Segurança básica de headers
 */
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: same-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

/**
 * Conexão com banco
 */
>>>>>>> d59653d81926c2431c9378841d721e67246f1c46
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

/**
 * Retorna conexão
 */
function db(): mysqli
{
    global $conexao;
    return $conexao;
}

/**
 * Respostas JSON
 */
function json_ok(array $data = [], int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $msg, int $code = 400, array $extra = []): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $msg] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * CSRF
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate(?string $token = null): bool
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return true;
    }

    if ($token === null || $token === '') {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? ($_POST['csrf_token'] ?? '');

        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if ($token === '' && stripos($ct, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['csrf_token'])) {
                $token = (string)$data['csrf_token'];
            }
        }
    }

    $sess = (string)($_SESSION['csrf_token'] ?? '');
    return $sess !== '' && $token !== '' && hash_equals($sess, $token);
}

/**
 * Login / sessão
 * Ajustado para aceitar user_id e id_usuario
 */
function current_user_id(): ?int
{
    if (isset($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }

    if (isset($_SESSION['id_usuario'])) {
        return (int)$_SESSION['id_usuario'];
    }

    return null;
}

function require_login(): int
{
    $uid = current_user_id();

    if (!$uid) {
        header('Location: /capa.php');
        exit;
    }

    return $uid;
}

/**
 * Helpers de sessão
 */
function login_user(int $userId): void
{
    $_SESSION['user_id'] = $userId;
    $_SESSION['id_usuario'] = $userId;
    session_regenerate_id(true);
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool)$params['secure'],
            (bool)$params['httponly']
        );
    }

    session_destroy();
}

/**
 * Escape HTML
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
>>>>>>> 665cc278062e96d09826f42e686cf449116b9ab9
