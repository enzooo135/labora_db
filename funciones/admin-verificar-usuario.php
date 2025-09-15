<?php
// labora_db/funciones/admin-verificar-usuario.php
session_start();
if (empty($_SESSION['admin'])) { header("Location: /labora_db/vistas/admin/admin-login.php"); exit(); }
require_once __DIR__ . '/../config/conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

function enviarDecisionUsuario($correo, $nombre, $accion, $obs) {
  if (empty($correo)) return;
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'labora1357@gmail.com';
    $mail->Password = 'fqkp sppu bmgv ynmb';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('labora1357@gmail.com', 'Labora');
    $mail->addAddress($correo, $nombre ?: $correo);
    $mail->isHTML(true);

    if ($accion === 'aprobar') {
    $mail->Subject = '✅ ¡Tu verificacion en LABORA fue APROBADA!';
    $mail->Body = "
      <div style='font-family:Arial,sans-serif;line-height:1.6;color:#333;padding:20px;background:#f9f9f9;border-radius:8px;'>
        <div style='text-align:center;margin-bottom:20px;'>
          <img src='https://i.imgur.com/7rI0XwP.png' alt='Labora' style='max-width:120px;'>
        </div>
        <h2 style='color:#005f8c;'>¡Felicitaciones, ".htmlspecialchars($nombre,ENT_QUOTES,'UTF-8')."! 🎉</h2>
        <p style='font-size:15px;'>
          Tu verificación fue <b style='color:#28a745;'>APROBADA</b> ✅<br>
          Ahora ya podés acceder a todas las funciones de <b>LABORA</b> y conectar con más oportunidades.
        </p>
        <div style='margin-top:30px;text-align:center;'>
          <a href='https://labora.com' style='background:#005f8c;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;'>
            Ir a LABORA
          </a>
        </div>
        <hr style='margin:30px 0;border:none;border-top:1px solid #ddd;'>
        <p style='font-size:12px;color:#999;text-align:center;'>
          Este es un correo automático, por favor no respondas a este mensaje.
        </p>
      </div>
    ";
} else {
    $mail->Subject = '❌ Tu verificacion en LABORA fue RECHAZADA';
    $mail->Body = "
      <div style='font-family:Arial,sans-serif;line-height:1.6;color:#333;padding:20px;background:#f9f9f9;border-radius:8px;'>
        <div style='text-align:center;margin-bottom:20px;'>
          <img src='https://i.imgur.com/7rI0XwP.png' alt='Labora' style='max-width:120px;'>
        </div>
        <h2 style='color:#d98324;'>Hola, ".htmlspecialchars($nombre,ENT_QUOTES,'UTF-8')." 👋</h2>
        <p style='font-size:15px;'>
          Lamentamos informarte que tu verificación fue <b style='color:#dc3545;'>RECHAZADA</b>. ❌
        </p>
        <p style='font-size:15px;'>
          <b>Motivo:</b><br>
          ".nl2br(htmlspecialchars($obs,ENT_QUOTES,'UTF-8'))."
        </p>
        <p style='font-size:14px;color:#555;margin-top:20px;'>
          Te invitamos a revisar la información enviada y volver a cargar tu documentación para una nueva evaluación.
        </p>
        <div style='margin-top:30px;text-align:center;'>
          <a href='https://labora.com' style='background:#d98324;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;'>
            Reintentar verificación
          </a>
        </div>
        <hr style='margin:30px 0;border:none;border-top:1px solid #ddd;'>
        <p style='font-size:12px;color:#999;text-align:center;'>
          Este es un correo automático, por favor no respondas a este mensaje.
        </p>
      </div>
    ";
}
    $mail->send();
  } catch (Exception $e) {
    error_log('[ADMIN USER MAIL ERROR] '.$mail->ErrorInfo);
  }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: /labora_db/vistas/admin/admin-panel.php#usuarios-pendientes"); exit(); }

$id  = (int)($_POST['id_usuario'] ?? 0);
$acc = $_POST['accion'] ?? '';
$obs = trim($_POST['observaciones'] ?? '');
if ($id <= 0 || !in_array($acc, ['aprobar','rechazar','pendiente'], true)) {
  header("Location: /labora_db/vistas/admin/admin-panel.php#usuarios-pendientes"); exit();
}
if ($acc === 'rechazar' && $obs === '') {
  header("Location: /labora_db/funciones/admin-usuario.php?id={$id}&err=obs_required"); exit();
}

$stmtInfo = $conn->prepare("SELECT nombre, correo FROM usuarios WHERE id_usuario = ?");
$stmtInfo->bind_param("i", $id);
$stmtInfo->execute();
$resInfo = $stmtInfo->get_result();
$u = $resInfo ? $resInfo->fetch_assoc() : null;
$stmtInfo->close();

$nuevo = $acc === 'aprobar' ? 'aprobado' : ($acc === 'rechazar' ? 'rechazado' : 'pendiente');
$stmt = $conn->prepare("
  UPDATE usuarios
     SET estado_verificacion = ?, verificado_por = ?, fecha_verificacion = NOW(), observaciones_verificacion = ?
   WHERE id_usuario = ?
");
$admin_id = (int)$_SESSION['admin_id'];
$stmt->bind_param("sisi", $nuevo, $admin_id, $obs, $id);
$stmt->execute();

if ($u) { enviarDecisionUsuario($u['correo'] ?? '', $u['nombre'] ?? '', $acc, $obs); }

header("Location: /labora_db/vistas/admin/admin-panel.php#usuarios-pendientes");
exit();
