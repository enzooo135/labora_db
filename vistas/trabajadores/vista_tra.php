<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../formulario/login.html");
    exit();
}

include '../../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$id = $_SESSION['empleado_id'];

// ====== Helper para BASE_URL absoluta (p/ rutas de imágenes) ======
function base_url_root(): string {
  $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/')); // ej: labora_db/vistas/empleado/vista_tra.php
  return '/' . ($parts[0] ?? '');
}
$BASE_URL = base_url_root(); // -> /labora_db

// ====== Configuraciones de selects ======
$ZONAS_PERMITIDAS = ['Moreno', 'Paso del rey', 'Merlo', 'Padua', 'Ituzaingo'];

// Catálogo de habilidades (se guarda como string separado por comas)
$HABILIDADES_CATALOGO = [
  'Puntualidad',
  'Comunicación',
  'Trabajo en equipo',
  'Resolución de problemas',
  'Atención al detalle',
  'Herramientas propias',
  'Licencia de conducir',
  'Disponibilidad para urgencias',
];

// Catálogo de disponibilidades (validado en POST y mostrado como <select>)
$DISPONIBILIDADES = [
  'Full time',
  'Part time',
  'Horario flexible',
  'Fines de semana',
  'Turnos nocturnos',
  'Por proyecto',
  'Rotativo',
  'Guardias / urgencias'
];

