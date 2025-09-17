<?php
session_start();
include '../../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

// 1) Validar ID del trabajador
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Perfil no encontrado.";
  exit();
}
$id = (int) $_GET['id'];

// 2) Traer datos del empleado
$sql = "SELECT * FROM empleado WHERE id_empleado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
  echo "Trabajador no encontrado.";
  exit();
}
$empleado = $res->fetch_assoc();

// 3) Experiencia laboral
$sql_exp = "SELECT * FROM experiencia_laboral WHERE id_empleado = ? LIMIT 1";
$stmt_exp = $conn->prepare($sql_exp);
$stmt_exp->bind_param("i", $id);
$stmt_exp->execute();
$res_exp = $stmt_exp->get_result();
$experiencia = $res_exp->fetch_assoc() ?? null;

// 4) Educación
$sql_edu = "SELECT * FROM educacion WHERE id_empleado = ? LIMIT 1";
$stmt_edu = $conn->prepare($sql_edu);
$stmt_edu->bind_param("i", $id);
$stmt_edu->execute();
$res_edu = $stmt_edu->get_result();
$educacion = $res_edu->fetch_assoc() ?? null;

// Helper: normaliza teléfonos de Argentina para WhatsApp (wa.me)
function ar_normalize_phone_for_wa(string $raw): string {
  // solo dígitos
  $digits = preg_replace('/\D+/', '', $raw ?? '');
  // quitar 00 internacional si viene así
  $digits = preg_replace('/^00/', '', $digits);
  // asegurar código país 54
  if (strpos($digits, '54') !== 0) {
    $digits = '54' . $digits;
  }
  // trabajar sobre el resto (sin 54)
  $rest = substr($digits, 2);
  // quitar 0 inicial de área
  $rest = preg_replace('/^0+/', '', $rest);
  // quitar prefijo local 15
  $rest = preg_replace('/^15/', '', $rest);
  // si parece celular y no tiene 9 (móvil internacional), agregárselo
  // (en AR móviles: 54 + 9 + área + línea)
  if (!preg_match('/^9/', $rest) && strlen($rest) >= 9) {
    $rest = '9' . $rest;
  }
  // limpiar duplicados de 9 si los hubiera (casos raros)
  $rest = preg_replace('/^99+/', '9', $rest);
  return '54' . $rest;
}

/* =========================
   5) Usuario logueado + mensaje con link a ficha pública
   ========================= */
$usuario_id = $_SESSION['usuario_id'] ?? null;  // <-- asumo que tu login setea esto
$nombre_usuario = 'Usuario LABORA';
$zona_usuario = '';
$rubros_usuario = '';
$perfil_publico_link = '';

if ($usuario_id) {
  // Traer datos mínimos del usuario para el mensaje
  $stU = $conn->prepare("SELECT nombre, zona_busqueda, rubros_interes, visibilidad FROM usuarios WHERE id_usuario = ? LIMIT 1");
  $stU->bind_param("i", $usuario_id);
  $stU->execute();
  $urow = $stU->get_result()->fetch_assoc();

  if ($urow) {
    $nombre_usuario = trim($urow['nombre'] ?? $nombre_usuario);
    $zona_usuario   = trim($urow['zona_busqueda'] ?? '');
    $rubros_usuario = trim($urow['rubros_interes'] ?? '');

    // Link absoluto a /labora_db/vistas/comunes/usuario_publico.php
    $scheme = (!empty($_SERVER['REQUEST_SCHEME'])) ? $_SERVER['REQUEST_SCHEME']
              : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $perfil_publico_link = $scheme.'://'.$host.'/labora_db/vistas/comunes/usuario_publico.php?uid='.$usuario_id;
  }
}

// Construir link de WhatsApp del trabajador con mensaje enriquecido
$telefonoRaw = $empleado['telefono'] ?? '';
$waNumber   = ar_normalize_phone_for_wa($telefonoRaw);
$oficio     = trim($empleado['profesion'] ?? '');
$zona_tra   = trim($empleado['zona_trabajo'] ?? '');

