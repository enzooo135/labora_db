<?php
include '../config/conexion.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin-login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID no especificado.";
    exit();
}

$id = intval($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $profesion = $_POST['profesion'];
    $zona = $_POST['zona'];

    $sql = "UPDATE empleado SET nombre='$nombre', correo='$correo', profesion='$profesion', zona_trabajo='$zona' WHERE id_empleado=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin-panel.php");
        exit();
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}

$sql = "SELECT * FROM empleado WHERE id_empleado=$id";
$resultado = $conn->query($sql);
$empleado = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Trabajador</title>
</head>
<body>

<h2>Editar Trabajador</h2>

<form method="post">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?php echo $empleado['nombre']; ?>"><br><br>

    <label>Correo:</label><br>
    <input type="email" name="correo" value="<?php echo $empleado['correo']; ?>"><br><br>

    <label>Profesión:</label><br>
    <input type="text" name="profesion" value="<?php echo $empleado['profesion']; ?>"><br><br>

    <label>Zona de Trabajo:</label><br>
    <input type="text" name="zona" value="<?php echo $empleado['zona_trabajo']; ?>"><br><br>

    <button type="submit">Guardar Cambios</button>
</form>

</body>
</html>
