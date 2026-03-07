<<<<<<< HEAD
<?php
declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
$ord = isset($_GET['ord']) ? (int)$_GET['ord'] : 1;

if ($uid <= 0) { http_response_code(404); exit; }
if ($ord < 1 || $ord > 6) $ord = 1;

$conn = db();

/**
 * Sua tabela:
 * - usuario_id (int)
 * - foto_url (text, pode ser NULL)
 * - foto_blob (longblob, pode ser NULL)
 * - ordem (int)
 */
$sql = "SELECT foto_blob, foto_url
        FROM fotos_perfil
        WHERE usuario_id = ? AND ordem = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $uid, $ord);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) { http_response_code(404); exit; }

$row = $res->fetch_assoc();

/**
 * 1) Preferência: foto_blob
 * 2) Se não tiver blob, tenta foto_url (redirect)
 */
$blob = $row['foto_blob'] ?? null;
$url  = $row['foto_url'] ?? null;

if (!empty($blob)) {
  // Tenta detectar mime pelo "magic bytes" (bom o suficiente pro básico)
  $mime = 'image/jpeg';
  $sig = substr($blob, 0, 12);

  if (strncmp($sig, "\x89PNG\r\n\x1a\n", 8) === 0) $mime = 'image/png';
  elseif (strncmp($sig, "GIF87a", 6) === 0 || strncmp($sig, "GIF89a", 6) === 0) $mime = 'image/gif';
  elseif (strncmp($sig, "RIFF", 4) === 0 && strpos($sig, "WEBP") !== false) $mime = 'image/webp';

  header("Content-Type: {$mime}");
  header("Cache-Control: public, max-age=86400");
  echo $blob;
  exit;
}

if (!empty($url)) {
  // Se você guardar URL externa, redireciona
  header("Location: " . $url, true, 302);
  exit;
}

http_response_code(404);
=======
<?php
declare(strict_types=1);

require_once __DIR__ . "/bootstrap.php";

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
$ord = isset($_GET['ord']) ? (int)$_GET['ord'] : 1;

if ($uid <= 0) { http_response_code(404); exit; }
if ($ord < 1 || $ord > 6) $ord = 1;

$conn = db();

/**
 * Sua tabela:
 * - usuario_id (int)
 * - foto_url (text, pode ser NULL)
 * - foto_blob (longblob, pode ser NULL)
 * - ordem (int)
 */
$sql = "SELECT foto_blob, foto_url
        FROM fotos_perfil
        WHERE usuario_id = ? AND ordem = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $uid, $ord);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) { http_response_code(404); exit; }

$row = $res->fetch_assoc();

/**
 * 1) Preferência: foto_blob
 * 2) Se não tiver blob, tenta foto_url (redirect)
 */
$blob = $row['foto_blob'] ?? null;
$url  = $row['foto_url'] ?? null;

if (!empty($blob)) {
  // Tenta detectar mime pelo "magic bytes" (bom o suficiente pro básico)
  $mime = 'image/jpeg';
  $sig = substr($blob, 0, 12);

  if (strncmp($sig, "\x89PNG\r\n\x1a\n", 8) === 0) $mime = 'image/png';
  elseif (strncmp($sig, "GIF87a", 6) === 0 || strncmp($sig, "GIF89a", 6) === 0) $mime = 'image/gif';
  elseif (strncmp($sig, "RIFF", 4) === 0 && strpos($sig, "WEBP") !== false) $mime = 'image/webp';

  header("Content-Type: {$mime}");
  header("Cache-Control: public, max-age=86400");
  echo $blob;
  exit;
}

if (!empty($url)) {
  // Se você guardar URL externa, redireciona
  header("Location: " . $url, true, 302);
  exit;
}

http_response_code(404);
>>>>>>> 665cc278062e96d09826f42e686cf449116b9ab9
exit;