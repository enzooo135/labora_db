<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../config/conexion.php';

$correo = $_POST['email']; //Obtenemos el correo primero

// Primero: Verificamos si ya existe el correo en empleado
$sql_check1 = "SELECT id_empleado FROM empleado WHERE correo = '$correo'";
$result1 = $conn->query($sql_check1);

// Segundo: Verificamos si ya está en registro_pendiente_empleados
$sql_check2 = "SELECT id_empleado FROM registro_pendiente_empleados WHERE correo = '$correo'";
$result2 = $conn->query($sql_check2);

if ($result1->num_rows > 0 || $result2->num_rows > 0) {
    echo "Este correo ya está registrado o pendiente de verificación.";
    exit();
}

// 1. Obtenemos datos del formulario

$nombre = $_POST['nombre'];
$correo = $_POST['email'];
$clave = $_POST['clave'];
$dni = $_POST['dni'];
$fecha_nacimiento = $_POST['fecha-nacimiento'];
$nacionalidad = $_POST['nacionalidad'];
$telefono = $_POST['telefono'];
$zona_trabajo = $_POST['zona_trabajo'];
$experiencia = $_POST['experiencia'];
$trabajos = isset($_POST['trabajos']) && is_array($_POST['trabajos']) ? $_POST['trabajos'] : [];
$profesion = implode(",", $trabajos);

$clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);

//2. Generamos el token unico

$token = bin2hex(random_bytes(32));

//3. Guardamos en la tabla temporal

$sql = "INSERT INTO registro_pendiente_empleados (nombre, correo, clave, profesion, dni, fecha_nacimiento, nacionalidad, telefono, zona_trabajo,experiencia_años, token) VALUES ('$nombre', '$correo', '$clave', '$profesion', '$dni', '$fecha_nacimiento', '$nacionalidad', '$telefono', '$zona_trabajo', '$experiencia', '$token')";

if ($conn->query($sql) === TRUE) {
    // 4. Enviar email con PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'labora1357@gmail.com'; // correo real de nuestra empresa
        $mail->Password = 'fqkp sppu bmgv ynmb'; // tu clave de aplicación
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('labora1357@gmail.com', 'Labora');
        $mail->addAddress($correo);
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta en LABORA';
        $enlace = "http://localhost/labora_db/funciones/worker-verify.php?token=$token";
        //Este es el mail que se va a mandar a las personas al momento de pedir verificacion
        $mail->Body = " 
                        <!DOCTYPE html>
                        <html lang='es'>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <title>Verificación de Cuenta</title>
                        </head>
                        <body style='margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
                            <table align='center' width='100%' cellpadding='0' cellspacing='0' style='padding: 20px 0;'>
                                <tr>
                                    <td align='center'>
                                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                                            <!-- Cabecera con el logo -->
                                            <tr>
                                                <td align='center' style='padding: 30px 0; background-color: #4CAF50;'>
                                                    <img src='imagenes/logo-labora' alt='LABORA' style='max-width: 150px;'>
                                                </td>
                                            </tr>

                                            <!-- Contenido principal -->
                                            <tr>
                                                <td style='padding: 40px 30px;'>
                                                    <h2 style='color: #333333;'>¡Hola $nombre!</h2>
                                                    <p style='font-size: 16px; color: #555555;'>
                                                        Gracias por registrarte en <strong>LABORA</strong>. Solo falta un paso más para activar tu cuenta.
                                                    </p>
                                                    <p style='font-size: 16px; color: #555555;'>
                                                        Por favor, hacé clic en el siguiente botón para verificar tu cuenta:
                                                    </p>
                                                    <div style='text-align: center; margin: 30px 0;'>
                                                        <a href='$enlace' style='background-color: #4CAF50; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px;'>
                                                            Verificar mi cuenta
                                                        </a>
                                                    </div>
                                                    <p style='font-size: 14px; color: #999999;'>
                                                        Si no creaste una cuenta en LABORA, podés ignorar este correo.
                                                    </p>
                                                </td>
                                            </tr>

                                            <!-- Footer -->
                                            <tr>
                                                <td style='background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #999999;'>
                                                    © 2025 LABORA | Todos los derechos reservados.<br>
                                                    Si tenés alguna consulta, escribinos a <a href='mailto:labora1357@gmail.com' style='color: #4CAF50;'>labora1357@gmail.com</a>.
                                                </td>
                                            </tr>

                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>
                        ";

        $mail->send();
        echo "Registro exitoso. Revisá tu correo para confirmar tu cuenta.";
    } catch (Exception $e) {
        echo "No se pudo enviar el correo de verificación: {$mail->ErrorInfo}";
    }
} else {
    echo "Error al registrar Empleado: " . $conn->error;
}


$conn->close();
?>