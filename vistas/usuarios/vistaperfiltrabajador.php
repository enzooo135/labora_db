<?php
session_start();
include '../../config/conexion.php';

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

// 5) Foto
$ruta_foto = !empty($empleado['foto_perfil'])
    ? '../../uploads/' . $empleado['foto_perfil']
    : '../../imagenes/default_user.jpg';

// 6) ----- Valoraciones: promedio + total -----
$sqlVal = "SELECT AVG(puntuacion) AS promedio, COUNT(*) AS total FROM valoraciones WHERE id_empleado = ?";
$stmtVal = $conn->prepare($sqlVal);
$stmtVal->bind_param("i", $id);
$stmtVal->execute();
$sumVal = $stmtVal->get_result()->fetch_assoc();
$promedio = isset($sumVal['promedio']) ? (float)$sumVal['promedio'] : 0.0;
$promedio1 = $promedio ? round($promedio, 1) : 0.0;
$totalVotos = (int)($sumVal['total'] ?? 0);

// 7) Últimos comentarios
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

// 8) ¿Usuario logueado y ya valoró?
$usuario_id = $_SESSION['usuario_id'] ?? null;  // <-- asumo que tu login de usuarios setea esto
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
  </style>
</head>
<body>
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
      <h2>Sobre mí</h2>
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
      <p><?php echo htmlspecialchars($experiencia['fecha_inicio']); ?> - <?php echo htmlspecialchars($experiencia['fecha_fin']); ?></p>
      <p><?php echo nl2br(htmlspecialchars($experiencia['descripcion'])); ?></p>
    </section>
    <?php endif; ?>

    <!-- Educación -->
    <?php if ($educacion): ?>
    <section class="section">
      <h2>Educación</h2>
      <p><strong><?php echo htmlspecialchars($educacion['titulo']); ?></strong> en <?php echo htmlspecialchars($educacion['institucion']); ?></p>
      <p><?php echo htmlspecialchars($educacion['fecha_inicio']); ?> - <?php echo htmlspecialchars($educacion['fecha_fin']); ?></p>
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
</body>
</html>
