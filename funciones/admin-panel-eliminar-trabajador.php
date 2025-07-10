<?php
include '../config/conexion.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../vistas/admin/admin-login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM empleado WHERE id_empleado = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../vistas/admin/admin-panel.php");
        exit();
    } else {
        echo "Error al eliminar trabajador: " . $conn->error;
    }
} else {
    echo "ID inválido.";
}
$conn->close();
?>
