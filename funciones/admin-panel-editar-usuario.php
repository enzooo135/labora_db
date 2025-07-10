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
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];

    $sql = "UPDATE usuarios SET nombre='$nombre', email='$email', telefono='$telefono' WHERE id_usuario=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../vistas/admin/admin-panel.php");
        exit();
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}

$sql = "SELECT * FROM usuarios WHERE id_usuario=$id";
$resultado = $conn->query($sql);
$usuario = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
</head>
<body>

<h2>Editar Usuario</h2>

<form method="post">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?php echo $usuario['nombre']; ?>"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?php echo $usuario['email']; ?>"><br><br>

    <label>Teléfono:</label><br>
    <input type="text" name="telefono" value="<?php echo $usuario['telefono']; ?>"><br><br>

    <button type="submit">Guardar Cambios</button>
</form>

</body>
</html>
