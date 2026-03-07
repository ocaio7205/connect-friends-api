<?php
declare(strict_types=1);
require_once __DIR__ . "/../bootstrap.php";

$meuId = require_login();
if (!csrf_validate()) json_err("CSRF inválido", 403);

$con = db();

/**
 * ✅ AJUSTE AQUI se sua tabela tiver outro nome/colunas:
 * Tabela curtidas:
 * - de_usuario_id (quem curtiu)
 * - para_usuario_id (quem recebeu a curtida)  => você (meuId)
 */
$sql = "
SELECT
  u.id_usuarios AS id,
  u.username AS nome,
  u.data_nascimento
FROM curtidas c
JOIN usuarios u ON u.id_usuarios = c.de_usuario_id
WHERE c.para_usuario_id = ?
ORDER BY c.id DESC
LIMIT 200
";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $meuId);
$stmt->execute();
$res = $stmt->get_result();

$likes = [];
$hoje = new DateTime('now');

while ($row = $res->fetch_assoc()) {
  $idade = 0;
  if (!empty($row['data_nascimento'])) {
    try {
      $n = new DateTime($row['data_nascimento']);
      $idade = $hoje->diff($n)->y;
    } catch (Throwable $e) { $idade = 0; }
  }

  $likes[] = [
    'id' => (int)$row['id'],
    'nome' => (string)$row['nome'],
    'idade' => (int)$idade
  ];
}

json_ok(['likes' => $likes]);