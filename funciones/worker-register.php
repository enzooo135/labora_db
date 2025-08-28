<?php
// worker-register.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../config/conexion.php'; // mysqli en $conn

mysqli_set_charset($conn, 'utf8mb4');

// --- Método ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// --- Email mínimo ---
$correo = isset($_POST['email']) ? trim($_POST['email']) : '';
if ($correo === '') {
    exit('Correo es obligatorio');
}

error_log('[FORM DEBUG] POST=' . print_r($_POST, true));

// --- Chequeo de existencia ---
$existe = false;

$sql_check1 = "SELECT id_empleado FROM empleado WHERE correo = ?";
$stmt1 = $conn->prepare($sql_check1);
$stmt1->bind_param("s", $correo);
$stmt1->execute();
$stmt1->store_result();
if ($stmt1->num_rows > 0) $existe = true;
$stmt1->close();

if (!$existe) {
    $sql_check2 = "SELECT id_empleado FROM registro_pendiente_empleados WHERE correo = ?";
    $stmt2 = $conn->prepare($sql_check2);
    $stmt2->bind_param("s", $correo);
    $stmt2->execute();
    $stmt2->store_result();
    if ($stmt2->num_rows > 0) $existe = true;
    $stmt2->close();
}

if ($existe) {
    header("Location: ../mensajes/existente.html");
    exit();
}

// --- Datos ---
$nombre            = trim($_POST['nombre'] ?? '');
$dni               = trim($_POST['dni'] ?? '');
$fecha_nacimiento  = $_POST['fecha-nacimiento'] ?? '';
$nacionalidad      = trim($_POST['nacionalidad'] ?? '');
$telefono          = trim($_POST['telefono'] ?? '');
$zona_trabajo      = trim($_POST['zona_trabajo'] ?? '');
$experiencia       = trim($_POST['experiencia'] ?? '0');
$clave_plain       = $_POST['clave'] ?? '';
$confirm_plain     = $_POST['confirm-password'] ?? '';

// Whitelists
$NACIONALIDADES_PERMITIDAS = ['Argentina', 'Chilena', 'Brasilera'];
$ZONAS_PERMITIDAS = ['Moreno', 'Paso del rey', 'Merlo', 'Padua', 'Ituzaingo'];

// Profesión (única o múltiple)
$profesion = '';
if (!empty($_POST['trabajo'])) {
    $profesion = $_POST['trabajo']; // única
} elseif (isset($_POST['trabajos'])) {
    $tmp = $_POST['trabajos'];
    $profesion = is_array($tmp) ? implode(',', $tmp) : (string)$tmp;
}

// --- Validaciones servidor ---
$errores = [];

// Nombre
if ($nombre === '') $errores[] = 'El nombre es obligatorio.';

// DNI 7 u 8 dígitos
if (!preg_match('/^\d{7,8}$/', $dni)) $errores[] = 'DNI inválido (7 u 8 dígitos).';

// Fecha nacimiento y >= 18
if ($fecha_nacimiento === '') {
    $errores[] = 'La fecha de nacimiento es obligatoria.';
} else {
    $fn = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    $fn_errors = DateTime::getLastErrors();
    $hasErrors = ($fn_errors !== false) && (
        (!empty($fn_errors['warning_count']) && $fn_errors['warning_count'] > 0) ||
        (!empty($fn_errors['error_count']) && $fn_errors['error_count'] > 0)
    );
    if (!$fn || $hasErrors) {
        $errores[] = 'Fecha de nacimiento inválida (formato Y-m-d).';
    } else {
        $hoy = new DateTime('today');
        $edad = $fn->diff($hoy)->y;
        if ($edad < 18) $errores[] = 'Debés ser mayor de 18 años.';
    }
}


// Nacionalidad whitelist
if (!in_array($nacionalidad, $NACIONALIDADES_PERMITIDAS, true)) {
    $errores[] = 'Nacionalidad inválida.';
}

// Teléfono: normalizar y validar 10 dígitos
$tel_digits = preg_replace('/\D/', '', $telefono);
if (strlen($tel_digits) !== 10) {
    $errores[] = 'El teléfono debe tener exactamente 10 dígitos.';
} else {
    $telefono = $tel_digits; // guardo normalizado
}

// Zona whitelist
if (!in_array($zona_trabajo, $ZONAS_PERMITIDAS, true)) {
    $errores[] = 'Zona de trabajo inválida.';
}

// Experiencia >= 0
if (!is_numeric($experiencia) || (int)$experiencia < 0) {
    $errores[] = 'La experiencia debe ser un número mayor o igual a 0.';
}

// Profesión
if ($profesion === '') $errores[] = 'Debés seleccionar una profesión.';

// Contraseña
if ($clave_plain === '' || $confirm_plain === '' || $clave_plain !== $confirm_plain) {
    $errores[] = 'Las contraseñas no coinciden o están vacías.';
}

if (!empty($errores)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "No se pudo registrar por:\n- " . implode("\n- ", $errores);
    exit();
}

// Hash de clave
$clave_hash = password_hash($clave_plain, PASSWORD_DEFAULT);

// --- Token y guardado ---
$token = bin2hex(random_bytes(32));

$sql_insert = "INSERT INTO registro_pendiente_empleados 
    (nombre, correo, clave, profesion, dni, fecha_nacimiento, nacionalidad, telefono, zona_trabajo, `experiencia_años`, token)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql_insert);
if (!$stmt) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error al preparar INSERT: " . $conn->error;
    exit();
}

$experiencia_int = (int)$experiencia;
$stmt->bind_param(
    "sssssssssis",
    $nombre,
    $correo,
    $clave_hash,
    $profesion,
    $dni,
    $fecha_nacimiento,
    $nacionalidad,
    $telefono,
    $zona_trabajo,
    $experiencia_int,
    $token
);

if ($stmt->execute()) {
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
        $mail->addAddress($correo);
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta en LABORA';

        $enlace = "http://localhost/labora_db/funciones/worker-verify.php?token=$token";

        $mail->Body =  "
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
                                <tr>
                                    <td style='padding: 40px 30px;'>
                                        <h2 style='color: #333333;'>¡Hola ".htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8')."!</h2>
                                        <p style='font-size: 16px; color: #555555;'>
                                            Gracias por registrarte en <strong>LABORA</strong>. Solo falta un paso más para activar tu cuenta.
                                        </p>
                                        <p style='font-size: 16px; color: #555555;'>
                                            Por favor, hacé clic en el siguiente botón para verificar tu cuenta:
                                        </p>
                                        <div style='text-align: center; margin: 30px 0;'>
                                            <a href='".htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8')."' style='background-color: #00B4D8; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px;'>
                                                Verificar mi cuenta
                                            </a>
                                        </div>
                                        <p style='font-size: 14px; color: #999999;'>
                                            Si no creaste una cuenta en LABORA, podés ignorar este correo.
                                        </p>
                                    </td>
                                </tr>
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
        exit();
    } catch (Exception $e) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "No se pudo enviar el correo de verificación: {$mail->ErrorInfo}";
        // Podrías también borrar el registro pendiente si falla el mail (opcional)
        exit();
    }
}else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error al registrar Empleado: " . $stmt->error;
    exit();
}

$stmt->close();
$conn->close();
?>