<?php
// vistas/usuarios/perfil_usuario.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../formulario/login.html");
  exit();
}

require_once __DIR__ . '/../../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$uid = (int)$_SESSION['usuario_id'];

/* ===== Helper para BASE_URL absoluta (p/ imágenes) ===== */
function base_url_root(): string {
  $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/')); // ej: labora_db/vistas/usuarios/perfil_usuario.php
  return '/' . ($parts[0] ?? '');
}
$BASE_URL = base_url_root(); // -> /labora_db

/* ===== Catálogos (selects) ===== */
$ZONAS_PERMITIDAS = ['Moreno', 'Paso del rey', 'Merlo', 'Padua', 'Ituzaingo'];
$RUBROS = ['Carpinteria','Plomeria','Educacion','Electricidad','Jardineria','Pintura','Gasista','Acompañante terapeutico','Profesor de educacion fisica','Fletero'];
$MEDIOS_CONTACTO = ['whatsapp','email'];

/* ===== GET usuario ===== */
$st = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario=?");
$st->bind_param("i", $uid);
$st->execute();
$user = $st->get_result()->fetch_assoc();
if (!$user) { echo "Usuario no encontrado."; exit(); }

/* ===== Alerta verificación ===== */
$estado = $user['estado_verificacion'] ?? 'pendiente';
$alerta = '';
if ($estado === 'pendiente') {
  $alerta = "Tu verificación está pendiente. Cuando la aprobemos, vas a tener más confianza frente a los trabajadores.";
} elseif ($estado === 'rechazado') {
  $motivo = htmlspecialchars($user['observaciones_verificacion'] ?? 'Motivo no especificado');
  $alerta = "Tu verificación fue rechazada. Motivo: $motivo";
} elseif ($estado === 'aprobado') {
  $alerta = "¡Cuenta verificada!";
}

/* ===== POST (guardar) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Campos básicos existentes
  $nombre    = trim($_POST['nombre'] ?? '');
  $telefono  = trim($_POST['telefono'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $localidad = trim($_POST['localidad'] ?? '');
  $fnac      = trim($_POST['fecha_nacimiento'] ?? '');

  // Campos nuevos en la MISMA tabla usuarios
  $zona_busqueda   = $_POST['zona_busqueda'] ?? null;
  if (!in_array($zona_busqueda, $ZONAS_PERMITIDAS, true)) { $zona_busqueda = null; }

  $rubros_sel = $_POST['rubros_interes'] ?? [];
  if (!is_array($rubros_sel)) $rubros_sel = [];
  $rubros_validos = implode(', ', array_values(array_intersect($rubros_sel, $RUBROS)));

  $presupuesto    = ($_POST['presupuesto_max'] !== '' && is_numeric($_POST['presupuesto_max'])) ? (float)$_POST['presupuesto_max'] : null;

  $medio_contacto = $_POST['medio_contacto'] ?? 'whatsapp';
  if (!in_array($medio_contacto, $MEDIOS_CONTACTO, true)) { $medio_contacto = 'whatsapp'; }

  $horario        = trim($_POST['horario_contacto'] ?? '');
  $descripcion    = trim($_POST['descripcion_usuario'] ?? '');
  $visibilidad    = ($_POST['visibilidad'] ?? 'publico') === 'oculto' ? 'oculto' : 'publico';

  // Update principal
  $sql = "UPDATE usuarios SET 
            nombre=?,
            telefono=?,
            direccion=?,
            localidad=?,
            fecha_nacimiento=?,
            zona_busqueda=?,
            rubros_interes=?,
            presupuesto_max=?,
            medio_contacto=?,
            horario_contacto=?,
            descripcion_usuario=?,
            visibilidad=?
          WHERE id_usuario=?";
  $up = $conn->prepare($sql);
  $up->bind_param(
    "ssssssssssssi",
    $nombre,
    $telefono,
    $direccion,
    $localidad,
    $fnac,
    $zona_busqueda,
    $rubros_validos,
    $presupuesto,
    $medio_contacto,
    $horario,
    $descripcion,
    $visibilidad,
    $uid
  );
  $up->execute();

  // Foto de perfil del USUARIO
  if (isset($_FILES['foto_usuario']) && $_FILES['foto_usuario']['error'] === UPLOAD_ERR_OK) {
      $ext = strtolower(pathinfo($_FILES['foto_usuario']['name'], PATHINFO_EXTENSION));
      $permitidas = ['jpg','jpeg','png','gif','webp'];
      if (in_array($ext, $permitidas, true)) {
          $fname = uniqid('u_', true) . "." . $ext;
          $destino = __DIR__ . '/../../uploads/' . $fname;
          if (move_uploaded_file($_FILES['foto_usuario']['tmp_name'], $destino)) {
              $pf = $conn->prepare("UPDATE usuarios SET foto_perfil_usuario=? WHERE id_usuario=?");
              $pf->bind_param("si", $fname, $uid);
              $pf->execute();
          }
      }
  }

  header("Location: perfil_usuario.php");
  exit();
}

/* ===== Helpers de vista ===== */
function h($s){ return htmlspecialchars($s ?? ''); }
$foto_usuario = !empty($user['foto_perfil_usuario'])
                  ? $BASE_URL . '/uploads/' . h($user['foto_perfil_usuario'])
                  : $BASE_URL . '/imagenes/default_user.jpg';

