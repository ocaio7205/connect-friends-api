<<<<<<< HEAD
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
 * Tabela matches esperada (igual a que você mostrou antes):
 * matches(user1_id, user2_id, status, par_key gerada, etc)
 * - e UNIQUE(par_key)
 */
$sql = "INSERT INTO matches (user1_id, user2_id, status)
        VALUES (?, ?, 'ativo')
        ON DUPLICATE KEY UPDATE status = 'ativo', atualizado_em = NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $meuId, $alvoId);
$stmt->execute();

=======
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
 * Tabela matches esperada (igual a que você mostrou antes):
 * matches(user1_id, user2_id, status, par_key gerada, etc)
 * - e UNIQUE(par_key)
 */
$sql = "INSERT INTO matches (user1_id, user2_id, status)
        VALUES (?, ?, 'ativo')
        ON DUPLICATE KEY UPDATE status = 'ativo', atualizado_em = NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $meuId, $alvoId);
$stmt->execute();

>>>>>>> 665cc278062e96d09826f42e686cf449116b9ab9
json_ok(["message" => "Match salvo"]);