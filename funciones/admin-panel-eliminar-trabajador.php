<?php
// labora_db/funciones/admin-panel-eliminar-trabajador.php
session_start();
if (empty($_SESSION['admin'])) {
  header("Location: /labora_db/vistas/admin/admin-login.php");
  exit();
}
require_once __DIR__ . '/../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header("Location: /labora_db/vistas/admin/admin-panel.php#trabajadores");
  exit();
}

// 1) Obtener paths para borrar carpeta
$stmt = $conn->prepare("SELECT dni_frente_path, dni_dorso_path, matricula_path FROM empleado WHERE id_empleado = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$paths = $res ? $res->fetch_assoc() : null;
$stmt->close();

// 2) Borrar registro
$del = $conn->prepare("DELETE FROM empleado WHERE id_empleado = ?");
$del->bind_param("i", $id);
$del->execute();

// 3) Borrar carpeta de verificaciones si existe
function rrmdir_safe($base) {
  if (!is_dir($base)) return;
  $it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
  );
  foreach ($it as $file) {
    if ($file->isDir()) { @rmdir($file->getPathname()); }
    else { @unlink($file->getPathname()); }
  }
  @rmdir($base);
}

if ($paths) {
  // detectar carpeta en base a cualquier path existente
  $any = $paths['dni_frente_path'] ?? $paths['dni_dorso_path'] ?? $paths['matricula_path'] ?? null;
  if ($any) {
    $any = str_replace('\\','/',$any);
    // esperamos "uploads/verificaciones/empleado_{id}/archivo"
    if (preg_match('#^uploads/verificaciones/(empleado_\d+)/#', $any, $m)) {
      $folderRel = 'uploads/verificaciones/' . $m[1];
      $projectRoot = realpath(__DIR__ . '/..'); if ($projectRoot === false) { $projectRoot = dirname(__DIR__); }
      $folderAbs = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folderRel);
      // seguridad: que esté dentro de /uploads/verificaciones
      $verifBase = realpath($projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'verificaciones');
      $folderReal = realpath($folderAbs);
      if ($verifBase && $folderReal && strpos($folderReal, $verifBase) === 0) {
        rrmdir_safe($folderReal);
      }
    }
  }
}

header("Location: /labora_db/vistas/admin/admin-panel.php#trabajadores");
exit();