$rubros_actuales = array_map('trim', array_filter(explode(',', (string)($user['rubros_interes'] ?? ''))));

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil del Usuario</title>
    <!-- CSS -->
    <link rel="stylesheet" href="/labora_db/recursos/css/nav.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style>
  :root{--azul:#0077B6; --cel:#00B4D8; --bg:#d6f3ff; --txt:#333; --b:#ddd; --card:#fff}
  *{box-sizing:border-box}
  body{margin:0; font-family:Arial,system-ui; background:var(--bg); color:var(--txt)}
  .container{max-width:960px; margin:24px auto; background:var(--card); padding:20px; border-radius:10px; box-shadow:0 2px 8px rgb(0 0 0 / .08)}
  h1{margin:0 0 10px; color:var(--azul)}
  h2{color:var(--azul); margin:18px 0 10px}
  label{font-weight:600; color:var(--cel); display:block; margin-bottom:4px}
  p, input, textarea, select{font-size:16px}
  .section{border:1px solid var(--b); border-radius:10px; padding:14px; margin-bottom:14px; background:#fff}
  .grid{display:grid; gap:12px; grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
  input[type=text], input[type=date], input[type=number], textarea, select{width:100%; padding:8px; border:1px solid #ccc; border-radius:6px}
  textarea{min-height:80px; resize:vertical}
  .static{}
  .editable{display:none}
  .btn{border:0; cursor:pointer; border-radius:8px; padding:10px 16px; color:#fff; margin:10px 6px 0 0; background:var(--azul)}
  .btn.save{background:green; display:none}
  .alerta{background:#fff3cd; border:1px solid #ffe8a1; color:#8a6d3b; border-radius:8px; padding:12px; margin-bottom:12px}
  .profile-picture{border-radius:50%; overflow:hidden; width:110px; height:110px; margin:12px auto; border:3px solid #90E0EF}
  .profile-picture img{width:100%; height:100%; object-fit:cover}
  .helper{font-size:.9rem; color:#555; margin-top:4px}
</style>
<script>
function toggleEdit(){
  document.querySelectorAll('.static').forEach(e=>e.style.display='none');
  document.querySelectorAll('.editable').forEach(e=>e.style.display='block');
  document.getElementById('btnEdit').style.display='none';
  document.getElementById('btnSave').style.display='inline-block';
}
</script>
</head>
<body>

<!-- NAV hamburguesa -->
    <nav class="navbar">
        <div class="logo">
            <a href="/labora_db/vistas/comunes/filtros.php">Labora</a>
        </div>
        <button class="hamburger" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <ul id="menu" class="nav-links">
            <li class="menu-header">
                <img src="/labora_db/imagenes/logo-labora.png" alt="Labora" class="menu-logo">
                <button class="menu-close" aria-label="Cerrar menú">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </li>
            <li class="menu-cta">
                <a href="#" class="btn-primario"><i class="fa-solid fa-rocket"></i> Mis Favoritos</a>
            </li>
            <li class="menu-divider"><span>Cuenta</span></li>
            <li><a href="/labora_db/vistas/usuarios/perfil_usuario.php">Perfil</a></li>
            <li><a href="/labora_db/vistas/usuarios/configuracion.php">Configuración</a></li>
            <li><a href="/labora_db/funciones/logout.php">Cerrar sesión</a></li>
        </ul>
        <div class="menu-backdrop"></div>
    </nav>
    
<div class="container">
  <h1>Tu perfil (Usuario)</h1>

  <?php if ($alerta): ?>
    <div class="alerta"><?= $alerta ?></div>
  <?php endif; ?>

  <div class="profile-picture">
    <img src="<?= h($foto_usuario) ?>" alt="Foto de perfil">
  </div>

  <form method="POST" enctype="multipart/form-data">
    <section class="section editable">
      <h2>Foto de Perfil</h2>
      <input type="file" name="foto_usuario" accept="image/*">
      <p class="helper">Formatos permitidos: jpg, jpeg, png, gif, webp.</p>
    </section>

    <section class="section">
      <h2>Información personal</h2>
      <div class="grid">
        <div>
          <label>Nombre completo</label>
          <p class="static"><?= h($user['nombre']) ?></p>
          <input class="editable" type="text" name="nombre" value="<?= h($user['nombre']) ?>">
        </div>
        <div>
          <label>Correo</label>
          <p class="static"><?= h($user['correo']) ?></p>
          <p class="editable helper">El correo no se edita desde aquí.</p>
        </div>
        <div>
          <label>Teléfono</label>
          <p class="static"><?= h($user['telefono']) ?></p>
          <input class="editable" type="text" name="telefono" value="<?= h($user['telefono']) ?>">
        </div>
        <div>
          <label>Dirección</label>
          <p class="static"><?= h($user['direccion']) ?></p>
          <input class="editable" type="text" name="direccion" value="<?= h($user['direccion']) ?>">
        </div>
        <div>
          <label>Localidad</label>
          <p class="static"><?= h($user['localidad']) ?></p>
          <input class="editable" type="text" name="localidad" value="<?= h($user['localidad']) ?>">
        </div>
        <div>
          <label>Fecha de nacimiento</label>
          <p class="static"><?= h($user['fecha_nacimiento']) ?></p>
          <input class="editable" type="date" name="fecha_nacimiento" 
                 value="<?= preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$user['fecha_nacimiento']) ? h($user['fecha_nacimiento']) : '' ?>">
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Preferencias de búsqueda</h2>
      <div class="grid">
        <div>
          <label>Zona de búsqueda</label>
          <p class="static"><?= h($user['zona_busqueda']) ?></p>
          <select class="editable" name="zona_busqueda">
            <option value="" disabled <?= empty($user['zona_busqueda']) ? 'selected' : '' ?>>Elegí tu zona…</option>
            <?php foreach ($ZONAS_PERMITIDAS as $z): ?>
              <option value="<?= h($z) ?>" <?= ($user['zona_busqueda'] === $z) ? 'selected' : '' ?>><?= h($z) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Rubros de interés</label>
          <p class="static"><?= h($user['rubros_interes']) ?></p>
          <select class="editable" name="rubros_interes[]" multiple size="6">
            <?php foreach ($RUBROS as $r): ?>
              <option value="<?= h($r) ?>" <?= in_array($r, $rubros_actuales, true) ? 'selected' : '' ?>><?= h($r) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="editable helper">Usá CTRL/CMD para elegir múltiples rubros.</p>
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Presupuesto y contacto</h2>
      <div class="grid">
        <div>
          <label>Presupuesto máximo (ARS)</label>
          <p class="static"><?= $user['presupuesto_max'] !== null && $user['presupuesto_max'] !== '' ? '$ '.number_format((float)$user['presupuesto_max'],2,',','.') : '—' ?></p>
          <input class="editable" type="number" step="0.01" min="0" name="presupuesto_max" value="<?= h($user['presupuesto_max']) ?>" placeholder="Ej: 25000.00">
        </div>
        <div>
          <label>Medio de contacto preferido</label>
          <p class="static"><?= h($user['medio_contacto']) ?></p>
          <select class="editable" name="medio_contacto">
            <?php foreach ($MEDIOS_CONTACTO as $m): ?>
              <option value="<?= h($m) ?>" <?= ($user['medio_contacto'] ?? 'whatsapp') === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Horario para ser contactado</label>
          <p class="static"><?= h($user['horario_contacto']) ?></p>
          <input class="editable" type="text" name="horario_contacto" value="<?= h($user['horario_contacto']) ?>" placeholder="Ej: Lun-Vie 9 a 18">
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Descripción</h2>
      <p class="static"><?= nl2br(h($user['descripcion_usuario'])) ?></p>
      <textarea class="editable" name="descripcion_usuario" placeholder="Contá brevemente qué buscás, cada cuánto contratás, etc."><?= h($user['descripcion_usuario']) ?></textarea>
    </section>

    <section class="section">
      <h2>Privacidad</h2>
      <div class="grid">
        <div>
          <label>Visibilidad del perfil</label>
          <p class="static"><?= h($user['visibilidad'] ?? 'publico') ?></p>
          <select class="editable" name="visibilidad">
            <option value="publico" <?= ($user['visibilidad'] ?? 'publico') === 'publico' ? 'selected' : '' ?>>Público</option>
            <option value="oculto"  <?= ($user['visibilidad'] ?? 'publico') === 'oculto'  ? 'selected' : '' ?>>Oculto</option>
          </select>
          <p class="editable helper">Si está en "Oculto", no aparece en listados (solo podrás contactar vos).</p>
        </div>
      </div>
    </section>

    <button type="button" id="btnEdit" class="btn" onclick="toggleEdit()">Editar información</button>
    <button type="submit" id="btnSave" class="btn save">Aplicar cambios</button>
  </form>
</div>
</body>

<script src="/labora_db/recursos/js/menu-hamburguesa.js"></script>

</html>
