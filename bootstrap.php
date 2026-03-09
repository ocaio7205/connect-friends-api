<?php

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÃO DO BANCO (InfinityFree)
|--------------------------------------------------------------------------
*/

// Suas credenciais diretas
$host = "sql211.infinityfree.com";
$user = "if0_41341273";
$pass = "Mg8BHCscFTWL3NT"; // Lembre-se de substituir pela sua senha real
$db   = "if0_41341273_connectfriends";
$port = 3306;

// Tenta conectar usando as variáveis acima
$conexao = new mysqli($host, $user, $pass, $db, $port);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset('utf8mb4');

// Ativa o reporte de erros para facilitar o debug
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*
|--------------------------------------------------------------------------
| FUNÇÃO DE CONEXÃO GLOBAL
|--------------------------------------------------------------------------
*/

function db(): mysqli
{
    global $host, $user, $pass, $db, $port;
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $conn = new mysqli($host, $user, $pass, $db, $port);
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
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $https,
            'httponly' => true,
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
| CSRF (Proteção contra falsificação de solicitação)
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
| LOGIN & CONTROLE DE ACESSO
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
| JSON HELPERS (Para APIs/Requisições AJAX)
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
| INICIALIZAÇÃO AUTOMÁTICA
|--------------------------------------------------------------------------
*/

security_headers();
start_session_secure();