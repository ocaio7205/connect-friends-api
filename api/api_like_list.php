<?php


require_once __DIR__ . '/bootstrap.php';

$uid = require_login();

try {
  $con = db();

  // Pega "foto" priorizando: fotos_perfil.ordem=1 (url) > usuarios.foto_perfil > fallback pravatar
  // Idade calculada por data_nascimento (se não tiver, cai pra 22)
  $sql = "
    SELECT
      u.id_usuarios AS id,
      u.username AS nome,
      COALESCE(TIMESTAMPDIFF(YEAR, u.data_nascimento, CURDATE()), 22) AS idade,
      COALESCE(fp1.foto_url, u.foto_perfil, CONCAT('https://i.pravatar.cc/300?u=', u.id_usuarios)) AS foto
    FROM curtidas_perfil cp
    INNER JOIN usuarios u
      ON u.id_usuarios = cp.usuario_curtiu
    LEFT JOIN fotos_perfil fp1
      ON fp1.usuario_id = u.id_usuarios AND fp1.ordem = 1
    WHERE cp.usuario_curtido = ?
    ORDER BY cp.data_curtida DESC
    LIMIT 200
  ";

  $stmt = $con->prepare($sql);
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $res = $stmt->get_result();

  $likes = [];
  while ($row = $res->fetch_assoc()) {
    $likes[] = [
      "id" => (int)$row["id"],
      "nome" => (string)$row["nome"],
      "idade" => (int)$row["idade"],
      "foto" => (string)$row["foto"],
    ];
  }

  json_ok(["likes" => $likes]);
} catch (Throwable $e) {
  json_err("Erro ao carregar curtidas", 500);
}