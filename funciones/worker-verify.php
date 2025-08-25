<?php
require '../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

if (!isset($_GET['token']) || $_GET['token'] === '') {
    echo "Token no proporcionado.";
    exit();
}

$token = $_GET['token'];

// 1) Buscar registro pendiente por token (prepared)
$sqlSel = "SELECT nombre, correo, clave, profesion, dni, fecha_nacimiento, nacionalidad, telefono, zona_trabajo, `experiencia_años`
           FROM registro_pendiente_empleados
           WHERE token = ?";
$stmtSel = $conn->prepare($sqlSel);
if (!$stmtSel) {
    echo "Error preparando SELECT: " . $conn->error;
    exit();
}
$stmtSel->bind_param("s", $token);
$stmtSel->execute();
$res = $stmtSel->get_result();

if ($res && $res->num_rows === 1) {
    $pendiente = $res->fetch_assoc();

    // 2) Mapear campos
    $nombre           = $pendiente['nombre'];
    $correo           = $pendiente['correo'];
    $clave            = $pendiente['clave']; // ya viene hasheada desde el registro
    $profesion        = $pendiente['profesion']; // 
    $dni              = $pendiente['dni'];
    $fecha_nacimiento = $pendiente['fecha_nacimiento'];
    $nacionalidad     = $pendiente['nacionalidad'];
    $telefono         = $pendiente['telefono'];
    $zona_trabajo     = $pendiente['zona_trabajo'];
    $experiencia      = (int)$pendiente['experiencia_años']; // asegurar int

    // (Opcional) Validación mínima por si viniera vacío:
    if ($profesion === null || $profesion === '') {
        echo "Error: la profesión está vacía en el registro pendiente.";
        exit();
    }

    // 3) Insertar en empleado (prepared). 
    $sqlIns = "INSERT INTO empleado 
        (nombre, correo, clave, profesion, dni, fecha_nacimiento, nacionalidad, telefono, zona_trabajo, `experiencia_años`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtIns = $conn->prepare($sqlIns);
    if (!$stmtIns) {
        echo "Error preparando INSERT: " . $conn->error;
        exit();
    }
    $stmtIns->bind_param(
        "sssssssssi",
        $nombre,
        $correo,
        $clave,
        $profesion,
        $dni,
        $fecha_nacimiento,
        $nacionalidad,
        $telefono,
        $zona_trabajo,
        $experiencia
    );

    if ($stmtIns->execute()) {
        // 4) Borrar el pendiente (prepared)
        $sqlDel = "DELETE FROM registro_pendiente_empleados WHERE token = ?";
        $stmtDel = $conn->prepare($sqlDel);
        if ($stmtDel) {
            $stmtDel->bind_param("s", $token);
            $stmtDel->execute();
            $stmtDel->close();
        }

        header("Location: ../mensajes/bienvenido-mensaje.html");
        exit();
    } else {
        echo "Error al crear cuenta de usuario: " . $stmtIns->error;
        exit();
    }
} else {
    echo "Enlace inválido o ya verificado.";
    exit();
}

$stmtSel->close();
$conn->close();
?>