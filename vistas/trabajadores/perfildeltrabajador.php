<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../formulario/login.html");
    exit();
}

include '../../config/conexion.php';

$id = (int)$_SESSION['empleado_id'];

/* ------------------------------ Actualizaciones ---------------------------- */
// (Dejamos tu lógica de actualización de campos como estaba)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zona           = $_POST['zona_trabajo']   ?? '';
    $sobre_mi       = $_POST['descripcion']    ?? '';
    $habilidades    = $_POST['profesion']      ?? '';
    $disponibilidad = $_POST['disponibilidad'] ?? '';
    $precio         = $_POST['precio_hora']    ?? '';

    $stmt = $conn->prepare("UPDATE empleado 
                            SET zona_trabajo=?, descripcion_servicios=?, profesion=?, disponibilidad=?, precio_hora=?
                            WHERE id_empleado=?");
    if ($stmt) {
        $stmt->bind_param("ssssdi", $zona, $sobre_mi, $habilidades, $disponibilidad, $precio, $id);
        $stmt->execute();
    }
}

/* ---------------------------- Obtener datos actuales ------------------------ */
$sql = "SELECT * FROM empleado WHERE id_empleado = $id";
$resultado = $conn->query($sql);

if (!$resultado || $resultado->num_rows !== 1) {
    echo "Empleado no encontrado.";
    exit();
}
$empleado = $resultado->fetch_assoc();

/* ------------------------- Helpers para armar URL foto --------------------- */
function base_url_root_pf(): string {
    $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    return '/' . ($parts[0] ?? '');
}
function foto_bd_a_url_pf(?string $v, string $BASE_URL, string $default): string {
    $v = trim((string)$v);
    if ($v === '') return $default;
    if (preg_match('#^https?://#i', $v)) return $v;     // URL completa
    if (strpos($v, '/') === 0) return $v;               // ruta web absoluta
    if (strpos($v, '/') !== false) return rtrim($BASE_URL, '/') . '/' . ltrim($v, '/'); // carpeta/archivo
    return rtrim($BASE_URL, '/') . '/uploads/' . $v;    // solo nombre -> /uploads/archivo
}

$BASE_URL        = base_url_root_pf();                    // ej: /labora_db
$DEFAULT_IMG_URL = $BASE_URL . '/imagenes/default_user.jpg';
$fotoPerfil      = foto_bd_a_url_pf($empleado['foto_perfil'] ?? '', $BASE_URL, $DEFAULT_IMG_URL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil del Trabajador</title>
  <link rel="stylesheet" href="../../recursos/css/profile.css">
  <script>
    function toggleEdit() {
      document.querySelectorAll('.static').forEach(e => e.style.display = 'none');
      document.querySelectorAll('.editable').forEach(e => e.style.display = 'block');
      document.getElementById('boton-editar').style.display = 'none';
      document.getElementById('boton-aplicar').style.display = 'block';
    }
  </script>
</head>
<body>
  <div class="perfil-container">
    <div class="perfil-header">
      <img src="<?php echo htmlspecialchars($fotoPerfil); ?>" alt="Foto de perfil" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:2px solid #005F8C;">
      <div>
        <h1><?php echo htmlspecialchars($empleado['nombre']); ?></h1>
        <p><?php echo htmlspecialchars($empleado['profesion']); ?></p>
      </div>
    </div>

    <form method="POST">
      <div class="perfil-section">
        <h2>Información Personal</h2>
        <div class="static">
          <p><strong>Email:</strong> <?php echo htmlspecialchars($empleado['correo']); ?></p>
          <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($empleado['telefono'] ?? 'No disponible'); ?></p>
          <p><strong>Zona de trabajo:</strong> <?php echo htmlspecialchars($empleado['zona_trabajo']); ?></p>
        </div>
        <div class="editable" style="display:none">
          <input type="text" name="zona_trabajo" value="<?php echo htmlspecialchars($empleado['zona_trabajo']); ?>">
        </div>
      </div>

      <div class="perfil-section">
        <h2>Sobre mí</h2>
        <div class="static">
          <p><?php echo htmlspecialchars($empleado['descripcion_servicios']); ?></p>
        </div>
        <div class="editable" style="display:none">
          <textarea name="descripcion" rows="4"><?php echo htmlspecialchars($empleado['descripcion_servicios']); ?></textarea>
        </div>
      </div>

      <div class="perfil-section">
        <h2>Profesiones</h2>
        <div class="static">
          <div class="habilidades">
            <?php
              $habilidades = explode(',', $empleado['profesion'] ?? '');
              foreach ($habilidades as $hab) {
                $hab = trim($hab);
                if ($hab === '') continue;
                echo '<div class="habilidad-tag">' . htmlspecialchars($hab) . '</div>';
              }
            ?>
          </div>
        </div>
        <div class="editable" style="display:none">
          <textarea name="profesion" rows="2"><?php echo htmlspecialchars($empleado['profesion']); ?></textarea>
        </div>
      </div>

      <div class="perfil-section">
        <h2>Disponibilidad</h2>
        <div class="static">
          <p><?php echo htmlspecialchars($empleado['disponibilidad']); ?></p>
        </div>
        <div class="editable" style="display:none">
          <input type="text" name="disponibilidad" value="<?php echo htmlspecialchars($empleado['disponibilidad']); ?>">
        </div>
      </div>

      <div class="perfil-section">
        <h2>Precio por hora</h2>
        <div class="static">
          <p>$<?php echo htmlspecialchars($empleado['precio_hora']); ?> ARS</p>
        </div>
        <div class="editable" style="display:none">
          <input type="text" name="precio_hora" value="<?php echo htmlspecialchars($empleado['precio_hora']); ?>">
        </div>
      </div>

      <button type="button" id="boton-editar" class="boton-editar" onclick="toggleEdit()">Editar información</button>
      <button type="submit" id="boton-aplicar" class="boton-aplicar" style="display:none;">Aplicar cambios</button>
    </form>
  </div>
</body>
</html>
