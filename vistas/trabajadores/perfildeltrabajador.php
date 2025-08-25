<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../formulario/login.html");
    exit();
}
include '../../config/conexion.php';

$id = (int)$_SESSION['empleado_id'];

/* ===================== HELPERS ===================== */
function save_as_jpg($tmpPath, $mime, $destPath) {
    if ($mime === 'image/jpeg') {
        $img = @imagecreatefromjpeg($tmpPath);
        if (!$img) return false;
        $ok = imagejpeg($img, $destPath, 85);
        imagedestroy($img);
        return $ok;
    }
    if ($mime === 'image/png') {
        $src = @imagecreatefrompng($tmpPath);
        if (!$src) return false;
        $w = imagesx($src); $h = imagesy($src);
        $bg = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefilledrectangle($bg, 0, 0, $w, $h, $white);
        imagecopy($bg, $src, 0, 0, 0, 0, $w, $h);
        $ok = imagejpeg($bg, $destPath, 85);
        imagedestroy($src);
        imagedestroy($bg);
        return $ok;
    }
    return false;
}

$uploadDir = __DIR__ . '/../../uploads/Foto_Perfil';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

/* ============= MODO API: SUBIDA DE FOTO (AJAX) ============= */
/* Llama con POST a perfildeltrabajador.php?upload=1 */
if (isset($_GET['upload'])) {
    header('Content-Type: application/json; charset=utf-8');

    if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'No llegó el archivo']);
        exit();
    }
    if ($_FILES['foto']['size'] > 3*1024*1024) {
        http_response_code(413);
        echo json_encode(['ok'=>false,'error'=>'Máximo 3MB']);
        exit();
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['foto']['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg','image/png'], true)) {
        http_response_code(415);
        echo json_encode(['ok'=>false,'error'=>'Solo JPG o PNG']);
        exit();
    }

    // Traigo nombre y foto anterior
    $q = $conn->prepare("SELECT nombre, foto_perfil FROM empleado WHERE id_empleado=?");
    $q->bind_param("i", $id);
    $q->execute();
    $r  = $q->get_result();
    $row = $r->fetch_assoc();
    $q->close();

    $nombre = $row['nombre'] ?: ('Empleado '.$id);
    // limpiar caracteres ilegales para nombre de archivo (dejamos espacios/acentos)
    $base = preg_replace('/[\\\\\/:*?"<>|]/', '', trim($nombre));
    if ($base === '') $base = 'Empleado';

    $filename = $base . '_' . $id . '.jpg';
    $dest     = $uploadDir . '/' . $filename;

    // Guardar siempre como JPG con nuestro nombre final
    if (!save_as_jpg($_FILES['foto']['tmp_name'], $mime, $dest)) {
        http_response_code(500);
        echo json_encode(['ok'=>false,'error'=>'No se pudo guardar la imagen']);
        exit();
    }

    // Borrar anterior si es distinto
    if (!empty($row['foto_perfil']) && $row['foto_perfil'] !== $filename) {
        $old = $uploadDir . '/' . $row['foto_perfil'];
        if (is_file($old)) @unlink($old);
    }

    // Actualizar BD
    $u = $conn->prepare("UPDATE empleado SET foto_perfil=? WHERE id_empleado=?");
    $u->bind_param("si", $filename, $id);
    $u->execute();
    $u->close();

    // URL pública (ajustá el prefijo si tu raíz no es /labora_db)
    $BASE_PUBLICA = '/labora_db/uploads/Foto_Perfil/';
    echo json_encode([
        'ok'       => true,
        'foto'     => $filename,
        'foto_url' => $BASE_PUBLICA . rawurlencode($filename)
    ]);
    exit();
}

