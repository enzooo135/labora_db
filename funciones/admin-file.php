<?php
// admin-file.php — servir archivos de /uploads/verificaciones de forma segura y robusta

session_start();
if (empty($_SESSION['admin'])) {
  http_response_code(403);
  exit('Acceso denegado');
}

// ---- Endurecer salida: nada de errores mezclados con el binario ----
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
@ini_set('zend.assertions', '-1');
error_reporting(E_ALL);

// ---- Obtener y normalizar parámetro ----
$rel = $_GET['f'] ?? '';
if ($rel === '') { http_response_code(400); exit('Faltan parámetros'); }

$rel = str_replace('\\', '/', $rel);
$rel = ltrim($rel, '/');

// Aceptar rutas con o sin el prefijo "uploads/verificaciones/"
$prefixes = [
  'uploads/verificaciones/',
  '/uploads/verificaciones/',
  'verificaciones/',
  '/verificaciones/',
];
foreach ($prefixes as $pref) {
  if (stripos($rel, $pref) === 0) {
    $rel = substr($rel, strlen($pref));
    break;
  }
}

// Seguridad básica
if (strpos($rel, '..') !== false) {
  http_response_code(400);
  exit('Ruta inválida');
}

// ---- Detectar raíz del proyecto (labora_db) ----
function find_project_root(string $startDir): ?string {
  $dir = $startDir;
  for ($i = 0; $i < 8; $i++) {
    $uploads = $dir . DIRECTORY_SEPARATOR . 'uploads';
    $verif   = $uploads . DIRECTORY_SEPARATOR . 'verificaciones';
    $config  = $dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'conexion.php';
    if (is_dir($verif) || is_file($config)) {
      $real = realpath($dir);
      return $real ?: $dir;
    }
    $parent = dirname($dir);
    if ($parent === $dir) break;
    $dir = $parent;
  }
  return null;
}

$projectRoot = find_project_root(__DIR__);
if ($projectRoot === null) {
  // Fallback
  $projectRoot = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
}

$verifBase = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'verificaciones';
$verifBaseReal = realpath($verifBase);
if ($verifBaseReal === false) {
  http_response_code(404);
  exit('No encontrado');
}

// $rel debe ser "empleado_X/archivo.ext"
$fullPath = $verifBaseReal . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
$fullPathReal = realpath($fullPath);

// Validaciones
if (!$fullPathReal || strpos($fullPathReal, $verifBaseReal) !== 0 || !is_file($fullPathReal) || !is_readable($fullPathReal)) {
  http_response_code(404);
  exit('Archivo no encontrado');
}

// ---- Preparar headers y enviar ----
$filename = basename($fullPathReal);
$size = filesize($fullPathReal);
$mime = 'application/octet-stream';
if (function_exists('mime_content_type')) {
  $tmpMime = @mime_content_type($fullPathReal);
  if ($tmpMime) $mime = $tmpMime;
}

// Limpiar cualquier buffer previo
while (ob_get_level() > 0) { @ob_end_clean(); }

// Headers seguros
header('Content-Type: ' . $mime);
if ($size !== false) {
  header('Content-Length: ' . $size);
}
header('Content-Disposition: inline; filename="' . rawurlencode($filename) . '"');
header('Accept-Ranges: none');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');

// Enviar el archivo
$fp = @fopen($fullPathReal, 'rb');
if ($fp === false) {
  http_response_code(500);
  exit('No se pudo abrir el archivo');
}
fpassthru($fp);
fclose($fp);
exit;
