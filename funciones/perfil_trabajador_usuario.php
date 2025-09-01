<?php
// /labora_db/perfil_trabajador_usuario.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli('localhost', 'root', '', 'labora_db');
$mysqli->set_charset('utf8mb4');


/* -------- Helpers para foto (inspirado en tu referencia) -------- */
function base_url_root_view(): string {
  $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/')); // ej: labora_db/perfil_trabajador_usuario.php
  return '/' . ($parts[0] ?? ''); // => /labora_db
}
function foto_bd_a_url_view(?string $v, string $BASE_URL, string $default): string {
  $v = trim((string)$v);
  if ($v === '') return $default;                         // sin valor: placeholder
  if (preg_match('#^https?://#i', $v)) return $v;         // ya es URL completa
  if (strpos($v, '/') === 0) return $v;                   // ruta absoluta
  if (strpos($v, '/') !== false)                          // carpeta/archivo
    return rtrim($BASE_URL, '/') . '/' . ltrim($v, '/');  // /labora_db/uploads/xxx.jpg
  return rtrim($BASE_URL, '/') . '/uploads/' . $v;        // sólo nombre -> /uploads/nombre
}

/* ------------------ Validar id (?id=) ------------------ */
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) { http_response_code(400); exit('Falta o es inválido el parámetro id.'); }

/* ------------------ Cargar empleado -------------------- */
$stmt = $mysqli->prepare("SELECT id_empleado, nombre, apellido, profesion, titulo_profesional, zona_trabajo, telefono, correo, descripcion_servicios, foto_perfil
                          FROM empleado WHERE id_empleado = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$empleado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$empleado) { http_response_code(404); exit('Empleado no encontrado.'); }

/* ------------------ Foto de perfil --------------------- */
$BASE_URL        = base_url_root_view();                             // ej: /labora_db
$DEFAULT_IMG_URL = $BASE_URL . '/imagenes/default_user.jpg';            // ajustá si usás otro
$fotoPerfil      = foto_bd_a_url_view($empleado['foto_perfil'] ?? '', $BASE_URL, $DEFAULT_IMG_URL);

/* ------------------ Helper de escape ------------------- */
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Perfil de <?= e(trim(($empleado['nombre']??'').' '.($empleado['apellido']??''))) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --azul:#0077B6; --sec:#00B4D8; --borde:#e5eef5; --txt:#123; --bg:#d6f3ff; --white:#fff; }
    *{box-sizing:border-box}
    body{background:var(--bg);font-family:Arial,Roboto,sans-serif;color:var(--txt);margin:0}
    .wrap{max-width:720px;margin:24px auto;padding:0 16px}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
    .btn{display:inline-block;background:var(--azul);color:#fff;text-decoration:none;padding:8px 12px;border-radius:10px}
    .card{background:var(--white);border:1px solid var(--borde);border-radius:16px;box-shadow:0 6px 18px rgba(0,0,0,.06);padding:20px}
    .header{display:flex;gap:18px;align-items:center}
    .avatar{width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--sec)}
    h1{margin:0 0 6px 0;color:var(--azul)}
    .muted{color:#456}
    .chips{margin-top:8px;display:flex;flex-wrap:wrap;gap:8px}
    .chip{display:inline-block;background:#E6F4FF;color:var(--azul);padding:4px 10px;border-radius:999px;font-size:12px}
    .block{margin-top:16px}
    .label{font-size:12px;color:#567;margin-bottom:4px}
    .value{font-size:15px}
  </style>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <a class="btn" href="<?= e($BASE_URL) ?>/vistas/comunes/filtros.php">← Volver</a>
  </div>

  <div class="card">
    <div class="header">
      <img class="avatar" src="<?= e($fotoPerfil) ?>" alt="Foto de perfil">
      <div>
        <h1><?= e(trim(($empleado['nombre']??'').' '.($empleado['apellido']??''))) ?></h1>
        <div class="muted"><?= e($empleado['titulo_profesional'] ?: ($empleado['profesion'] ?? '')) ?></div>
        <div class="chips">
          <?php if (!empty($empleado['zona_trabajo'])): ?><span class="chip">Zona: <?= e($empleado['zona_trabajo']) ?></span><?php endif; ?>
          <?php if (!empty($empleado['telefono'])): ?><span class="chip">Tel: <?= e($empleado['telefono']) ?></span><?php endif; ?>
          <?php if (!empty($empleado['correo'])): ?><span class="chip">Email: <?= e($empleado['correo']) ?></span><?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!empty($empleado['descripcion_servicios'])): ?>
      <div class="block">
        <div class="label">Acerca de mí</div>
        <div class="value"><?= nl2br(e($empleado['descripcion_servicios'])) ?></div>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
