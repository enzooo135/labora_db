<?php
require '../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

function enviarPendiente($correo, $nombre) {
    if (empty($correo)) return;
    $m = new PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host = 'smtp.gmail.com';
        $m->SMTPAuth = true;
        $m->Username = 'labora1357@gmail.com';
        $m->Password = 'fqkp sppu bmgv ynmb';
        $m->SMTPSecure = 'tls';
        $m->Port = 587;

        $m->setFrom('labora1357@gmail.com', 'Labora');
        $m->addAddress($correo, $nombre ?: $correo);
        $m->isHTML(true);
        $m->Subject = ' ¡Tu verificación en LABORA está en proceso!';
        $m->Body = "
        <div style='font-family:Arial,sans-serif;line-height:1.6;color:#333;padding:20px;background:#f9f9f9;border-radius:8px;'>
          <div style='text-align:center;margin-bottom:20px;'>
            <img src='https://i.imgur.com/7rI0XwP.png' alt='Labora' style='max-width:120px;'>
          </div>
          <h2 style='color:#005f8c;'>Hola, ".htmlspecialchars($nombre,ENT_QUOTES,'UTF-8')." 👋</h2>
          <p style='font-size:15px;'>
            ¡Gracias por unirte a <b>LABORA</b>! 🙌<br>
            Hemos recibido tu documentación y tu cuenta se encuentra ahora en <b style='color:#d98324;'>REVISIÓN</b>.
          </p>
          <p style='font-size:15px;'>
            Nuestro equipo de administración evaluará tu perfil y te notificaremos por este medio 
            apenas esté <b>verificado</b>. ⏳
          </p>
          <p style='font-size:14px;color:#555;margin-top:20px;'>
            Mientras tanto, te invitamos a seguir explorando LABORA y conocer todas las oportunidades que tenemos para vos.
          </p>
          <div style='margin-top:30px;text-align:center;'>
            <a href='https://tusitio.com' style='background:#005f8c;color:#fff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;'>
              Ir a LABORA
            </a>
          </div>
          <hr style='margin:30px 0;border:none;border-top:1px solid #ddd;'>
          <p style='font-size:12px;color:#999;text-align:center;'>
            Este es un correo automático, por favor no respondas a este mensaje.
          </p>
        </div>
        ";
        $m->send();
    } catch (Exception $e) {
        error_log('[USER VERIFY MAIL ERROR] '.$m->ErrorInfo);
    }
}

if (empty($_GET['token'])) { echo "Token no proporcionado."; exit(); }
$token = $_GET['token'];

// Traer pendiente
$stmt = $conn->prepare("
  SELECT id_usuario, nombre, dni, fecha_nacimiento, correo, clave, telefono, direccion, localidad,
         dni_frente_tmp, dni_dorso_tmp, matricula_tmp
  FROM registro_pendiente_usuarios
  WHERE token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) { echo "Token inválido o ya verificado."; exit(); }
$pend = $res->fetch_assoc();

// Insertar en usuarios con estado pendiente
$ins = $conn->prepare("
  INSERT INTO usuarios (nombre, dni, fecha_nacimiento, correo, clave, telefono, direccion, localidad,
                        estado_verificacion, dni_frente_path, dni_dorso_path, matricula_path)
  VALUES (?,?,?,?,?,?,?,?, 'pendiente', NULL, NULL, NULL)
");
$ins->bind_param(
  "ssssssss",
  $pend['nombre'], $pend['dni'], $pend['fecha_nacimiento'], $pend['correo'],
  $pend['clave'], $pend['telefono'], $pend['direccion'], $pend['localidad']
);
if (!$ins->execute()) { echo "Error al crear cuenta de usuario: " . $ins->error; exit(); }
$nuevo_id = $ins->insert_id;

// Mover archivos a carpeta final
$projectRoot = realpath(__DIR__ . '/..'); if ($projectRoot === false) { $projectRoot = dirname(__DIR__); }
$srcRel = "uploads/verificaciones/pre_usuario_{$pend['id_usuario']}";
$srcAbs = $projectRoot . DIRECTORY_SEPARATOR . $srcRel;

$dstRel = "uploads/verificaciones/usuario_{$nuevo_id}";
$dstAbs = $projectRoot . DIRECTORY_SEPARATOR . $dstRel;
if (!is_dir($dstAbs)) { @mkdir($dstAbs, 0775, true); }

$moveOne = function($relTmp) use ($srcAbs, $dstAbs) {
    if (!$relTmp) return null;
    $base = basename($relTmp);
    $src  = $srcAbs . DIRECTORY_SEPARATOR . $base;
    if (!is_file($src)) return null;
    $dst  = $dstAbs . DIRECTORY_SEPARATOR . $base;
    if (@rename($src, $dst)) { return $base; }
    return null;
};
$dni_frente_new = $moveOne($pend['dni_frente_tmp']);
$dni_dorso_new  = $moveOne($pend['dni_dorso_tmp']);
$matricula_new  = $moveOne($pend['matricula_tmp']);

$dni_frente_path = $dni_frente_new ? ($dstRel . '/' . $dni_frente_new) : null;
$dni_dorso_path  = $dni_dorso_new  ? ($dstRel . '/' . $dni_dorso_new)  : null;
$matricula_path  = $matricula_new  ? ($dstRel . '/' . $matricula_new)  : null;

// Guardar paths
$upd = $conn->prepare("UPDATE usuarios SET dni_frente_path=?, dni_dorso_path=?, matricula_path=? WHERE id_usuario=?");
$upd->bind_param("sssi", $dni_frente_path, $dni_dorso_path, $matricula_path, $nuevo_id);
$upd->execute();

// Borrar pendiente y carpeta temporal si quedó vacía
$del = $conn->prepare("DELETE FROM registro_pendiente_usuarios WHERE token = ?");
$del->bind_param("s", $token); $del->execute();

if (is_dir($srcAbs)) { @rmdir($srcAbs); }

enviarPendiente($pend['correo'], $pend['nombre']);
header("Location: ../mensajes/revision.html");
exit();