/* ============= GUARDADO DE CAMPOS DE TEXTO ============= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['upload'])) {
    $zona           = $_POST['zona_trabajo']   ?? '';
    $sobre_mi       = $_POST['descripcion']    ?? '';
    $habilidades    = $_POST['profesion']      ?? '';
    $disponibilidad = $_POST['disponibilidad'] ?? '';
    $precio         = $_POST['precio_hora']    ?? null;

    $stmt = $conn->prepare("UPDATE empleado SET zona_trabajo=?, descripcion_servicios=?, profesion=?, disponibilidad=?, precio_hora=? WHERE id_empleado=?");
    $stmt->bind_param("ssssdi", $zona, $sobre_mi, $habilidades, $disponibilidad, $precio, $id);
    $stmt->execute();
    $stmt->close();
}

/* ============= LECTURA PARA RENDER ============= */
$sql = "SELECT * FROM empleado WHERE id_empleado = $id";
$res = $conn->query($sql);
if (!$res || $res->num_rows !== 1) { echo "Empleado no encontrado."; exit(); }
$empleado = $res->fetch_assoc();

$foto_nombre   = $empleado['foto_perfil'] ?? '';
$foto_url_rel  = '../../uploads/Foto_Perfil/' . ($foto_nombre ?: 'default.jpg');
$foto_url_rel_cb = $foto_url_rel . (!empty($foto_nombre) ? '?v=' . time() : '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil del Trabajador</title>
  <link rel="stylesheet" href="../../recursos/css/profile.css">
  <style>
    .flash { margin: 10px 0; padding: 10px; border-radius: 8px; }
    .flash.ok { background:#e6ffed; color:#0a5; border:1px solid #6c9; }
    .flash.err { background:#ffecec; color:#a00; border:1px solid #e99; }
    .foto-perfil { width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #005F8C; }
    .help {font-size:12px;color:#666;margin-top:6px}
  </style>
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
      <img class="foto-perfil"
           src="<?php echo htmlspecialchars($foto_url_rel_cb); ?>"
           alt="Foto de perfil"
           onerror="this.onerror=null;this.src='../../uploads/Foto_Perfil/default.jpg';">
      <div>
        <h1><?php echo htmlspecialchars($empleado['nombre']); ?></h1>
        <p><?php echo htmlspecialchars($empleado['profesion']); ?></p>
      </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
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

      <!-- Foto de perfil (AJAX a este mismo archivo) -->
      <div class="perfil-section">
        <h2>Foto de perfil</h2>
        <div class="static">
          <p>Podés actualizar tu foto cuando quieras.</p>
        </div>
        <div class="editable" style="display:none">
          <input id="input-foto-perfil" type="file" name="foto" accept="image/jpeg,image/png">
          <p class="help">Formatos: JPG/PNG. Máx: 3MB. Se convertirá a JPG automáticamente.</p>
          <div id="foto-msg" class="help"></div>
        </div>
      </div>

      <button type="button" id="boton-editar" class="boton-editar" onclick="toggleEdit()">Editar información</button>
      <button type="submit" id="boton-aplicar" class="boton-aplicar" style="display:none;">Aplicar cambios</button>
    </form>
  </div>

  <script>
    (function(){
      const input = document.getElementById('input-foto-perfil');
      const msg   = document.getElementById('foto-msg');
      const imgEl = document.querySelector('.foto-perfil');

      if (!input) return;

      input.addEventListener('change', async (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        msg.style.color = '#555';
        msg.textContent = 'Subiendo...';

        const fd = new FormData();
        fd.append('foto', file);

        try {
          const res  = await fetch('?upload=1', { method: 'POST', body: fd });
          const data = await res.json();

          if (!res.ok || !data.ok) {
            throw new Error(data && data.error ? data.error : 'Error al subir');
          }

          // Refrescar imagen (cache-busting)
          imgEl.src = data.foto_url + '?v=' + Date.now();
          msg.style.color = '#0a5';
          msg.textContent = '¡Foto actualizada: ' + data.foto + '!';
        } catch (err) {
          msg.style.color = '#a00';
          msg.textContent = 'Error: ' + (err.message || err);
        }
      });
    })();
  </script>
</body>
</html>
