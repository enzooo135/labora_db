<?php
include '../config/conexion.php';

if (!isset($_GET['token'])) {
    echo "Token no proporcionado.";
    exit();
}

$token = $_GET['token'];

// Buscar en registro_pendiente_usuarios
$sql = "SELECT * FROM registro_pendiente_empleados WHERE token = '$token'";
$resultado = $conn->query($sql);

if ($resultado && $resultado->num_rows === 1) {
    $pendiente = $resultado->fetch_assoc();

    $nombre = $pendiente['nombre'];
    $correo = $pendiente['correo'];
    $clave = $pendiente['clave'];
    $dni = $pendiente['dni'];
    $fecha_nacimiento = $pendiente['fecha_nacimiento'];
    $nacionalidad = $pendiente['nacionalidad'];
    $telefono = $pendiente['telefono'];
    $zona_trabajo = $pendiente['zona_trabajo'];
    $experiencia = $pendiente['experiencia_años'];

        //Falta poner profesion cuando arreglemos problema
    $sql = "INSERT INTO empleado (nombre, correo, clave, dni, fecha_nacimiento, nacionalidad, telefono, zona_trabajo, experiencia_años) VALUES ('$nombre', '$correo', '$clave', '$dni', '$fecha_nacimiento', '$nacionalidad', '$telefono', '$zona_trabajo', '$experiencia')";


    if ($conn->query($sql) === TRUE) {
        $conn->query("DELETE FROM registro_pendiente_empleados WHERE token = '$token'");
        header("Location: ../mensajes/bienvenido-mensaje.html"); //Redirige a la pagina
    } else {
        echo "Error al crear cuenta de usuario: " . $conn->error;
    }
} else {
    echo "Enlace inválido o ya verificado.";
}

$conn->close();
?>