// ====== POST (guardar) ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Zona (validada contra whitelist)
    $zona = trim($_POST['zona_trabajo'] ?? '');
    if (!in_array($zona, $ZONAS_PERMITIDAS, true)) {
        $zona = null;
    }

    $sobre_mi       = trim($_POST['descripcion'] ?? '');

    // Disponibilidad (validada contra whitelist)
    $disponibilidad_post = trim($_POST['disponibilidad'] ?? '');
    $disponibilidad = in_array($disponibilidad_post, $DISPONIBILIDADES, true) ? $disponibilidad_post : null;

    $precio         = is_numeric($_POST['precio_hora'] ?? '') ? (float)$_POST['precio_hora'] : null;
    $telefono       = trim($_POST['telefono'] ?? '');

    // Habilidades: viene como array (multi-select), lo almacenamos como string separado por comas
    $habilidades_sel = $_POST['habilidades'] ?? [];
    if (!is_array($habilidades_sel)) $habilidades_sel = [];
    $habilidades_filtradas = array_values(array_intersect($habilidades_sel, $HABILIDADES_CATALOGO));
    $habilidades = implode(', ', $habilidades_filtradas);

    $portafolio     = trim($_POST['portafolio'] ?? '');
    $portafolio_link= trim($_POST['portafolio_link'] ?? '');

    // UPDATE tabla empleado (sin profesion ni titulo_profesional: ADMIN ONLY)
    $sql = "UPDATE empleado
               SET zona_trabajo = COALESCE(?, zona_trabajo),
                   descripcion_servicios = ?,
                   disponibilidad = COALESCE(?, disponibilidad),
                   precio_hora = ?,
                   telefono = ?,
                   habilidades = ?,
                   portafolio = ?,
                   portafolio_link = ?
             WHERE id_empleado = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssdssssi",
        $zona,                // s (puede ser null, usamos COALESCE en SQL)
        $sobre_mi,            // s
        $disponibilidad,      // s (validada)
        $precio,              // d
        $telefono,            // s
        $habilidades,         // s
        $portafolio,          // s
        $portafolio_link,     // s
        $id                   // i
    );
    $stmt->execute();

    // UPDATE tabla experiencia_laboral
    $puesto            = $_POST['puesto'] ?? '';
    $empresa           = $_POST['empresa'] ?? '';
    $contacto          = $_POST['contacto_referencia'] ?? '';
    $fecha_inicio      = $_POST['fecha_inicio'] ?? '';
    $fecha_fin         = $_POST['fecha_fin'] ?? '';
    $descripcion_exp   = $_POST['descripcion_experiencia'] ?? '';

    $check = $conn->prepare("SELECT id_experiencia FROM experiencia_laboral WHERE id_empleado = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result_check = $check->get_result();

    if ($result_check->num_rows > 0) {
        $stmt_exp = $conn->prepare("UPDATE experiencia_laboral
                                       SET puesto = ?, empresa = ?, contacto_referencia = ?, fecha_inicio = ?, fecha_fin = ?, descripcion = ?
                                     WHERE id_empleado = ?");
        $stmt_exp->bind_param("ssssssi", $puesto, $empresa, $contacto, $fecha_inicio, $fecha_fin, $descripcion_exp, $id);
    } else {
        $stmt_exp = $conn->prepare("INSERT INTO experiencia_laboral (id_empleado, puesto, empresa, contacto_referencia, fecha_inicio, fecha_fin, descripcion)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_exp->bind_param("issssss", $id, $puesto, $empresa, $contacto, $fecha_inicio, $fecha_fin, $descripcion_exp);
    }
    $stmt_exp->execute();

    // UPDATE tabla educacion
    $titulo_edu        = $_POST['titulo_edu'] ?? '';
    $institucion_edu   = $_POST['institucion_edu'] ?? '';
    $fecha_inicio_edu  = $_POST['fecha_inicio_edu'] ?? '';
    $fecha_fin_edu     = $_POST['fecha_fin_edu'] ?? '';

    $check_edu = $conn->prepare("SELECT id_educacion FROM educacion WHERE id_empleado = ?");
    $check_edu->bind_param("i", $id);
    $check_edu->execute();
    $result_check_edu = $check_edu->get_result();

    if ($result_check_edu->num_rows > 0) {
        $stmt_edu = $conn->prepare("UPDATE educacion
                                       SET titulo = ?, institucion = ?, fecha_inicio = ?, fecha_fin = ?
                                     WHERE id_empleado = ?");
        $stmt_edu->bind_param("ssssi", $titulo_edu, $institucion_edu, $fecha_inicio_edu, $fecha_fin_edu, $id);
    } else {
        $stmt_edu = $conn->prepare("INSERT INTO educacion (id_empleado, titulo, institucion, fecha_inicio, fecha_fin)
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt_edu->bind_param("issss", $id, $titulo_edu, $institucion_edu, $fecha_inicio_edu, $fecha_fin_edu);
    }
    $stmt_edu->execute();

    // FOTO DE PERFIL
    if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $extensiones_permitidas, true)) {
            $foto_nueva = uniqid('pf_', true) . "." . $ext;
            $destino = __DIR__ . '/../../uploads/' . $foto_nueva;
            if (move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $destino)) {
                $stmt_foto = $conn->prepare("UPDATE empleado SET foto_perfil = ? WHERE id_empleado = ?");
                $stmt_foto->bind_param("si", $foto_nueva, $id);
                $stmt_foto->execute();
            }
        }
    }

    header("Location: vista_tra.php");
    exit();
}

// ====== GET (cargar datos) ======
$sql = "SELECT * FROM empleado WHERE id_empleado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
if (!$resultado || $resultado->num_rows !== 1) {
    echo "Empleado no encontrado.";
    exit();
}
$empleado = $resultado->fetch_assoc();

$sql_exp = "SELECT * FROM experiencia_laboral WHERE id_empleado = ? LIMIT 1";
$stmt_exp_get = $conn->prepare($sql_exp);
$stmt_exp_get->bind_param("i", $id);
$stmt_exp_get->execute();
$res_exp = $stmt_exp_get->get_result();
$experiencia = $res_exp->fetch_assoc() ?? [
    'puesto' => '',
    'empresa' => '',
    'contacto_referencia' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'descripcion' => ''
];

$sql_edu = "SELECT * FROM educacion WHERE id_empleado = ? LIMIT 1";
$stmt_edu_get = $conn->prepare($sql_edu);
$stmt_edu_get->bind_param("i", $id);
$stmt_edu_get->execute();
$res_edu = $stmt_edu_get->get_result();
$educacion = $res_edu->fetch_assoc() ?? [
    'titulo' => '',
    'institucion' => '',
    'fecha_inicio' => '',
    'fecha_fin' => ''
];

