<?php
include '../config/conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];

    $sql = "SELECT * FROM usuarios WHERE correo = '$correo'";
    $resultado = $conn->query($sql);

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($clave, $usuario['clave'])) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            header("Location: ../vistas/comunes/filtros.php"); 
            //- esto es para redireccionar a la pagina que aparezca aca al momento de iniciar sesione exitosamente.
            exit();
        } else {
            echo "Contraseña incorrecta";
        }
    } else {
        echo "Correo no encontrado";
    }

    $conn->close();
} else {
    echo "Acceso no válido.";
}
?>