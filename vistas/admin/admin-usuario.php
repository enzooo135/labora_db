<?php
// labora_db/vistas/admin/admin-usuario.php
session_start();
if (empty($_SESSION['admin'])) { header("Location: /labora_db/vistas/admin/admin-login.php"); exit(); }
require_once __DIR__ . '/../../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); exit('ID inválido'); }

$sql = "SELECT id_usuario, nombre, correo, dni, fecha_nacimiento, telefono, localidad,
               estado_verificacion, dni_frente_path, dni_dorso_path, matricula_path,
               verificado_por, fecha_verificacion, observaciones_verificacion, direccion
          FROM usuarios
         WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) { http_response_code(404); exit('Usuario no encontrado'); }
$u = $res->fetch_assoc();

function badge($estado) {
  if ($estado === 'aprobado')  return "<span class='badge b-apr'>Aprobado</span>";
  if ($estado === 'rechazado') return "<span class='badge b-rech'>Rechazado</span>";
  return "<span class='badge b-pend'>Pendiente</span>";
}
function renderDoc($label, $relPath) {
  if (empty($relPath)) {
    return "<div class='doc'><div class='label'>$label</div><em>Sin archivo</em></div>";
  }
  $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
  $url = "/labora_db/funciones/admin-file.php?f=" . urlencode($relPath);

  if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
    return "
      <div class='doc'>
        <div class='label'>$label</div>
        <a href='$url' target='_blank'><img src='$url' alt='$label' class='thumb'></a>
        <div><a href='$url' target='_blank'>Abrir en pestaña nueva</a></div>
      </div>
    ";
  }
  if ($ext === 'pdf') {
    return "
      <div class='doc'>
        <div class='label'>$label</div>
        <div class='pdf'>
          <a class='btn sec' href='$url' target='_blank'>Ver PDF</a>
        </div>
      </div>
    ";
  }
  return "
    <div class='doc'>
      <div class='label'>$label</div>
      <div><a href='$url' target='_blank'>Descargar / Ver</a></div>
    </div>
  ";
}

