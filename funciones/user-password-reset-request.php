<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require __DIR__ . '/../config/conexion.php'; // $conn (mysqli)

mysqli_set_charset($conn, 'utf8mb4');

// --- Input ---
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  header('Location: /labora_db/mensajes/revisar-restauración-contraseña.html');
  exit();
}

// --- Buscar usuario ---
$sql = "SELECT id_usuario, nombre FROM usuarios WHERE correo = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

// Siempre respondemos igual para no filtrar existencia
if (!$user) {
  header('Location: /labora_db/mensajes/revisar-restauración-contraseña.html');
  exit();
}

// --- Generar token y guardar HASH + expiración en usuarios ---
$rawToken   = bin2hex(random_bytes(32));    // 64 hex
$tokenHash  = hash('sha256', $rawToken);
$expiresAt  = (new DateTime('+60 minutes'))->format('Y-m-d H:i:s');

$upd = $conn->prepare("UPDATE usuarios SET reset_token_hash = ?, reset_expires = ? WHERE id_usuario = ?");
$upd->bind_param("ssi", $tokenHash, $expiresAt, $user['id_usuario']);
$upd->execute();
$upd->close();

// --- Armar enlace ---
$resetLink = "http://localhost/labora_db/funciones/user-password-reset-verify.php?token=" . urlencode($rawToken);

// --- Enviar email (MISMA CONFIG QUE worker-register.php) ---
$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'labora1357@gmail.com';
  $mail->Password = 'fqkp sppu bmgv ynmb'; // clave de aplicación
  $mail->SMTPSecure = 'tls';
  $mail->Port = 587;

  $mail->setFrom('labora1357@gmail.com', 'Labora');
  $mail->addAddress($correo);
  $mail->isHTML(true);
  $mail->Subject = 'Restablece tu contrasena Labora';

  $nombre = $user['nombre'] ?: 'Usuario';
  $enlace = $resetLink;

  $mail->Body = "
  <!DOCTYPE html>
  <html lang='es'>
  <head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Restablecer contraseña</title></head>
  <body style='margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
    <table align='center' width='100%' cellpadding='0' cellspacing='0' style='padding: 20px 0;'>
      <tr><td align='center'>
        <table width='600' cellpadding='0' cellspacing='0' style='background-color:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 0 10px rgba(0,0,0,0.1);'>
          <tr><td style='padding:40px 30px;'>
            <h2 style='color:#333'>¡Hola " . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . "!</h2>
            <p style='font-size:16px;color:#555'>Recibimos tu solicitud para restablecer la contraseña.</p>
            <p style='font-size:16px;color:#555'>Hacé clic en el botón para continuar (vigente por 60 minutos):</p>
            <div style='text-align:center;margin:30px 0;'>
              <a href='" . htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8') . "' style='background-color:#00B4D8;color:white;padding:15px 25px;text-decoration:none;border-radius:5px;font-size:16px;'>
                Restablecer contraseña
              </a>
            </div>
            <p style='font-size:14px;color:#999'>Si no solicitaste esto, podés ignorar este correo.</p>
          </td></tr>
          <tr><td style='background-color:#f4f4f4;padding:20px;text-align:center;font-size:12px;color:#999'>
            © 2025 LABORA | <a href='mailto:labora1357@gmail.com' style='color:#00B4D8;'>labora1357@gmail.com</a>
          </td></tr>
        </table>
      </td></tr>
    </table>
  </body>
  </html>";

  $mail->AltBody = "Usá este enlace (60 min): $resetLink";

  $mail->send();
} catch (Exception $e) {
  // En local podés mostrar el link para seguir probando sin mail real:
  if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost') {
    echo "<p><strong>[DEV]</strong> No se pudo enviar el mail, pero acá tenés el enlace para probar:</p>";
    echo "<p><a href='".htmlspecialchars($resetLink, ENT_QUOTES)."'>".$resetLink."</a></p>";
    exit;
  }
}

// Redirigimos igual para no filtrar existencia
header('Location: /labora_db/mensajes/revisar-restauración-contraseña.html');
exit();
