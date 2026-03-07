<?php
declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();
$conn  = db();

if (!csrf_validate()) json_err("CSRF inválido", 403);

$alvoId = (int)($_POST['usuario_id'] ?? 0);
if ($alvoId <= 0) json_err("Usuário inválido", 400);
if ($alvoId === $meuId) json_err("Ação inválida", 400);

/**
 * Tabela esperada:
 * bloqueios(bloqueador_id, bloqueado_id, data_bloqueio)
 * e UNIQUE(bloqueador_id, bloqueado_id) (ideal)
 */
$sql = "INSERT INTO bloqueios (bloqueador_id, bloqueado_id, data_bloqueio)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE data_bloqueio = NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $meuId, $alvoId);
$stmt->execute();

json_ok(["message" => "Bloqueado"]);