$partes = [];
$partes[] = "Hola {$empleado['nombre']}, te contacto desde Labora.";
if ($oficio)   $partes[] = "Me interesa un trabajo de {$oficio}.";
if ($zona_tra) $partes[] = "Zona: {$zona_tra}.";
$partes[] = "Soy {$nombre_usuario}.";
if ($zona_usuario)   $partes[] = "Mi zona: {$zona_usuario}.";
if ($rubros_usuario) $partes[] = "Busco: {$rubros_usuario}.";
if ($perfil_publico_link) $partes[] = "Mi ficha: {$perfil_publico_link}";
$mensaje = implode(' ', $partes);
$waText  = rawurlencode($mensaje);
$waLink  = "https://wa.me/{$waNumber}?text={$waText}";

// 6) Foto
$ruta_foto = !empty($empleado['foto_perfil'])
  ? '../../uploads/' . $empleado['foto_perfil']
  : '../../imagenes/default_user.jpg';

// 7) ----- Valoraciones: promedio + total -----
$sqlVal = "SELECT AVG(puntuacion) AS promedio, COUNT(*) AS total FROM valoraciones WHERE id_empleado = ?";
$stmtVal = $conn->prepare($sqlVal);
$stmtVal->bind_param("i", $id);
$stmtVal->execute();
$sumVal = $stmtVal->get_result()->fetch_assoc();
$promedio = isset($sumVal['promedio']) ? (float)$sumVal['promedio'] : 0.0;
$promedio1 = $promedio ? round($promedio, 1) : 0.0;
$totalVotos = (int)($sumVal['total'] ?? 0);

// 8) Últimos comentarios
$sqlCom = "SELECT v.puntuacion, v.comentario, v.fecha, COALESCE(u.nombre,'Usuario') AS usuario
           FROM valoraciones v
           LEFT JOIN usuarios u ON u.id_usuario = v.id_usuario
           WHERE v.id_empleado = ?
           ORDER BY v.fecha DESC
           LIMIT 10";
$stmtCom = $conn->prepare($sqlCom);
$stmtCom->bind_param("i", $id);
$stmtCom->execute();
$comentarios = $stmtCom->get_result();

// 9) ¿Usuario ya valoró?
$ya_valoro = false;
if ($usuario_id) {
  $sqlYa = "SELECT id_valoracion FROM valoraciones WHERE id_empleado=? AND id_usuario=? LIMIT 1";
  $stmtYa = $conn->prepare($sqlYa);
  $stmtYa->bind_param("ii", $id, $usuario_id);
  $stmtYa->execute();
  $resYa = $stmtYa->get_result();
  $ya_valoro = $resYa->num_rows > 0;
}

