<<<<<<< HEAD
<?php
require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  json_err("Método não permitido", 405);
}
if (!csrf_validate()) {
  json_err("CSRF inválido", 403);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
  json_err("JSON inválido", 422);
}

$conn = db();

/**
 * 1) min_fotos / apenas_com_bio -> preferencias_usuario
 */
if (isset($data['min_fotos']) || isset($data['apenas_com_bio'])) {
  $minFotos = isset($data['min_fotos']) ? (int)$data['min_fotos'] : null;
  $comBio   = isset($data['apenas_com_bio']) ? (int)$data['apenas_com_bio'] : null;

  if ($minFotos !== null && ($minFotos < 1 || $minFotos > 6)) {
    json_err("min_fotos inválido", 422);
  }
  if ($comBio !== null && !in_array($comBio, [0,1], true)) {
    json_err("apenas_com_bio inválido", 422);
  }

  // upsert (insere se não existe, senão atualiza)
  $sql = "
    INSERT INTO preferencias_usuario (usuario_id, min_fotos, apenas_com_bio)
    VALUES (?, COALESCE(?, 1), COALESCE(?, 0))
    ON DUPLICATE KEY UPDATE
      min_fotos = COALESCE(VALUES(min_fotos), min_fotos),
      apenas_com_bio = COALESCE(VALUES(apenas_com_bio), apenas_com_bio),
      atualizado_em = CURRENT_TIMESTAMP
  ";
  $stmt = $conn->prepare($sql);
  // ii i (minFotos e comBio podem ser null -> manda como null mesmo)
  $stmt->bind_param("iii", $meuId, $minFotos, $comBio);
  $stmt->execute();

  json_ok();
}

/**
 * 2) campo/valor -> salva em preferencias_usuario (colunas textuais)
 * Mapeia os nomes do seu modal para colunas existentes na tabela.
 */
if (isset($data['campo'], $data['valor'])) {
  $campo = (string)$data['campo'];
  $valor = trim((string)$data['valor']);

  // mapeamento: título do modal -> coluna no banco
  $map = [
    'Tô procurando' => 'procurando',
    'Me interesso por...' => 'interessado_em',
    'Signo' => 'signo',
    'Formação' => 'formacao',
    'Família' => 'familia',
    'Estilo de comunicação' => 'estilo_comunicacao',
    'Linguagem do amor' => 'linguagem_amor',
    'Pets' => 'pets',
    'Bebida' => 'bebida',
    'Você fuma?' => 'fuma',
    'Atividade física' => 'atividade_fisica',
    // 'Interesses' aqui você pode depois trocar para N:N com usuarios_interesses
    'Interesses' => 'interesses',
  ];

  if (!isset($map[$campo])) {
    json_err("Campo não suportado ainda", 422);
  }

  $col = $map[$campo];

  // garante que existe um registro em preferencias_usuario
  $conn->query("INSERT IGNORE INTO preferencias_usuario (usuario_id) VALUES ($meuId)");

  // Atualiza a coluna permitida (sem SQL injection: coluna vem de whitelist)
  $sql = "UPDATE preferencias_usuario SET {$col} = ?, atualizado_em = CURRENT_TIMESTAMP WHERE usuario_id = ? LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("si", $valor, $meuId);
  $stmt->execute();

  json_ok();
}

=======
<?php
require_once __DIR__ . "/bootstrap.php";

$meuId = require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  json_err("Método não permitido", 405);
}
if (!csrf_validate()) {
  json_err("CSRF inválido", 403);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
  json_err("JSON inválido", 422);
}

$conn = db();

/**
 * 1) min_fotos / apenas_com_bio -> preferencias_usuario
 */
if (isset($data['min_fotos']) || isset($data['apenas_com_bio'])) {
  $minFotos = isset($data['min_fotos']) ? (int)$data['min_fotos'] : null;
  $comBio   = isset($data['apenas_com_bio']) ? (int)$data['apenas_com_bio'] : null;

  if ($minFotos !== null && ($minFotos < 1 || $minFotos > 6)) {
    json_err("min_fotos inválido", 422);
  }
  if ($comBio !== null && !in_array($comBio, [0,1], true)) {
    json_err("apenas_com_bio inválido", 422);
  }

  // upsert (insere se não existe, senão atualiza)
  $sql = "
    INSERT INTO preferencias_usuario (usuario_id, min_fotos, apenas_com_bio)
    VALUES (?, COALESCE(?, 1), COALESCE(?, 0))
    ON DUPLICATE KEY UPDATE
      min_fotos = COALESCE(VALUES(min_fotos), min_fotos),
      apenas_com_bio = COALESCE(VALUES(apenas_com_bio), apenas_com_bio),
      atualizado_em = CURRENT_TIMESTAMP
  ";
  $stmt = $conn->prepare($sql);
  // ii i (minFotos e comBio podem ser null -> manda como null mesmo)
  $stmt->bind_param("iii", $meuId, $minFotos, $comBio);
  $stmt->execute();

  json_ok();
}

/**
 * 2) campo/valor -> salva em preferencias_usuario (colunas textuais)
 * Mapeia os nomes do seu modal para colunas existentes na tabela.
 */
if (isset($data['campo'], $data['valor'])) {
  $campo = (string)$data['campo'];
  $valor = trim((string)$data['valor']);

  // mapeamento: título do modal -> coluna no banco
  $map = [
    'Tô procurando' => 'procurando',
    'Me interesso por...' => 'interessado_em',
    'Signo' => 'signo',
    'Formação' => 'formacao',
    'Família' => 'familia',
    'Estilo de comunicação' => 'estilo_comunicacao',
    'Linguagem do amor' => 'linguagem_amor',
    'Pets' => 'pets',
    'Bebida' => 'bebida',
    'Você fuma?' => 'fuma',
    'Atividade física' => 'atividade_fisica',
    // 'Interesses' aqui você pode depois trocar para N:N com usuarios_interesses
    'Interesses' => 'interesses',
  ];

  if (!isset($map[$campo])) {
    json_err("Campo não suportado ainda", 422);
  }

  $col = $map[$campo];

  // garante que existe um registro em preferencias_usuario
  $conn->query("INSERT IGNORE INTO preferencias_usuario (usuario_id) VALUES ($meuId)");

  // Atualiza a coluna permitida (sem SQL injection: coluna vem de whitelist)
  $sql = "UPDATE preferencias_usuario SET {$col} = ?, atualizado_em = CURRENT_TIMESTAMP WHERE usuario_id = ? LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("si", $valor, $meuId);
  $stmt->execute();

  json_ok();
}

>>>>>>> 665cc278062e96d09826f42e686cf449116b9ab9
json_err("Nada para salvar", 422);