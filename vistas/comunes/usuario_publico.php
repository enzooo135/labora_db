<?php
// vistas/public/usuario_publico.php?uid=123
require_once __DIR__ . '/../../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
if ($uid <= 0) { http_response_code(400); echo "Usuario inválido."; exit; }

$st = $conn->prepare("
  SELECT id_usuario, nombre, localidad, zona_busqueda, rubros_interes, descripcion_usuario,
         estado_verificacion, foto_perfil_usuario, visibilidad, fecha_registro
  FROM usuarios
  WHERE id_usuario = ?
  LIMIT 1
");
$st->bind_param("i", $uid);
$st->execute();
$u = $st->get_result()->fetch_assoc();
if (!$u) { echo "Perfil no disponible."; exit; }

// si el usuario eligió ocultar su perfil, no lo mostramos
if (($u['visibilidad'] ?? 'publico') === 'oculto') { echo "Perfil no disponible."; exit; }

function h($s){ return htmlspecialchars($s ?? ''); }
$foto = !empty($u['foto_perfil_usuario'])
          ? "/labora_db/uploads/".h($u['foto_perfil_usuario'])
          : "/labora_db/imagenes/default_user.jpg";
$anio = !empty($u['fecha_registro']) ? date('Y', strtotime($u['fecha_registro'])) : '';
$zona = $u['zona_busqueda'] ?: $u['localidad'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Perfil del solicitante</title>
<style>
  :root{--azul:#0077B6; --cel:#00B4D8; --b:#e5e7eb; --txt:#222}
  body{font-family:system-ui,Arial; margin:0; background:#f6fbff; color:var(--txt)}
  .card{max-width:720px; margin:24px auto; background:#fff; border:1px solid var(--b); border-radius:12px; padding:18px}
  .head{display:flex; gap:16px; align-items:center}
  .ava{width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #90E0EF}
  h1{font-size:20px; margin:0}
  .badges span{display:inline-block; padding:4px 8px; border-radius:999px; border:1px solid var(--b); margin-right:6px; font-size:12px}
  .ok{border-color:#10b981; color:#065f46}
  .grid{display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px}
  .section{border-top:1px solid var(--b); padding-top:12px; margin-top:12px}
  .muted{color:#666}
</style>
</head>
<body>
  <div class="card">
    <div class="head">
      <img class="ava" src="<?= h($foto) ?>" alt="Foto de perfil">
      <div>
        <h1><?= h($u['nombre']) ?></h1>
        <div class="badges">
          <span class="<?= ($u['estado_verificacion']==='aprobado'?'ok':'') ?>">
            Identidad: <?= h($u['estado_verificacion'] ?? 'pendiente') ?>
          </span>
          <?php if($anio): ?><span>Miembro desde <?= h($anio) ?></span><?php endif; ?>
        </div>
      </div>
    </div>

    <div class="grid">
      <div><strong>Zona:</strong> <?= h($zona ?: '—') ?></div>
      <div><strong>Rubros que busca:</strong> <?= h($u['rubros_interes'] ?: '—') ?></div>
    </div>

    <?php if(!empty($u['descripcion_usuario'])): ?>
      <div class="section">
        <strong>Descripción:</strong>
        <p><?= nl2br(h($u['descripcion_usuario'])) ?></p>
      </div>
    <?php endif; ?>

    <p class="muted">Esta ficha sirve para que el profesional verifique quién lo contacta desde LABORA.</p>
  </div>
</body>
</html>
