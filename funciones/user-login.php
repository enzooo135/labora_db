<?php
include '../config/conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../formularios/user-login.html?error=acceso");
    exit();
}

$correo = $_POST['correo'] ?? '';
$clave  = $_POST['clave'] ?? '';

if ($correo === '' || $clave === '') {
    header("Location: ../formularios/user-login.html?error=campos&email=" . urlencode($correo));
    exit();
}

$stmt = $conn->prepare("SELECT id_usuario, nombre, clave FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($clave, $row['clave'])) {
        $_SESSION['usuario_id'] = $row['id_usuario'];
        $_SESSION['nombre']     = $row['nombre'];
        header("Location: ../vistas/comunes/filtros.php");
        exit();
    }
}

// Fallo
header("Location: ../formularios/user-login.html?error=cred&email=" . urlencode($correo));
exit();
