
<?php
require_once __DIR__ . "/../bootstrap.php";
$meuId = require_login();

$conn = db();

$sql = "SELECT id_usuarios, username, foto_perfil, bio, data_nascimento
        FROM usuarios WHERE id_usuarios = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meuId);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
if (!$u) json_err("Usuário não encontrado", 404);

// idade
$idade = null;
if (!empty($u['data_nascimento'])) {
  try {
    $nasc = new DateTime($u['data_nascimento']);
    $hoje = new DateTime();
    $idade = $hoje->diff($nasc)->y;
  } catch (Exception $e) { $idade = null; }
}

// interesses (N:N)
$interesses = [];
$sqlI = "SELECT i.nome
         FROM usuarios_interesses ui
         INNER JOIN interesses i ON i.id = ui.interesse_id
         WHERE ui.usuario_id = ?
         ORDER BY i.nome";
$stmt2 = $conn->prepare($sqlI);
$stmt2->bind_param("i", $meuId);
$stmt2->execute();
$resI = $stmt2->get_result();
while ($row = $resI->fetch_assoc()) $interesses[] = $row['nome'];

// fotos grade (fotos_perfil)
$fotos = [];
$sqlF = "SELECT foto_url, ordem
         FROM fotos_perfil
         WHERE usuario_id = ?
         ORDER BY ordem ASC, id ASC
         LIMIT 6";
$stmt3 = $conn->prepare($sqlF);
$stmt3->bind_param("i", $meuId);
$stmt3->execute();
$resF = $stmt3->get_result();
while ($row = $resF->fetch_assoc()) {
  if (!empty($row['foto_url'])) $fotos[] = $row['foto_url'];
}

json_ok([
  "user" => [
    "id" => (int)$u["id_usuarios"],
    "username" => (string)$u["username"],
    "foto_perfil" => $u["foto_perfil"] ? (string)$u["foto_perfil"] : null,
    "bio" => $u["bio"] ? (string)$u["bio"] : null,
    "idade" => $idade,
    "interesses" => $interesses,
    "fotos" => $fotos,
  ]
]);