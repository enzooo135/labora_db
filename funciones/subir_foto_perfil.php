<?php
// ../../funciones/subir_foto_perfil.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['empleado_id'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'No autenticado']);
  exit;
}

require '../config/conexion.php';

$id = (int)$_SESSION['empleado_id'];
$DIR = __DIR__ . '/../uploads/Foto_Perfil';
$BASE_PUBLICA = '/labora_db/uploads/Foto_Perfil/';

if (!is_dir($DIR)) { @mkdir($DIR, 0755, true); }

if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'No llegó el archivo']);
  exit;
}

$tmp  = $_FILES['foto']['tmp_name'];
$size = $_FILES['foto']['size'];

if ($size > 3*1024*1024) {
  http_response_code(413);
  echo json_encode(['ok' => false, 'error' => 'Máximo 3MB']);
  exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $tmp);
finfo_close($finfo);

if (!in_array($mime, ['image/jpeg','image/png'], true)) {
  http_response_code(415);
  echo json_encode(['ok' => false, 'error' => 'Solo JPG o PNG']);
  exit;
}

// Traigo nombre y foto actual
$q = $conn->prepare("SELECT nombre, foto_perfil FROM empleado WHERE id_empleado=?");
$q->bind_param("i", $id);
$q->execute();
$res = $q->get_result();
$row = $res->fetch_assoc();
$q->close();

$nombre = $row['nombre'] ?: ('Empleado '.$id);
// quitar caracteres ilegales de nombre de archivo (dejamos espacios/acentos)
$base   = preg_replace('/[\\\\\/:*?"<>|]/', '', trim($nombre));
if ($base === '') $base = 'Empleado';

// Nombre final FIJO
$filename = $base . '_' . $id . '.jpg';
$dest     = $DIR . '/' . $filename;

// Convertir/guardar como JPG SIEMPRE
$info = @getimagesize($tmp);
if (!$info) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Imagen inválida']);
  exit;
}

if ($mime === 'image/jpeg') {
  $img = imagecreatefromjpeg($tmp);
} else { // png
  $src = imagecreatefrompng($tmp);
  $w = imagesx($src); $h = imagesy($src);
  $img = imagecreatetruecolor($w, $h);
  $white = imagecolorallocate($img, 255, 255, 255);
  imagefilledrectangle($img, 0, 0, $w, $h, $white);
  imagecopy($img, $src, 0, 0, 0, 0, $w, $h);
  imagedestroy($src);
}

if (!$img || !imagejpeg($img, $dest, 85)) {
  if ($img) imagedestroy($img);
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'No se pudo guardar la imagen']);
  exit;
}
imagedestroy($img);

// Borrar anterior si es distinto
if (!empty($row['foto_perfil']) && $row['foto_perfil'] !== $filename) {
  $old = $DIR . '/' . $row['foto_perfil'];
  if (is_file($old)) @unlink($old);
}

// Guardar en BD
$u = $conn->prepare("UPDATE empleado SET foto_perfil=? WHERE id_empleado=?");
$u->bind_param("si", $filename, $id);
$u->execute();
$u->close();

// Respuesta
echo json_encode([
  'ok'       => true,
  'foto'     => $filename,
  'foto_url' => $BASE_PUBLICA . rawurlencode($filename)
]);
