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
    $profesion = $_POST['profesion'];
    $disponibilidad = $_POST['disponibilidad'];
    $precio = $_POST['precio_hora'];
    $telefono = $_POST['telefono'];

    $titulo_profesional = $_POST['titulo_profesional'] ?? '';
    $habilidades = $_POST['habilidades'] ?? '';
    $educacion = $_POST['educacion'] ?? '';
    $experiencia = $_POST['experiencia'] ?? '';
    $portafolio = $_POST['portafolio'] ?? '';
    $portafolio_link = $_POST ['portafolio_link'] ?? '';
    
    //Tabla empleados
    $stmt = $conn->prepare("UPDATE empleado SET zona_trabajo=?, descripcion_servicios=?, profesion=?, disponibilidad=?, precio_hora=?, telefono=?, titulo_profesional=?, habilidades=?, educacion=?, experiencia=?, portafolio=?, portafolio_link=? WHERE id_empleado=?");
    $stmt->bind_param("ssssdsssssssi", $zona, $sobre_mi, $profesion, $disponibilidad, $precio, $telefono, $titulo_profesional, $habilidades, $educacion, $experiencia, $portafolio, $portafolio_link, $id);
    $stmt->execute();

    if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION);
        $foto_nueva = uniqid() . "." . strtolower($ext);
        $destino = __DIR__ . '/../../uploads/' . $foto_nueva;
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $extensiones_permitidas)) {
            move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $destino);
            $stmt_foto = $conn->prepare("UPDATE empleado SET foto_perfil = ? WHERE id_empleado = ?");
            $stmt_foto->bind_param("si", $foto_nueva, $id);
            $stmt_foto->execute();
        }
    }

    header("Location: vista_tra.php");
    exit();
}

$sql = "SELECT * FROM empleado WHERE id_empleado = $id";
$resultado = $conn->query($sql);
if (!$resultado || $resultado->num_rows !== 1) {
    echo "Empleado no encontrado.";
    exit();
}
$empleado = $resultado->fetch_assoc();

function esta_vacio($valor) {
    return !isset($valor) || trim($valor) === '';
}

$perfil_incompleto =
    esta_vacio($empleado['zona_trabajo']) ||
    esta_vacio($empleado['descripcion_servicios']) ||
    esta_vacio($empleado['profesion']) ||
    esta_vacio($empleado['disponibilidad']) ||
    esta_vacio($empleado['precio_hora']) ||
    esta_vacio($empleado['telefono']) ||
    esta_vacio($empleado['titulo_profesional']) ||
    esta_vacio($empleado['habilidades']) ||
    esta_vacio($empleado['educacion']) ||
    esta_vacio($empleado['experiencia']) ||
    esta_vacio($empleado['portafolio']) ||
    esta_vacio($empleado['portafolio_link']);
    
$ruta_foto = !empty($empleado['foto_perfil']) ? '../../uploads/' . $empleado['foto_perfil'] : 'placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Perfil del Trabajador</title>
  <style>
    :root {
        --azul-principal: #0077B6;
        --azul-secundario: #00B4D8;
        --azul-claro: #90E0EF;
        --fondo-principal: #d6f3ff;
         --fondo-secundario: #f0fbff;
        --texto: #333;
        --borde: #ccc;
        --blanco: #fff;
    }
    body {
        background-color: var(--fondo-principal);
        font-family: Arial, sans-serif;
        color: var(--texto);
        margin: 0; padding: 0;
      }
    .container {
      max-width: 960px;
        margin: 2rem auto;
        background: var(--blanco);
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
        position: relative;
    }
    .profile-picture {
        border-radius: 50%;
        overflow: hidden;
        width: 100px;
        height: 100px;
        margin: 1rem auto;
        border: 3px solid var(--azul-claro);
    }
    .profile-picture img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .alerta-perfil-incompleto {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .alerta-perfil-incompleto button {
        background-color: var(--azul-secundario);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }
    h2 {
        color: var(--azul-principal);
        margin-top: 2rem;
        margin-bottom: 0.8rem;
    }
    label {
        font-weight: 600;
        color: var(--azul-secundario);
        display: block;
        margin-bottom: 0.3rem;
    }
    p, input, textarea, a {
        font-size: 1rem;
        color: var(--texto);
    }
    a {
        color: var(--azul-secundario);
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    input[type=text], input[type=url], input[type=number], textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid var(--borde);
        border-radius: 5px;
        box-sizing: border-box;
        font-family: inherit;
        resize: vertical;
    }
    textarea {
        min-height: 80px;
    }
    .section {
        background-color: var(--blanco);
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.2rem;
    }
    .section h2 {
        color: var(--azul-principal);
        margin-bottom: .5rem;
    }
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
        gap: 1rem;
    }
    .border-t {
        border-top: 1px solid var(--borde);
        padding-top: 1rem;
        margin-top: 1rem;
    }

    .editable { display: none; margin-top: 5px; width: 100%; }

    .edit-button, .save-button {
        background-color: var(--azul-principal);
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        margin: 10px 5px;
    }
    .save-button { background-color: green; display: none; }
    .alerta-perfil-incompleto {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.2rem;
        text-align: center;
    }
  </style>

  <script>
    function toggleEdit() {
        document.querySelectorAll('.static').forEach(e => e.style.display = 'none');
        document.querySelectorAll('.editable').forEach(e => e.style.display = 'block');
        document.getElementById('boton-editar').style.display = 'none';
        document.getElementById('boton-aplicar').style.display = 'inline-block';
    }
  </script>

