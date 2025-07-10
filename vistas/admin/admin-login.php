<?php
session_start();
include '../../config/conexion.php'; // Asegurate de tener la conexión a tu base

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = $_POST['usuario'];
    $adminPass = $_POST['clave'];

    // Buscar en la base de datos
    $sql = "SELECT * FROM administradores WHERE usuario = '$adminUser'";
    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows === 1) {
        $admin = $resultado->fetch_assoc();

        if ($adminPass === $admin['clave']) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_user'] = $admin['usuario'];
            header("Location: admin-panel.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Administrador no encontrado.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador - LABORA</title>
</head>
<body>
    <h2>Panel de Administración</h2>

    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

    <form method="post">
        <label>Usuario:</label><br>
        <input type="text" name="usuario" required><br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="clave" required><br><br>

        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