// Helper para dibujar estrellas (enteras)
function estrellas($valorFloat) {
  $llenas = (int) floor($valorFloat + 0.00001);
  $llenas = max(0, min(5, $llenas));
  return str_repeat("⭐", $llenas) . str_repeat("☆", 5 - $llenas);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- CSS-->
  <link rel="stylesheet" href="/labora_db/recursos/css/nav.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($empleado['nombre']); ?> - Perfil</title>
  <style>
    :root {
      --azul-principal: #0077B6;
      --azul-secundario: #00B4D8;
      --azul-claro: #90E0EF;
      --fondo-principal: #d6f3ff;
      --blanco: #fff;
      --texto: #333;
      --borde: #ccc;
    }
    body {
      background-color: var(--fondo-principal);
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      color: var(--texto);
    }
    .container {
      max-width: 960px;
      margin: 2rem auto;
      background: var(--blanco);
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
    }
    .profile-picture {
      border-radius: 50%;
      width: 120px;
      height: 120px;
      overflow: hidden;
      margin: 1rem auto;
      border: 3px solid var(--azul-claro);
    }
    .profile-picture img { width: 100%; height: 100%; object-fit: cover; }
    h1 { text-align: center; color: var(--azul-principal); margin-top: 0.5rem; }
    h2 { color: var(--azul-principal); margin-top: 1.5rem; }
    .section {
      background: var(--blanco);
      border: 1px solid var(--borde);
      border-radius: 8px;
      padding: 1rem;
      margin-top: 1rem;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
      gap: 1rem;
    }
    label { font-weight: bold; color: var(--azul-secundario); }
    p { margin: 0.2rem 0 1rem; }

    /* Valoraciones */
    .rating-header {
      display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
    }
    .rating-badge {
      background: #eef8ff; border: 1px solid var(--azul-claro);
      padding: .4rem .7rem; border-radius: 999px; font-weight: bold;
    }
    .comment {
      border-top: 1px dashed #e5e5e5; padding-top: .7rem; margin-top: .7rem;
    }
    .muted { color: #666; font-size: .95rem; }
    .btn {
      background: var(--azul-principal);
      color: #fff; border: none; border-radius: 8px;
      padding: .6rem 1rem; cursor: pointer;
    }
    .btn:disabled { opacity: .6; cursor: not-allowed; }
    textarea, select {
      width: 100%; padding: .6rem; border: 1px solid var(--borde); border-radius: 6px;
      font-family: inherit; box-sizing: border-box;
    }

    /* FAB WhatsApp */
    .chat-float{
      position: fixed;
      right: 24px;
      bottom: 24px;
      width: 100px; height: 100px;
      display: inline-grid; place-items: center;
      text-decoration: none;
      border-radius: 50%;
      box-shadow: 0 12px 28px rgba(0,0,0,.3), 0 4px 8px rgba(0,0,0,.25);
      transition: transform .15s ease, filter .15s ease;
      background: transparent;
    }
    .chat-float:hover{ transform: translateY(-3px); filter: brightness(1.05); }
    .chat-float:active{ transform: translateY(0); filter: brightness(.95); }
    .chat-float svg{ width: 100%; height: 100%; }
    .chat-float .bg{ fill: #005F8C; }        /* fondo principal */
    .chat-float .bubble{ fill: white; }      /* burbuja */
    .chat-float .dot{ fill: #0077B6; }       /* puntos acento */

    /* Modal */
    .modal-bg{display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:1000; align-items:center; justify-content:center;}
    .modal-card{background:#fff; padding:20px; border-radius:12px; max-width:420px; width:90%; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,.25);}
    .modal-card .worker{width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #90E0EF; margin:-56px auto 8px; background:#fff;}
    .modal-title{margin:0 0 4px; color:#0077B6;}
    .modal-desc{margin:0 0 8px; color:#333;}
    .modal-link{font-size:.9rem; word-break:break-all; color:#0077B6;}
    .modal-actions{margin-top:12px; display:flex; gap:8px; justify-content:center; flex-wrap:wrap;}
    .modal-btn{background:#0077B6; color:#fff; border:0; padding:10px 16px; border-radius:8px; cursor:pointer;}
    .modal-close{background:#ccc; color:#333;}
  </style>
</head>
<body>

  <!-- NAV hamburguesa -->
  <nav class="navbar">
    <div class="logo">
      <a href="/labora_db/filtros.php">Labora</a>
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
      <li><a href="#">Perfil</a></li>
      <li><a href="/labora_db/vistas/usuarios/configuracion.php">Configuración</a></li>
      <li><a href="/labora_db/funciones/logout.php">Cerrar sesión</a></li>
    </ul>
    <div class="menu-backdrop"></div>
  </nav>

  <div class="container">
    <!-- Foto -->
    <div class="profile-picture">
      <img src="<?php echo htmlspecialchars($ruta_foto); ?>" alt="Foto de Perfil">
    </div>

    <!-- Nombre -->
    <h1><?php echo htmlspecialchars($empleado['nombre']); ?></h1>
    <p style="text-align:center; font-size:1.1rem; color:gray;">
      <?php echo htmlspecialchars($empleado['titulo_profesional'] ?: ($empleado['profesion'] ?: 'Profesional')); ?>
    </p>

    <!-- ====== Valoración (promedio + total) ====== -->
    <section class="section">
      <h2>Valoración</h2>
      <?php if ($totalVotos > 0): ?>
        <div class="rating-header">
          <div class="rating-badge"><?php echo number_format($promedio1,1); ?>/5</div>
          <div style="font-size:1.2rem"><?php echo estrellas($promedio1); ?></div>
          <div class="muted">(<?php echo $totalVotos; ?> reseña<?php echo $totalVotos===1?'':'s'; ?>)</div>
        </div>
      <?php else: ?>
        <p class="muted">Este perfil aún no tiene valoraciones.</p>
      <?php endif; ?>
    </section>

    <!-- Información Personal -->
    <section class="section">
      <h2>Información Personal</h2>
      <div class="grid">
        <div>
          <label>Correo</label>
          <p><?php echo htmlspecialchars($empleado['correo']); ?></p>
        </div>
        <div>
          <label>Teléfono</label>
          <p><?php echo htmlspecialchars($empleado['telefono']); ?></p>
        </div>
        <div>
          <label>Zona de trabajo</label>
          <p><?php echo htmlspecialchars($empleado['zona_trabajo']); ?></p>
        </div>
      </div>
    </section>

    <!-- Sobre mí -->
    <?php if(!empty($empleado['descripcion_servicios'])): ?>
    <section class="section">
      <h2>Sobre <?php echo htmlspecialchars($empleado['nombre']); ?></h2>
      <p><?php echo nl2br(htmlspecialchars($empleado['descripcion_servicios'])); ?></p>
    </section>
    <?php endif; ?>

    <!-- Habilidades -->
    <?php if(!empty($empleado['habilidades'])): ?>
    <section class="section">
      <h2>Habilidades</h2>
      <p><?php echo nl2br(htmlspecialchars($empleado['habilidades'])); ?></p>
    </section>
    <?php endif; ?>

    <!-- Tarifas -->
    <?php if(strlen((string)$empleado['precio_hora'])): ?>
    <section class="section">
      <h2>Tarifa</h2>
      <p><strong>$<?php echo htmlspecialchars($empleado['precio_hora']); ?> ARS/hora</strong></p>
    </section>
    <?php endif; ?>

    <!-- Experiencia -->
    <?php if ($experiencia): ?>
    <section class="section">
      <h2>Experiencia Laboral</h2>
      <p><strong><?php echo htmlspecialchars($experiencia['puesto']); ?></strong> en <?php echo htmlspecialchars($experiencia['empresa']); ?></p>
      <p>Inicio: <?php echo htmlspecialchars($experiencia['fecha_inicio']); ?> - Fin: <?php echo htmlspecialchars($experiencia['fecha_fin']); ?></p>
      <p><?php echo nl2br(htmlspecialchars($experiencia['descripcion'])); ?></p>
    </section>
    <?php endif; ?>

    <!-- Educación -->
    <?php if ($educacion): ?>
    <section class="section">
      <h2>Educación</h2>
      <p><strong><?php echo htmlspecialchars($educacion['titulo']); ?></strong> en <?php echo htmlspecialchars($educacion['institucion']); ?></p>
      <p>Inicio: <?php echo htmlspecialchars($educacion['fecha_inicio']); ?> - Fin: <?php echo htmlspecialchars($educacion['fecha_fin']); ?></p>
    </section>
    <?php endif; ?>

    <!-- Portafolio -->
    <?php if (!empty($empleado['portafolio']) || !empty($empleado['portafolio_link'])): ?>
    <section class="section">
      <h2>Portafolio</h2>
      <?php if (!empty($empleado['portafolio'])): ?>
        <p><?php echo htmlspecialchars($empleado['portafolio']); ?></p>
      <?php endif; ?>
      <?php if (!empty($empleado['portafolio_link'])): ?>
        <a href="<?php echo htmlspecialchars($empleado['portafolio_link']); ?>" target="_blank" rel="noopener">Ver proyecto</a>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- ====== Formulario para dejar una valoración ====== -->
    <section class="section">
      <h2>Dejar una valoración</h2>

      <?php if (!$usuario_id): ?>
        <p class="muted">
          Para valorar este perfil, <a href="/labora_db/vistas/formulario/login.html">iniciá sesión</a>.
        </p>
      <?php elseif ($ya_valoro): ?>
        <p class="muted">Ya dejaste una valoración para este profesional. ¡Gracias!</p>
      <?php else: ?>
        <form method="POST" action="../../funciones/guardar_valoracion.php">
          <input type="hidden" name="id_empleado" value="<?php echo $id; ?>">
          <label for="puntuacion">Puntuación</label>
          <select id="puntuacion" name="puntuacion" required>
            <option value="">--Seleccionar--</option>
            <option value="5">⭐⭐⭐⭐⭐ (5)</option>
            <option value="4">⭐⭐⭐⭐ (4)</option>
            <option value="3">⭐⭐⭐ (3)</option>
            <option value="2">⭐⭐ (2)</option>
            <option value="1">⭐ (1)</option>
          </select>
          <label for="comentario">Comentario (opcional)</label>
          <textarea id="comentario" name="comentario" rows="3" placeholder="Contanos cómo fue tu experiencia"></textarea>
          <br>
          <button class="btn" type="submit">Enviar valoración</button>
        </form>
      <?php endif; ?>
    </section>

    <!-- ====== Últimos comentarios ====== -->
    <section class="section">
      <h2>Últimos comentarios</h2>
      <?php if ($comentarios->num_rows === 0): ?>
        <p class="muted">No hay comentarios todavía.</p>
      <?php else: ?>
        <?php while($c = $comentarios->fetch_assoc()): ?>
          <div class="comment">
            <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;">
              <strong><?php echo htmlspecialchars($c['usuario']); ?></strong>
              <span><?php echo estrellas((float)$c['puntuacion']); ?></span>
              <em class="muted"><?php echo date('d/m/Y', strtotime($c['fecha'])); ?></em>
            </div>
            <?php if(trim((string)$c['comentario']) !== ''): ?>
              <p><?php echo nl2br(htmlspecialchars($c['comentario'])); ?></p>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </section>
  </div>

  <!-- Botón de chat flotante: ahora abre el MODAL (no va directo a WhatsApp) -->
  <a
    class="chat-float"
    href="#"
    id="btnAbrirModal"
    aria-label="Abrir chat"
    title="Chatear"
  >
    <svg viewBox="0 0 64 64" aria-hidden="true">
      <circle cx="32" cy="32" r="30" class="bg"/>
      <path class="bubble" d="M48 30c0 8.28-7.61 15-17 15-2.66 0-5.17-.51-7.37-1.45L16 46l1.74-6.34A15.2 15.2 0 0 1 15 30c0-8.28 7.61-15 17-15s16 6.72 16 15z"/>
      <circle cx="26" cy="30" r="3" class="dot"/>
      <circle cx="32" cy="30" r="3" class="dot"/>
      <circle cx="38" cy="30" r="3" class="dot"/>
    </svg>
  </a>

  <!-- Modal de confirmación antes de WhatsApp -->
  <div class="modal-bg" id="modalContacto" style="display:none;">
    <div class="modal-card">
      <img class="worker" src="<?php echo htmlspecialchars($ruta_foto); ?>" alt="Foto trabajador">
      <h2 class="modal-title">Contactar a <?php echo htmlspecialchars($empleado['nombre']); ?></h2>
      <p class="modal-desc">Se abrirá WhatsApp con un mensaje que incluye tu ficha pública.</p>
      <?php if ($perfil_publico_link): ?>
        <p class="modal-link"><?php echo htmlspecialchars($perfil_publico_link); ?></p>
      <?php endif; ?>
      <div class="modal-actions">
        <button class="modal-btn" id="btnContinuarWA">Continuar a WhatsApp</button>
        <button class="modal-btn modal-close" id="btnCerrarModal">Cancelar</button>
      </div>
    </div>
  </div>

  <script>
    // Modal open/close
    const btnOpen = document.getElementById('btnAbrirModal');
    const btnClose = document.getElementById('btnCerrarModal');
    const modal = document.getElementById('modalContacto');
    const btnGo = document.getElementById('btnContinuarWA');

    btnOpen.addEventListener('click', function(e){
      e.preventDefault();
      modal.style.display = 'flex';
    });
    btnClose.addEventListener('click', function(){
      modal.style.display = 'none';
    });
    // Click afuera cierra
    modal.addEventListener('click', function(e){
      if (e.target === modal) modal.style.display = 'none';
    });
    // Ir a WhatsApp
    btnGo.addEventListener('click', function(){
      window.open('<?php echo htmlspecialchars($waLink, ENT_QUOTES, "UTF-8"); ?>', '_blank');
      modal.style.display = 'none';
    });
  </script>

  <script src="/labora_db/recursos/js/menu-hamburguesa.js"></script>
</body>
</html>
