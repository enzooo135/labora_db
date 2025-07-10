<?php
include '../config/conexion.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../vistas/admin/admin-login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM usuarios WHERE id_usuario = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../vistas/admin/admin-panel.php");
        exit();
    } else {
        echo "Error al eliminar usuario: " . $conn->error;
    }
} else {
    echo "ID inválido.";
}
$conn->close();
?>
