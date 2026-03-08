<?php


require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();
$con = db();

$sql = "
SELECT
  m.id AS match_id,
  CASE WHEN m.user1_id = ? THEN m.user2_id ELSE m.user1_id END AS other_id,
  u.username AS other_name,
  COALESCE(u.foto_perfil, '') AS other_photo,
  lm.tipo AS last_tipo,
  lm.mensagem AS last_msg,
  lm.data_envio AS last_time
FROM matches m
JOIN usuarios u
  ON u.id_usuarios = (CASE WHEN m.user1_id = ? THEN m.user2_id ELSE m.user1_id END)
LEFT JOIN mensagens lm
  ON lm.id = (
    SELECT mm.id
    FROM mensagens mm
    WHERE mm.match_id = m.id
    ORDER BY mm.data_envio DESC, mm.id DESC
    LIMIT 1
  )
WHERE (m.user1_id = ? OR m.user2_id = ?)
  AND m.status = 'ativo'
ORDER BY COALESCE(lm.data_envio, m.data_match) DESC
";

$stmt = $con->prepare($sql);
$stmt->bind_param("iiii", $meuId, $meuId, $meuId, $meuId);
$stmt->execute();
$res = $stmt->get_result();

$chats = [];
while ($row = $res->fetch_assoc()) {
  $ultima = "";
  if (!empty($row["last_tipo"])) {
    $ultima = ($row["last_tipo"] === "imagem") ? "📷 Foto" : (string)($row["last_msg"] ?? "");
  }
  $chats[] = [
    "match_id" => (int)$row["match_id"],
    "other_id" => (int)$row["other_id"],
    "nome" => (string)$row["other_name"],
    "foto" => (string)$row["other_photo"],
    "ultimaMensagem" => $ultima,
  ];
}

json_ok(["chats" => $chats]);