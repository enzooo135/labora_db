<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

$correoDestino = $_POST['correo'];
$token = bin2hex(random_bytes(32));
$enlace = "http://localhost/labora_db/funciones/user-verify.php?token=$token"; // ajustalo a tu proyecto

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'labora1357@gmail.com';        // correo real
    $mail->Password   = 'fqkp sppu bmgv ynmb';       // clave de aplicación
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('tucorreo@gmail.com', 'Labora');
    $mail->addAddress($correoDestino);
    $mail->isHTML(true);
    $mail->Subject = 'Verifica tu cuenta';
    $mail->Body    = "Hola,<br>Haz clic en este enlace para verificar tu correo:<br><a href='$enlace'>Verificar cuenta</a>";

    $mail->send();
    echo "Correo enviado con éxito a $correoDestino";
} catch (Exception $e) {
    echo "Error al enviar correo: {$mail->ErrorInfo}";
}
