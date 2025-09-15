<?php
// labora_db/funciones/admin-panel-editar-trabajador.php
session_start();
if (empty($_SESSION['admin'])) {
  header("Location: /labora_db/vistas/admin/admin-login.php");
  exit();
}
require_once __DIR__ . '/../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id_empleado'] ?? 0);
  if ($id <= 0) { header("Location: /labora_db/vistas/admin/admin-panel.php#trabajadores"); exit(); }

  // Campos editables (ajustá a gusto)
  $nombre   = trim($_POST['nombre'] ?? '');
  $correo   = trim($_POST['correo'] ?? '');
  $prof     = trim($_POST['profesion'] ?? '');
  $zona     = trim($_POST['zona_trabajo'] ?? '');
  $tel      = trim($_POST['telefono'] ?? '');
  $dni      = trim($_POST['dni'] ?? '');
  $fnac     = trim($_POST['fecha_nacimiento'] ?? '');
  $nac      = trim($_POST['nacionalidad'] ?? '');
  $disp     = trim($_POST['disponibilidad'] ?? '');
  $desc     = trim($_POST['descripcion_servicios'] ?? '');
  $exp      = trim($_POST['experiencia_años'] ?? '');
  $precio   = trim($_POST['precio_hora'] ?? '');

  // Validaciones mínimas
  $errores = [];
  if ($nombre === '') $errores[] = 'El nombre es obligatorio';
  if ($correo === '') $errores[] = 'El correo es obligatorio';
  if ($exp !== '' && !ctype_digit($exp)) $errores[] = 'Experiencia debe ser un número entero';
  if ($precio !== '' && !is_numeric($precio)) $errores[] = 'Precio hora debe ser numérico';
  if ($fnac !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fnac)) $errores[] = 'Fecha de nacimiento debe ser YYYY-MM-DD';

  if (!empty($errores)) {
    $query = http_build_query(['id' => $id, 'err' => implode(' | ', $errores)]);
    header("Location: /labora_db/funciones/admin-panel-editar-trabajador.php?$query");
    exit();
  }

  // Update
  $sql = "UPDATE empleado SET
            nombre = ?, correo = ?, profesion = ?, zona_trabajo = ?, telefono = ?,
            dni = ?, fecha_nacimiento = ?, nacionalidad = ?, disponibilidad = ?,
            descripcion_servicios = ?, experiencia_años = ?, precio_hora = ?
          WHERE id_empleado = ?";
  $stmt = $conn->prepare($sql);
  $exp_i = ($exp === '') ? null : (int)$exp;
  $precio_d = ($precio === '') ? null : (float)$precio;
  $stmt->bind_param(
    "ssssssssssidi",
    $nombre, $correo, $prof, $zona, $tel,
    $dni, $fnac, $nac, $disp,
    $desc, $exp_i, $precio_d, $id
  );
  if (!$stmt->execute()) {
    // por ejemplo, correo duplicado
    $query = http_build_query(['id' => $id, 'err' => 'No se pudo guardar: ' . $stmt->error]);
    header("Location: /labora_db/funciones/admin-panel-editar-trabajador.php?$query");
    exit();
  }

  header("Location: /labora_db/vistas/admin/admin-panel.php#trabajadores");
  exit();
}

// GET: cargar datos
if ($id <= 0) { header("Location: /labora_db/vistas/admin/admin-panel.php#trabajadores"); exit(); }

$stmt = $conn->prepare("SELECT * FROM empleado WHERE id_empleado = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) {
  header("Location: /labora_db/vistas/admin/admin-panel.php#trabajadores");
  exit();
}
$emp = $res->fetch_assoc();

