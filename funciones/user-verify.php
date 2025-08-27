<?php
include '../config/conexion.php';

if (!isset($_GET['token'])) {
    echo "Token no proporcionado.";
    exit();
}

$token = $_GET['token'];

// Buscar en registro_pendiente_usuarios
$sql = "SELECT * FROM registro_pendiente_usuarios WHERE token = '$token'";
$resultado = $conn->query($sql);

if ($resultado && $resultado->num_rows === 1) {
    $pendiente = $resultado->fetch_assoc();

    $nombre = $pendiente['nombre'];
    $dni = $pendiente['dni'];
    $fecha_nacimiento = $pendiente['fecha_nacimiento'];
    $correo = $pendiente['correo'];
    $clave = $pendiente['clave'];
    $telefono = $pendiente['telefono'];
    $direccion = $pendiente['direccion'];
    $localidad = $pendiente['localidad'];

    $insert = "INSERT INTO usuarios (nombre, dni, fecha_nacimiento, correo, clave, telefono, direccion, localidad)
               VALUES ('$nombre', '$dni', '$fecha_nacimiento', '$correo', '$clave', '$telefono', '$direccion', '$localidad')";

    if ($conn->query($insert) === TRUE) {
        $conn->query("DELETE FROM registro_pendiente_usuarios WHERE token = '$token'");
        header("Location: ../mensajes/bienvenido-mensaje.html"); //Redirige a la pagina
    } else {
        echo "Error al crear cuenta de usuario: " . $conn->error;
    }
} else {
    echo "Token inválido o ya verificado.";
}

$conn->close();
?>