// ====== Perfil incompleto (solo campos que completa el trabajador) ======
function esta_vacio($valor) { return !isset($valor) || trim((string)$valor) === ''; }

$faltantes = [];

if (esta_vacio($empleado['zona_trabajo']))          $faltantes[] = 'Zona de trabajo';
if (esta_vacio($empleado['descripcion_servicios'])) $faltantes[] = 'Acerca de mí / descripción de servicios';
if (esta_vacio($empleado['disponibilidad']))        $faltantes[] = 'Disponibilidad';
if (esta_vacio($empleado['precio_hora']))           $faltantes[] = 'Tarifa por hora';
if (esta_vacio($empleado['telefono']))              $faltantes[] = 'Teléfono';

// OJO con habilidades: si elegiste opciones fuera del catálogo, quedan vacías al filtrar.
if (esta_vacio($empleado['habilidades']))           $faltantes[] = 'Habilidades';

// Educación (si pedís que sea obligatoria)
if (esta_vacio($educacion['titulo']))               $faltantes[] = 'Título (Educación)';
if (esta_vacio($educacion['institucion']))          $faltantes[] = 'Institución (Educación)';

// Experiencia (si pedís que sea obligatoria)
if (esta_vacio($experiencia['descripcion']))        $faltantes[] = 'Descripción (Experiencia)';

// NO pedimos: profesion, titulo_profesional (ADMIN ONLY)
// NO pedimos: portafolio ni portafolio_link porque ya no están en el formulario

$perfil_incompleto = count($faltantes) > 0;

// ====== FOTO: default si no hay subida ======
if (!empty($empleado['foto_perfil'])) {
    $ruta_foto = $BASE_URL . '/uploads/' . $empleado['foto_perfil'];
} else {
    // Default solicitado
    $ruta_foto = $BASE_URL . '/imagenes/default_user.jpg';
}

// Para preseleccionar habilidades desde la BD
$habilidades_actuales = array_map('trim', array_filter(explode(',', (string)($empleado['habilidades'] ?? ''))));

