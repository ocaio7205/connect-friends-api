<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÃO DO BANCO
|--------------------------------------------------------------------------
*/

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'connect_friends';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*
|--------------------------------------------------------------------------
| CONEXÃO COM BANCO
|--------------------------------------------------------------------------
*/

function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');

    return $conn;
}

/*
|--------------------------------------------------------------------------
| SESSÃO SEGURA
|--------------------------------------------------------------------------
*/

function start_session_secure(): void
{
    if (session_status() === PHP_SESSION_NONE) {

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        session_set_cookie_params([
            'httponly' => true,
            'secure'   => $https,
            'samesite' => 'Lax'
        ]);

        session_start();
    }
}

/*
|--------------------------------------------------------------------------
| HEADERS DE SEGURANÇA
|--------------------------------------------------------------------------
*/

function security_headers(): void
{
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: same-origin");
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

function csrf_token(): string
{
    start_session_secure();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_validate(?string $token = null): bool
{
    start_session_secure();

    $sess = $_SESSION['csrf_token'] ?? '';

    return $sess && $token && hash_equals($sess, $token);
}

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

function current_user_id(): ?int
{
    if (isset($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }

    return null;
}

function require_login(): int
{
    start_session_secure();

    $uid = current_user_id();

    if (!$uid) {
        header("Location: capa.php");
        exit;
    }

    return $uid;
}

/*
|--------------------------------------------------------------------------
| JSON HELPERS
|--------------------------------------------------------------------------
*/

function json_ok(array $data = [], int $code = 200): void
{
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode(["ok" => true] + $data);
    exit;
}

function json_err(string $msg, int $code = 400): void
{
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode(["ok" => false, "error" => $msg]);
    exit;
}

/*
|--------------------------------------------------------------------------
| INICIALIZAÇÃO
|--------------------------------------------------------------------------
*/

security_headers();
start_session_secure();