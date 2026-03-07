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