// Helper para mostrar ARS formateado
function mostrar_ars($monto) {
    if ($monto === null || $monto === '' ) return '—';
    $num = (float)$monto;
    return '$ ' . number_format($num, 2, ',', '.'); // $ 12.345,67
}
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
    body { background-color: var(--fondo-principal); font-family: Arial, sans-serif; color: var(--texto); margin: 0; padding: 0; }
    .container { max-width: 960px; margin: 2rem auto; background: var(--blanco); padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgb(0 0 0 / 0.1); position: relative; }
    .profile-picture { border-radius: 50%; overflow: hidden; width: 100px; height: 100px; margin: 1rem auto; border: 3px solid var(--azul-claro); }
    .profile-picture img { width: 100%; height: 100%; object-fit: cover; }
    .alerta-perfil-incompleto { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 1rem; border-radius: 8px; margin-bottom: 1.2rem; text-align:center; }
    .alerta-perfil-incompleto button { background-color: var(--azul-secundario); color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; margin-top: 10px; }
    h2 { color: var(--azul-principal); margin-top: 2rem; margin-bottom: 0.8rem; }
    label { font-weight: 600; color: var(--azul-secundario); display: block; margin-bottom: 0.3rem; }
    p, input, textarea, a, select { font-size: 1rem; color: var(--texto); }
    a { color: var(--azul-secundario); text-decoration: none; }
    a:hover { text-decoration: underline; }
    input[type=text], input[type=url], input[type=number], input[type=tel], textarea, select { width: 100%; padding: 0.5rem; border: 1px solid var(--borde); border-radius: 5px; box-sizing: border-box; font-family: inherit; resize: vertical; }
    textarea { min-height: 80px; }
    .section { background-color: var(--blanco); border: 1px solid #ccc; border-radius: 8px; padding: 1rem; margin-bottom: 1.2rem; }
    .section h2 { color: var(--azul-principal); margin-bottom: .5rem; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(270px,1fr)); gap: 1rem; }
    .border-t { border-top: 1px solid var(--borde); padding-top: 1rem; margin-top: 1rem; }
    .editable { display: none; margin-top: 5px; width: 100%; }
    .edit-button, .save-button { background-color: var(--azul-principal); color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; margin: 10px 5px; }
    .save-button { background-color: green; display: none; }
    .helper { font-size: .9rem; color: #555; margin-top: 4px; }
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
      Completá los siguientes campos para mejorar tus oportunidades:
      <ul style="text-align:left; margin:10px auto; max-width:560px">
        <?php foreach ($faltantes as $f): ?>
          <li><?php echo htmlspecialchars($f); ?></li>
        <?php endforeach; ?>
      </ul>
      <button onclick="toggleEdit()">Editar ahora</button>
    </div>
  <?php endif; ?>

    <div class="profile-picture">
      <!-- Imagen default si no tiene -->
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
            <label>Nombre Completo</label>
            <p><?php echo htmlspecialchars($empleado['nombre']); ?></p>
          </div>

          <div>
            <!-- ZONA: select (Moreno, Paso del rey, Merlo, Padua, Ituzaingo) -->
            <label>Zona</label>
            <p class="static"><?php echo htmlspecialchars($empleado['zona_trabajo']); ?></p>
            <select class="editable" name="zona_trabajo">
              <option value="" disabled <?php echo empty($empleado['zona_trabajo']) ? 'selected' : ''; ?>>Elegí tu zona…</option>
              <?php foreach ($ZONAS_PERMITIDAS as $z): ?>
                <option value="<?php echo htmlspecialchars($z); ?>" <?php echo ($empleado['zona_trabajo'] === $z) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($z); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label>Correo</label>
            <p><?php echo htmlspecialchars($empleado['correo']); ?></p>
          </div>

          <div>
            <label>Teléfono</label>
            <p class="static"><?php echo htmlspecialchars($empleado['telefono']); ?></p>
            <input class="editable" type="tel" name="telefono" value="<?php echo htmlspecialchars($empleado['telefono']); ?>">
          </div>
        </div>
      </section>

      <!-- Título Profesional (ADMIN ONLY) -->
      <section class="section">
        <h2>Título Profesional</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['titulo_profesional']); ?></p>
        <!-- Sin input: lo define el administrador -->
      </section>

      <section class="section">
        <h2>Acerca de mí / descripción de servicios</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['descripcion_servicios']); ?></p>
        <textarea class="editable" name="descripcion"><?php echo htmlspecialchars($empleado['descripcion_servicios']); ?></textarea>
      </section>

      <!-- Profesión (ADMIN ONLY) -->
      <section class="section">
        <h2>Profesión</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['profesion']); ?></p>
        <!-- Sin input: lo define el administrador -->
      </section>

      <!-- Habilidades: select múltiple -->
      <section class="section">
        <h2>Habilidades</h2>
        <p class="static"><?php echo htmlspecialchars($empleado['habilidades']); ?></p>
        <select class="editable" name="habilidades[]" multiple size="6">
          <?php foreach ($HABILIDADES_CATALOGO as $hab): ?>
            <option value="<?php echo htmlspecialchars($hab); ?>" <?php echo in_array($hab, $habilidades_actuales, true) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($hab); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="editable helper">Sostené CTRL (o CMD en Mac) para seleccionar varias.</p>
      </section>

      <section class="section">
        <h2>Tarifas y Disponibilidad</h2>
        <div class="grid">
          <div>
            <label>Tarifa por Hora (ARS)</label>
            <p class="static"><?php echo mostrar_ars($empleado['precio_hora']); ?></p>
            <input class="editable" type="number" step="0.01" min="0" name="precio_hora" value="<?php echo htmlspecialchars($empleado['precio_hora']); ?>" placeholder="Ej: 2500.00">
            <p class="editable helper">Ingresá con punto decimal (ej.: <b>2500.50</b>). Se mostrará como <b>$ 2.500,50</b>.</p>
          </div>
          <div>
            <label>Disponibilidad</label>
            <p class="static"><?php echo htmlspecialchars($empleado['disponibilidad']); ?></p>
            <!-- SELECT de disponibilidad -->
            <select class="editable" name="disponibilidad">
              <option value="" disabled <?php echo empty($empleado['disponibilidad']) ? 'selected' : ''; ?>>Elegí tu disponibilidad…</option>
              <?php foreach ($DISPONIBILIDADES as $disp): ?>
                <option value="<?php echo htmlspecialchars($disp); ?>" <?php echo ($empleado['disponibilidad'] === $disp) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($disp); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </section>

      <section class="section">
        <h2>Experiencia Laboral</h2>
        <div class="grid">
          <div>
            <label>Puesto</label>
            <p class="static"><?php echo htmlspecialchars($experiencia['puesto']); ?></p>
            <input class="editable" type="text" name="puesto" value="<?php echo htmlspecialchars($experiencia['puesto']); ?>">
          </div>
          <div>
            <label>Empresa</label>
            <p class="static"><?php echo htmlspecialchars($experiencia['empresa']); ?></p>
            <input class="editable" type="text" name="empresa" value="<?php echo htmlspecialchars($experiencia['empresa']); ?>">
          </div>
          <div>
            <label>Contacto</label>
            <p class="static"><?php echo htmlspecialchars($experiencia['contacto_referencia']); ?></p>
            <input class="editable" type="text" name="contacto_referencia" value="<?php echo htmlspecialchars($experiencia['contacto_referencia']); ?>">
          </div>
          <div>
            <label>Fecha de inicio</label>
            <p class="static"><?php echo htmlspecialchars($experiencia['fecha_inicio']); ?></p>
            <input class="editable" type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($experiencia['fecha_inicio']); ?>">
          </div>
          <div>
            <label>Fecha de fin</label>
            <p class="static"><?php echo htmlspecialchars($experiencia['fecha_fin']); ?></p>
            <input class="editable" type="date" name="fecha_fin" value="<?php echo htmlspecialchars($experiencia['fecha_fin']); ?>">
          </div>
          <div class="grid" style="grid-column: 1 / -1;">
            <label>Descripción</label>
            <p class="static"><?php echo htmlspecialchars($experiencia['descripcion']); ?></p>
            <textarea class="editable" name="descripcion_experiencia"><?php echo htmlspecialchars($experiencia['descripcion']); ?></textarea>
          </div>
        </div>
      </section>

      <section class="section">
        <h2>Educación</h2>
        <div class="grid">
          <div>
            <label>Título</label>
            <p class="static"><?php echo htmlspecialchars($educacion['titulo']); ?></p>
            <input class="editable" type="text" name="titulo_edu" value="<?php echo htmlspecialchars($educacion['titulo']); ?>">
          </div>
          <div>
            <label>Institución</label>
            <p class="static"><?php echo htmlspecialchars($educacion['institucion']); ?></p>
            <input class="editable" type="text" name="institucion_edu" value="<?php echo htmlspecialchars($educacion['institucion']); ?>">
          </div>
          <div>
            <label>Fecha de inicio</label>
            <p class="static"><?php echo htmlspecialchars($educacion['fecha_inicio']); ?></p>
            <input class="editable" type="date" name="fecha_inicio_edu" value="<?php echo htmlspecialchars($educacion['fecha_inicio']); ?>">
          </div>
          <div>
            <label>Fecha de finalización</label>
            <p class="static"><?php echo htmlspecialchars($educacion['fecha_fin']); ?></p>
            <input class="editable" type="date" name="fecha_fin_edu" value="<?php echo htmlspecialchars($educacion['fecha_fin']); ?>">
          </div>
        </div>
      </section>


      <button type="button" id="boton-editar" class="edit-button" onclick="toggleEdit()">Editar información</button>
      <button type="submit" id="boton-aplicar" class="save-button">Aplicar cambios</button>
    </form>
  </div>
</body>
</html>