$errMsg = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar trabajador #<?= (int)$emp['id_empleado'] ?> - LABORA</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root{--bg:#f4f6f8; --side:#1f2937; --acc:#2563eb;}
  *{box-sizing:border-box} body{margin:0; font-family:system-ui,Segoe UI,Roboto,Arial; background:var(--bg)}
  .wrap{max-width:900px; margin:30px auto; background:#fff; border-radius:14px; padding:20px; box-shadow:0 6px 18px rgba(0,0,0,.06)}
  h2{margin:0 0 14px}
  .grid{display:grid; grid-template-columns: 1fr 1fr; gap:12px}
  label{font-weight:600; font-size:14px; color:#334155}
  input, textarea{width:100%; padding:10px; border:1px solid #d1d5db; border-radius:10px}
  textarea{min-height:100px}
  .row{display:flex; gap:10px; flex-wrap:wrap; margin-top:14px}
  .btn{padding:10px 12px; border:0; border-radius:10px; cursor:pointer; font-weight:700}
  .btn.save{background:#16a34a; color:white}
  .btn.back{background:#e5e7eb}
  .error{background:#fee2e2; color:#7f1d1d; padding:10px 12px; border-radius:10px; margin-bottom:12px}
  @media (max-width: 860px){ .grid{grid-template-columns:1fr} }
</style>
</head>
<body>
<div class="wrap">
  <h2>Editar trabajador #<?= (int)$emp['id_empleado'] ?></h2>
  <div style="margin-bottom:8px;color:#6b7280">Estado actual: <b><?= htmlspecialchars($emp['estado_verificacion'] ?? 'pendiente') ?></b></div>
  <?php if ($errMsg): ?><div class="error"><?= htmlspecialchars($errMsg) ?></div><?php endif; ?>

  <form method="post" action="/labora_db/funciones/admin-panel-editar-trabajador.php">
    <input type="hidden" name="id_empleado" value="<?= (int)$emp['id_empleado'] ?>">

    <div class="grid">
      <div>
        <label>Nombre</label>
        <input name="nombre" value="<?= htmlspecialchars($emp['nombre'] ?? '') ?>" required>
      </div>
      <div>
        <label>Correo</label>
        <input name="correo" value="<?= htmlspecialchars($emp['correo'] ?? '') ?>" required>
      </div>

      <div>
        <label>Profesión</label>
        <input name="profesion" value="<?= htmlspecialchars($emp['profesion'] ?? '') ?>">
      </div>
      <div>
        <label>Zona de trabajo</label>
        <input name="zona_trabajo" value="<?= htmlspecialchars($emp['zona_trabajo'] ?? '') ?>">
      </div>

      <div>
        <label>Teléfono</label>
        <input name="telefono" value="<?= htmlspecialchars($emp['telefono'] ?? '') ?>">
      </div>
      <div>
        <label>DNI</label>
        <input name="dni" value="<?= htmlspecialchars($emp['dni'] ?? '') ?>">
      </div>

      <div>
        <label>Fecha de nacimiento (YYYY-MM-DD)</label>
        <input name="fecha_nacimiento" value="<?= htmlspecialchars($emp['fecha_nacimiento'] ?? '') ?>">
      </div>
      <div>
        <label>Nacionalidad</label>
        <input name="nacionalidad" value="<?= htmlspecialchars($emp['nacionalidad'] ?? '') ?>">
      </div>

      <div>
        <label>Disponibilidad</label>
        <input name="disponibilidad" value="<?= htmlspecialchars($emp['disponibilidad'] ?? '') ?>">
      </div>
      <div>
        <label>Experiencia (años)</label>
        <input name="experiencia_años" value="<?= htmlspecialchars((string)($emp['experiencia_años'] ?? '')) ?>">
      </div>

      <div>
        <label>Precio por hora</label>
        <input name="precio_hora" value="<?= htmlspecialchars((string)($emp['precio_hora'] ?? '')) ?>">
      </div>
      <div>
        <label>Profesionales/Habilidades (opcional)</label>
        <input name="habilidades" value="<?= htmlspecialchars($emp['habilidades'] ?? '') ?>" disabled>
      </div>
    </div>

    <div style="margin-top:12px">
      <label>Descripción de servicios</label>
      <textarea name="descripcion_servicios"><?= htmlspecialchars($emp['descripcion_servicios'] ?? '') ?></textarea>
    </div>

    <div class="row">
      <button class="btn save" type="submit">Guardar cambios</button>
      <a class="btn back" href="/labora_db/vistas/admin/admin-panel.php#trabajadores">Volver</a>
      <a class="btn back" href="/labora_db/funciones/admin-trabajador.php?id=<?= (int)$emp['id_empleado'] ?>">Ver ficha</a>
    </div>
  </form>
</div>
</body>
</html>
