<?php
include '../config/conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];

    $sql = "SELECT * FROM empleado WHERE correo = '$correo'";
    $resultado = $conn->query($sql);

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($clave, $usuario['clave'])) {
            $_SESSION['empleado_id'] = $usuario['id_empleado'];
            $_SESSION['nombre'] = $usuario['nombre'];
            header("Location: ../vistas/trabajadores/perfildeltrabajador.php");
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