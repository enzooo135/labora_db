<?php
// labora_db/funciones/worker-verify.php
require '../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

// === Email (PHPMailer) ===
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

function enviarPendiente($correo, $nombre) {
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
        $mail->Subject = '¡Recibimos tu verificación en LABORA!';

        $nombreEsc = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;line-height:1.5;color:#111'>
              <h2 style='margin:0 0 8px'>Hola, $nombreEsc 👋</h2>
              <p>¡Gracias por verificar tu correo y enviar tu documentación!</p>
              <p>Tu cuenta pasó al estado <strong>PENDIENTE</strong>. Nuestro equipo de administración revisará tus datos y te enviaremos un mail cuando sea <strong>aprobada</strong> o en caso de requerir correcciones.</p>
              <p style='font-size:13px;color:#555'>Este proceso puede demorar un poco. Te avisamos apenas haya novedades.</p>
              <hr style='border:none;border-top:1px solid #eee;margin:16px 0'>
              <p style='font-size:12px;color:#777'>Si no fuiste vos, escribinos a <a href='mailto:labora1357@gmail.com'>labora1357@gmail.com</a>.</p>
            </div>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log('[VERIFY MAIL ERROR] ' . $mail->ErrorInfo);
    }
}

if (!isset($_GET['token']) || $_GET['token'] === '') {
    echo "Token no proporcionado.";
    exit();
}

$token = $_GET['token'];

// 1) Buscar registro pendiente por token (incluimos id y rutas temporales)
$sqlSel = "SELECT id_empleado, nombre, correo, clave, profesion, dni, fecha_nacimiento, nacionalidad, telefono, zona_trabajo, `experiencia_años`,
                  dni_frente_tmp, dni_dorso_tmp, matricula_tmp
           FROM registro_pendiente_empleados
           WHERE token = ?";
$stmtSel = $conn->prepare($sqlSel);
if (!$stmtSel) { echo "Error preparando SELECT: " . $conn->error; exit(); }
$stmtSel->bind_param("s", $token);
$stmtSel->execute();
$res = $stmtSel->get_result();

