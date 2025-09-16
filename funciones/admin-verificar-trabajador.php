<?php
// labora_db/funciones/admin-verificar-trabajador.php
session_start();
if (empty($_SESSION['admin'])) { header("Location: ../vistas/admin/admin-login.php"); exit(); }

require_once __DIR__ . '/../config/conexion.php';
if (function_exists('mysqli_set_charset')) { @mysqli_set_charset($conn, 'utf8mb4'); }

// === Email (PHPMailer) ===
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

// Base URL del proyecto (ej: /labora_db)
function base_url_root(): string {
  $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/')); // ej: labora_db/funciones/admin-verificar-trabajador.php
  return '/' . ($parts[0] ?? '');
}
$BASE_URL = base_url_root(); // -> /labora_db

function enviarDecision($correo, $nombre, $accion, $obs) {
  if (empty($correo)) return;

  $mail = new PHPMailer(true);
  try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'labora1357@gmail.com';
      $mail->Password = 'fqkp sppu bmgv ynmb'; // App Password
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      $mail->setFrom('labora1357@gmail.com', 'Labora');
      $mail->addAddress($correo, $nombre ?: $correo);
      $mail->isHTML(true);

      if ($accion === 'aprobar') {
        $mail->Subject = 'Tu verificación en LABORA fue APROBADA';
        $mail->Body = "
          <div style='font-family:Arial,sans-serif;line-height:1.5;color:#111'>
            <h2 style='color:#16a34a;margin:0 0 8px'>¡Listo, ".htmlspecialchars($nombre,ENT_QUOTES,'UTF-8')."!</h2>
            <p>Tu verificación fue <strong>aprobada</strong>. Ya podés aparecer en las búsquedas y difundir tus servicios en <strong>LABORA</strong>.</p>
            <p style='font-size:13px;color:#555'>Si necesitás actualizar tus datos o documentación, podés hacerlo desde tu perfil.</p>
            <hr style='border:none;border-top:1px solid #eee;margin:16px 0'>
            <p style='font-size:12px;color:#777'>Este es un mensaje automático. Si no fuiste vos, escribinos a <a href='mailto:labora1357@gmail.com'>labora1357@gmail.com</a>.</p>
          </div>
        ";
      } else { // rechazar
        $mail->Subject = 'Tu verificación en LABORA fue RECHAZADA';
        $obsHtml = nl2br(htmlspecialchars($obs, ENT_QUOTES, 'UTF-8'));
        $mail->Body = "
          <div style='font-family:Arial,sans-serif;line-height:1.5;color:#111'>
            <h2 style='color:#b91c1c;margin:0 0 8px'>Hola, ".htmlspecialchars($nombre,ENT_QUOTES,'UTF-8')."</h2>
            <p>Por el momento tu verificación fue <strong>rechazada</strong>.</p>
            <p><strong>Motivo:</strong></p>
            <blockquote style='margin:10px 0;padding:10px;border-left:3px solid #f59e0b;background:#fff8e1'>$obsHtml</blockquote>
            <p>Podés corregir o volver a subir la documentación solicitada y solicitar una nueva revisión.</p>
            <hr style='border:none;border-top:1px solid #eee;margin:16px 0'>
            <p style='font-size:12px;color:#777'>Dudas o consultas: <a href='mailto:labora1357@gmail.com'>labora1357@gmail.com</a>.</p>
          </div>
        ";
      }

      $mail->send();
  } catch (Exception $e) {
      error_log('[ADMIN MAIL ERROR] ' . $mail->ErrorInfo);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id     = (int)($_POST['id_empleado'] ?? 0);
  $accion = $_POST['accion'] ?? ''; // aprobar | rechazar | pendiente
  $obs    = trim($_POST['observaciones'] ?? '');

  // NUEVO: campos desde el admin
  $profesion          = trim($_POST['profesion'] ?? '');
  $titulo_profesional = trim($_POST['titulo_profesional'] ?? '');

  if ($id <= 0 || !in_array($accion, ['aprobar','rechazar','pendiente'], true)) {
    header("Location: {$BASE_URL}/vistas/admin/admin-panel.php#trabajadores");
    exit();
  }

  // === OBLIGATORIO MOTIVO AL RECHAZAR (server-side) ===
  if ($accion === 'rechazar' && $obs === '') {
    header("Location: {$BASE_URL}/funciones/admin-trabajador.php?id={$id}&err=obs_required");
    exit();
  }

  // === SI APRUEBA, PROFESION OBLIGATORIA (puede venir ya cargada, pero exigimos si está vacía en el POST) ===
  if ($accion === 'aprobar' && $profesion === '') {
    header("Location: {$BASE_URL}/funciones/admin-trabajador.php?id={$id}&err=prof_required");
    exit();
  }

  // Datos del empleado para email
  $stmtInfo = $conn->prepare("SELECT nombre, correo FROM empleado WHERE id_empleado = ?");
  $stmtInfo->bind_param("i", $id);
  $stmtInfo->execute();
  $resInfo = $stmtInfo->get_result();
  $emp = $resInfo ? $resInfo->fetch_assoc() : null;
  $stmtInfo->close();

  $nuevo = $accion === 'aprobar' ? 'aprobado' : ($accion === 'rechazar' ? 'rechazado' : 'pendiente');

  // === UPDATE con guardado de profesión y título profesional ===
  $sql = "
      UPDATE empleado
         SET estado_verificacion      = ?,
             verificado_por           = ?,
             fecha_verificacion       = NOW(),
             observaciones_verificacion = ?,
             profesion                = COALESCE(NULLIF(?, ''), profesion),
             titulo_profesional       = COALESCE(NULLIF(?, ''), titulo_profesional)
       WHERE id_empleado = ?
  ";
  $stmt = $conn->prepare($sql);
  $admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
  $stmt->bind_param("sisssi", $nuevo, $admin_id, $obs, $profesion, $titulo_profesional, $id);
  $stmt->execute();
  $stmt->close();

  // Enviar email de notificación (best-effort)
  if ($emp) {
    enviarDecision($emp['correo'] ?? '', $emp['nombre'] ?? '', $accion, $obs);
  }
}

// Redirigir al panel correcto en vistas/admin
header("Location: {$BASE_URL}/vistas/admin/admin-panel.php#trabajadores");
exit();
