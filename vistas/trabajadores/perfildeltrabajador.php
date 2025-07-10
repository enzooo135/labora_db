<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../formulario/login.html");
    exit();
}

include '../../config/conexion.php';

$id = $_SESSION['empleado_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zona = $_POST['zona_trabajo'];
    $sobre_mi = $_POST['descripcion'];
    $habilidades = $_POST['profesion'];
    $disponibilidad = $_POST['disponibilidad'];
    $precio = $_POST['precio_hora'];

    $stmt = $conn->prepare("UPDATE empleado SET zona_trabajo=?, descripcion_servicios=?, profesion=?, disponibilidad=?, precio_hora=? WHERE id_empleado=?");
    $stmt->bind_param("ssssdi", $zona, $sobre_mi, $habilidades, $disponibilidad, $precio, $id);
    $stmt->execute();
}

$sql = "SELECT * FROM empleado WHERE id_empleado = $id";
$resultado = $conn->query($sql);

if ($resultado && $resultado->num_rows === 1) {
    $empleado = $resultado->fetch_assoc();
} else {
    echo "Empleado no encontrado.";
    exit();
}
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
      <img src="" alt="Foto de perfil">
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
            $habilidades = explode(',', $empleado['profesion']);
            foreach ($habilidades as $hab) {
              echo '<div class="habilidad-tag">' . htmlspecialchars(trim($hab)) . '</div>';
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