if ($res && $res->num_rows === 1) {
    $pend = $res->fetch_assoc();

    // Mapear
    $reg_id          = (int)$pend['id_empleado'];
    $nombre          = $pend['nombre'];
    $correo          = $pend['correo'];
    $clave           = $pend['clave']; // ya hasheada
    $profesion       = $pend['profesion'];
    $dni             = $pend['dni'];
    $fecha_nacimiento= $pend['fecha_nacimiento'];
    $nacionalidad    = $pend['nacionalidad'];
    $telefono        = $pend['telefono'];
    $zona_trabajo    = $pend['zona_trabajo'];
    $experiencia     = (int)$pend['experiencia_años'];

    $dni_frente_tmp  = $pend['dni_frente_tmp'];
    $dni_dorso_tmp   = $pend['dni_dorso_tmp'];
    $matricula_tmp   = $pend['matricula_tmp'];

    if ($profesion === null || $profesion === '') {
        echo "Error: la profesión está vacía en el registro pendiente.";
        exit();
    }

    // 2) Insertar en empleado: estado_verificacion 'pendiente'
    $sqlIns = "INSERT INTO empleado 
        (nombre, correo, clave, profesion, dni, fecha_nacimiento, nacionalidad, telefono, zona_trabajo, `experiencia_años`,
         estado_verificacion, dni_frente_path, dni_dorso_path, matricula_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NULL, NULL, NULL)";
    $stmtIns = $conn->prepare($sqlIns);
    if (!$stmtIns) { echo "Error preparando INSERT: " . $conn->error; exit(); }
    $stmtIns->bind_param(
        "sssssssssi",
        $nombre, $correo, $clave, $profesion, $dni, $fecha_nacimiento, $nacionalidad, $telefono, $zona_trabajo, $experiencia
    );
    if (!$stmtIns->execute()) { echo "Error al crear cuenta de trabajador: " . $stmtIns->error; exit(); }

    $empleado_id = $stmtIns->insert_id;

    // 3) Mover/renombrar archivos a carpeta final
    $projectRoot = realpath(__DIR__ . '/..'); if ($projectRoot === false) { $projectRoot = dirname(__DIR__); }

    $preRel = "uploads/verificaciones/pre_empleado_{$reg_id}";
    $preAbs = $projectRoot . DIRECTORY_SEPARATOR . $preRel;

    $dstRel = "uploads/verificaciones/empleado_{$empleado_id}";
    $dstAbs = $projectRoot . DIRECTORY_SEPARATOR . $dstRel;

    $srcLegacyRel = "uploads/pendientes/reg_{$reg_id}";
    $srcLegacyAbs = $projectRoot . DIRECTORY_SEPARATOR . $srcLegacyRel;

    $dni_frente_new = null; $dni_dorso_new = null; $matricula_new = null;

    if (is_dir($preAbs)) {
        if (!is_dir(dirname($dstAbs))) { @mkdir(dirname($dstAbs), 0775, true); }
        if (is_dir($dstAbs)) { @rmdir($dstAbs); }
        if (@rename($preAbs, $dstAbs)) {
            $dni_frente_new = $dni_frente_tmp ? basename($dni_frente_tmp) : null;
            $dni_dorso_new  = $dni_dorso_tmp  ? basename($dni_dorso_tmp)  : null;
            $matricula_new  = $matricula_tmp  ? basename($matricula_tmp)  : null;
        } else {
            $moveFrom = function($relTmp) use ($preAbs, $dstAbs) {
                if (!$relTmp) return null;
                $base = basename($relTmp);
                $src  = $preAbs . DIRECTORY_SEPARATOR . $base;
                if (!is_file($src)) return null;
                if (!is_dir($dstAbs)) { @mkdir($dstAbs, 0775, true); }
                $dst  = $dstAbs . DIRECTORY_SEPARATOR . $base;
                if (@rename($src, $dst) || @copy($src, $dst)) { return $base; }
                return null;
            };
            $dni_frente_new = $moveFrom($dni_frente_tmp);
            $dni_dorso_new  = $moveFrom($dni_dorso_tmp);
            $matricula_new  = $moveFrom($matricula_tmp);
            @rmdir($preAbs);
        }
    } elseif (is_dir($srcLegacyAbs)) {
        $moveOne = function($relTmp) use ($srcLegacyAbs, $dstAbs) {
            if (!$relTmp) return null;
            $base = basename($relTmp);
            $src  = $srcLegacyAbs . DIRECTORY_SEPARATOR . $base;
            if (!is_file($src)) return null;
            if (!is_dir($dstAbs)) { @mkdir($dstAbs, 0775, true); }
            $dst  = $dstAbs . DIRECTORY_SEPARATOR . $base;
            if (@rename($src, $dst)) { return $base; }
            return null;
        };
        $dni_frente_new = $moveOne($dni_frente_tmp);
        $dni_dorso_new  = $moveOne($dni_dorso_tmp);
        $matricula_new  = $moveOne($matricula_tmp);
        @rmdir($srcLegacyAbs);
    }

    $dni_frente_path = $dni_frente_new ? ($dstRel . '/' . $dni_frente_new) : null;
    $dni_dorso_path  = $dni_dorso_new  ? ($dstRel . '/' . $dni_dorso_new)  : null;
    $matricula_path  = $matricula_new  ? ($dstRel . '/' . $matricula_new)  : null;

    $upd = $conn->prepare("
        UPDATE empleado
           SET dni_frente_path = ?, dni_dorso_path = ?, matricula_path = ?
         WHERE id_empleado = ?
    ");
    $upd->bind_param("sssi", $dni_frente_path, $dni_dorso_path, $matricula_path, $empleado_id);
    $upd->execute();

    // 4) Borrar el pendiente y mandar email de "pendiente"
    $sqlDel = "DELETE FROM registro_pendiente_empleados WHERE token = ?";
    $stmtDel = $conn->prepare($sqlDel);
    if ($stmtDel) { $stmtDel->bind_param("s", $token); $stmtDel->execute(); $stmtDel->close(); }

    enviarPendiente($correo, $nombre);

    header("Location: ../mensajes/revision.html");
    exit();
} else {
    echo "Enlace inválido o ya verificado.";
    exit();
}

$stmtSel->close();
$conn->close();
