    <?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../PHPMailer-master/src/Exception.php';
    require '../PHPMailer-master/src/PHPMailer.php';
    require '../PHPMailer-master/src/SMTP.php';
    require '../config/conexion.php';

    $correo = $_POST['email']; //Obtenemos el correo primero

    // Primero: Verificamos si ya existe el correo en usuarios
    $sql_check1 = "SELECT id_usuario FROM usuarios WHERE correo = '$correo'";
    $result1 = $conn->query($sql_check1);

    // Segundo: Verificamos si ya está en registro_pendiente_usuarios
    $sql_check2 = "SELECT id_usuario FROM registro_pendiente_usuarios WHERE correo = '$correo'";
    $result2 = $conn->query($sql_check2);

    if ($result1->num_rows > 0 || $result2->num_rows > 0) {
        header("Location: ../mensajes/existente.html");
        exit();
    }

    // 1. Obtener datos del formulario
    $nombre = $_POST['nombre'];
    $dni = $_POST['dni'];
    $fecha_nacimiento = $_POST['fecha-nacimiento'];
    $correo = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    $localidad = $_POST['localidad'];

    // 2. Generar token
    $token = bin2hex(random_bytes(32));

    error_log("DEBUG Localidad recibida: " . ($_POST['localidad'] ?? 'VACÍA'));

    // 3. Guardar en tabla temporal
    $sql = "INSERT INTO registro_pendiente_usuarios (nombre, dni, fecha_nacimiento, correo, clave, telefono, direccion, token, localidad)
            VALUES ('$nombre', '$dni', '$fecha_nacimiento', '$correo', '$clave', '$telefono', '$direccion', '$token', '$localidad')";

    if ($conn->query($sql) === TRUE) {
        // 4. Enviar email con PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'labora1357@gmail.com'; // tu correo real
            $mail->Password = 'fqkp sppu bmgv ynmb'; // clave de aplicación
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('labora1357@gmail.com', 'Labora');
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->Subject = 'Verifica tu cuenta en LABORA';
            $enlace = "http://localhost/labora_db/funciones/user-verify.php?token=$token";
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
                                                            <a href='$enlace' style='background-color: #00B4D8; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px;'>
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
                                                        Si tenés alguna consulta, escribinos a <a href='mailto:labora1357@gmail.com' style='color: #00B4D8;'>labora1357@gmail.com</a>.
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
            header("Location: ../mensajes/revisar-mail.html");
        } catch (Exception $e) {
            echo "No se pudo enviar el correo de verificación: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error al registrar usuario: " . $conn->error;
    }

    $conn->close();
    ?>
