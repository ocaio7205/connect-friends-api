<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$uid = require_login();

try {
  $con = db();

  // Gold = existe assinatura ATIVA e dentro do período
  $sql = "
    SELECT 1
    FROM assinaturas
    WHERE id_usuario = ?
      AND status = 'ativa'
      AND data_inicio <= CURDATE()
      AND data_fim >= CURDATE()
    LIMIT 1
  ";
  $stmt = $con->prepare($sql);
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $res = $stmt->get_result();

  $isGold = ($res->num_rows > 0);

  json_ok([
    "user" => [
      "id" => $uid,
      "is_gold" => $isGold
    ]
  ]);
} catch (Throwable $e) {
  json_err("Erro ao carregar status", 500);
}