</head>

<body>
  <div class="container">
    <?php if ($perfil_incompleto): ?>
      <div class="alerta-perfil-incompleto">
        <strong>¡Tu perfil está incompleto!</strong><br>
        Completalo para mejorar tus oportunidades laborales.<br><br>
        <button onclick="toggleEdit()">Editar ahora</button>
      </div>
    <?php endif; ?>

    <div class="profile-picture">

      <img src="<?php echo htmlspecialchars($ruta_foto); ?>" alt="Foto de Perfil">
    
    </div>


    <form method="POST" enctype="multipart/form-data">
      <section class="section editable">
        <h2>Foto de Perfil</h2>
        <input type="file" name="nueva_foto" accept="image/*">
      </section>

      <section class="section">
        <h2>Información Personal</h2>
        <div class="grid">
          <div>
            <label>Nombre</label>
            <p><?php echo htmlspecialchars($empleado['nombre']); ?></p>
          </div>
          <div>
            <label>Zona</label>
            <p class="static"><?php echo htmlspecialchars($empleado['zona_trabajo']); ?></p>
            <input class="editable" type="text" name="zona_trabajo" value="<?php echo htmlspecialchars($empleado['zona_trabajo']); ?>">
          </div>
          <div>
            <label>Correo</label>
            <p><?php echo htmlspecialchars($empleado['correo']); ?></p>
          </div>
          <div>
            <label>Teléfono</label>
            <p class="static"><?php echo htmlspecialchars($empleado['telefono']); ?></p>
            <input class="editable" type="editable" type="Number" name="telefono" value="<?php echo htmlspecialchars($empleado['telefono']); ?>">
          </div>
        </div>
      </section>

      <section class="section"><h2>Título Profesional</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['titulo_profesional']); ?></p>
        <input class="editable" type="text" name="titulo_profesional" value="<?php echo htmlspecialchars($empleado['titulo_profesional']); ?>">
      </section>

      <section class="section"><h2>Acerca de Mí</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['descripcion_servicios']); ?></p>
        <textarea class="editable" name="descripcion"><?php echo htmlspecialchars($empleado['descripcion_servicios']); ?></textarea>
      </section>

      <section class="section"><h2>Profesión</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['profesion']); ?></p>
        <input class="editable" type="text" name="profesion" value="<?php echo htmlspecialchars($empleado['profesion']); ?>">
      </section>

      <section class="section"><h2>Habilidades</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['habilidades']); ?></p>
        <textarea class="editable" name="habilidades"><?php echo htmlspecialchars($empleado['habilidades']); ?></textarea>
      </section>

      <section class="section"><h2>Disponibilidad</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['disponibilidad']); ?></p>
        <input class="editable" type="text" name="disponibilidad" value="<?php echo htmlspecialchars($empleado['disponibilidad']); ?>">
      </section>

      <section class="section">
        <h2>Tarifas y Disponibilidad</h2>
        <div class="grid">
            <div>
                <label>Tarifa por Hora (USD)</label>
                <p class="static">$<?php echo htmlspecialchars($empleado['precio_hora']); ?></p>
                <input class="editable" style="display:none;" type="number" step="0.01" name="precio_hora" value="<?php echo htmlspecialchars($empleado['precio_hora']); ?>">
            </div>
            <div>
                <label>Disponibilidad</label>
                <p class="static"><?php echo htmlspecialchars($empleado['disponibilidad']); ?></p>
                <input class="editable" style="display:none;" type="text" name="disponibilidad" value="<?php echo htmlspecialchars($empleado['disponibilidad']); ?>">
            </div>
        </div>
    </section>

      <section class="section"><h2>Experiencia</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['experiencia']); ?></p>
        <textarea class="editable" name="experiencia"><?php echo htmlspecialchars($empleado['experiencia']); ?></textarea>
      </section>

      <section class="section"><h2>Educación</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['educacion']); ?></p>
        <textarea class="editable" name="educacion"><?php echo htmlspecialchars($empleado['educacion']); ?></textarea>
      </section>

      <section class="section"><h2>Portafolio</h2>
        <div class="grid">
            <div>
                <label>Titulo del proyecto</label>
                <p class="static"><?php echo htmlspecialchars($empleado['portafolio']); ?></p>
                <input class="editable" style="display:none;" type="text" name="portafolio" value="<?php echo htmlspecialchars($empleado['portafolio']); ?>">
            </div>
            <div>
                <label>Enlace</label>
                <p class="static"><?php echo htmlspecialchars($empleado['portafolio_link']); ?></p>
                <input class="editable" style="display:none;" type="text" name="portafolio_link" value="<?php echo htmlspecialchars($empleado['portafolio_link']); ?>">
            </div>
        </div>
      </section>

      <button type="button" id="boton-editar" class="edit-button" onclick="toggleEdit()">Editar información</button>
      <button type="submit" id="boton-aplicar" class="save-button">Aplicar cambios</button>
    </form>
  </div>
</body>
</html>