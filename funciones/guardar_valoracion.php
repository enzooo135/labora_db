<?php
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../vistas/formulario/login.html");
    exit();
}

include __DIR__ . '/../config/conexion.php';

// Verificar que se reciban los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_empleado'], $_POST['puntuacion'])) {
    $id_empleado = (int) $_POST['id_empleado'];
    $id_usuario = (int) $_SESSION['usuario_id'];
    $puntuacion = (int) $_POST['puntuacion'];
    $comentario = $_POST['comentario'] ?? '';
    $fecha = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO valoraciones (id_empleado, id_usuario, puntuacion, comentario, fecha) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $id_empleado, $id_usuario, $puntuacion, $comentario, $fecha);
    $stmt->execute();

    // Redirigir al perfil del trabajador (vista de usuario)
    header("Location: ../vistas/usuarios/vistaperfiltrabajador.php?id=" . urlencode($id_empleado));
    exit();
} else {
    echo "Datos incompletos para enviar la valoración.";
}
?>