$errObs = isset($_GET['err']) && $_GET['err'] === 'obs_required';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Verificación de usuario #<?= (int)$u['id_usuario'] ?> - LABORA</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root{--bg:#f4f6f8; --side:#1f2937; --acc:#2563eb; --danger:#ef4444;}
  *{box-sizing:border-box} body{margin:0; font-family:system-ui,Segoe UI,Roboto,Arial; background:var(--bg)}
  .layout{display:grid; grid-template-columns: 260px 1fr; min-height:100vh}
  .sidebar{background:var(--side); color:#e5e7eb; padding:20px}
  .sidebar h2{margin:0 0 12px}
  .sidebar a{color:#cbd5e1; text-decoration:none; display:block; padding:10px 8px; border-radius:8px}
  .sidebar a:hover{background:#111827}
  .content{padding:20px}
  .top-bar{background:linear-gradient(90deg,#0f172a,#1f2937); color:white; padding:16px; border-radius:12px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:10px}
  .back{background:#e5e7eb; border:0; border-radius:10px; padding:8px 12px; cursor:pointer; font-weight:600}
  .grid{display:grid; gap:16px; grid-template-columns: 1fr 1fr}
  .card{background:white; border-radius:12px; padding:16px; box-shadow:0 6px 18px rgba(0,0,0,.06)}
  .row{display:grid; grid-template-columns: 180px 1fr; gap:8px; margin-bottom:8px}
  .label{color:#6b7280; font-size:14px}
  .value{font-weight:600}
  .docs{display:grid; gap:14px; grid-template-columns: repeat(3, 1fr)}
  .doc{background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:12px}
  .thumb{width:100%; height:220px; object-fit:contain; background:white; border-radius:8px; border:1px solid #e5e7eb}
  .btn{padding:10px 12px; border:0; border-radius:10px; cursor:pointer; font-weight:700}
  .btn.ok{background:#16a34a; color:white}
  .btn.warn{background:#f59e0b; color:white}
  .btn.sec{background:#e5e7eb}
  .badge{display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:700}
  .b-pend{background:#fde68a; color:#92400e}
  .b-apr{background:#bbf7d0; color:#065f46}
  .b-rech{background:#fecaca; color:#7f1d1d}
  textarea{width:100%; min-height:90px; padding:10px; border:1px solid #d1d5db; border-radius:10px}
  .error{background:linear-gradient(90deg,#ef4444,#f97316); color:white; padding:10px 12px; border-radius:10px; margin-bottom:14px; font-size:14px}
  @media (max-width: 980px){ .grid{grid-template-columns:1fr} .docs{grid-template-columns:1fr} .row{grid-template-columns:1fr} .layout{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="layout">
  <div class="sidebar">
    <h2>LABORA Admin</h2>
    <a href="/labora_db/vistas/admin/admin-panel.php#pendientes">Trabajadores pendientes</a>
    <a href="/labora_db/vistas/admin/admin-panel.php#trabajadores">Trabajadores</a>
    <a href="/labora_db/vistas/admin/admin-panel.php#usuarios-pendientes">Usuarios pendientes</a>
    <a href="/labora_db/vistas/admin/admin-panel.php#usuarios">Usuarios</a>
    <a href="/labora_db/funciones/logout.php">Cerrar sesión</a>
  </div>

  <div class="content">
    <div class="top-bar">
      <div>
        <h2 style="margin:0;">Verificación: <?= htmlspecialchars($u['nombre']) ?> <?= badge($u['estado_verificacion']) ?></h2>
        <div style="font-size:13px;opacity:.8">ID #<?= (int)$u['id_usuario'] ?> · Correo: <?= htmlspecialchars($u['correo'] ?? '—') ?></div>
      </div>
      <div>
        <button class="back" onclick="history.back()">← Volver</button>
      </div>
    </div>

    <?php if ($errObs): ?>
      <div class="error">Para <strong>rechazar</strong> es obligatorio ingresar un motivo en “Observaciones”.</div>
    <?php endif; ?>

    <div class="grid">
      <div class="card">
        <h3 style="margin-top:0">Datos personales</h3>
        <div class="row"><div class="label">Nombre</div><div class="value"><?= htmlspecialchars($u['nombre']) ?></div></div>
        <div class="row"><div class="label">Correo</div><div class="value"><?= htmlspecialchars($u['correo']) ?></div></div>
        <div class="row"><div class="label">Teléfono</div><div class="value"><?= htmlspecialchars($u['telefono'] ?? '—') ?></div></div>
        <div class="row"><div class="label">DNI</div><div class="value"><?= htmlspecialchars($u['dni'] ?? '—') ?></div></div>
        <div class="row"><div class="label">Fecha de nacimiento</div><div class="value"><?= htmlspecialchars($u['fecha_nacimiento'] ?? '—') ?></div></div>
        <div class="row"><div class="label">Localidad</div><div class="value"><?= htmlspecialchars($u['localidad'] ?? '—') ?></div></div>
        <div class="row"><div class="label">Dirección</div><div class="value"><?= htmlspecialchars($u['direccion'] ?? '—') ?></div></div>
      </div>

      <div class="card">
        <h3 style="margin-top:0">Acciones de verificación</h3>
        <form method="post" action="/labora_db/funciones/admin-verificar-usuario.php" id="verifForm" onsubmit="return checkForm(event)">
          <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
          <div class="row" style="grid-template-columns:1fr">
            <label class="label" for="obs">Observaciones (obligatorio para rechazar)</label>
            <textarea id="obs" name="observaciones" placeholder="Motivo del rechazo, inconsistencias, etc."><?= htmlspecialchars($u['observaciones_verificacion'] ?? '') ?></textarea>
          </div>
          <div style="display:flex; gap:10px; margin-top:10px; flex-wrap:wrap">
            <button class="btn ok"   type="submit" name="accion" value="aprobar">Aprobar</button>
            <button class="btn warn" type="submit" name="accion" value="rechazar">Rechazar</button>
            <a class="btn sec" href="/labora_db/vistas/admin/admin-panel.php#usuarios-pendientes">Cancelar</a>
          </div>
        </form>
        <?php if (!empty($u['verificado_por']) && !empty($u['fecha_verificacion'])): ?>
          <div style="margin-top:14px; font-size:13px; color:#6b7280">
            Última acción: <?= htmlspecialchars($u['estado_verificacion']) ?> el <?= htmlspecialchars($u['fecha_verificacion']) ?> (admin #<?= (int)$u['verificado_por'] ?>)
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card" style="margin-top:16px">
      <h3 style="margin-top:0">Documentación subida</h3>
      <div class="docs">
        <?= renderDoc('DNI Frente',   $u['dni_frente_path']) ?>
        <?= renderDoc('DNI Dorso',    $u['dni_dorso_path']) ?>
        <?= renderDoc('Adjunto / Certificado', $u['matricula_path']) ?>
      </div>
    </div>
  </div>
</div>

<script>
function checkForm(e){
  const obs = document.getElementById('obs').value.trim();
  const submitter = e.submitter && e.submitter.value ? e.submitter.value : null;
  if (submitter === 'rechazar' && !obs) {
    alert('Para RECHAZAR es obligatorio ingresar un motivo en Observaciones.');
    return false;
  }
  return true;
}
</script>
</body>
</html>
