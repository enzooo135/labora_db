<?php
include '../config/conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../formularios/worker-login.html?error=acceso");
    exit();
}

$correo = $_POST['correo'] ?? '';
$clave  = $_POST['clave'] ?? '';

if ($correo === '' || $clave === '') {
    header("Location: ../formularios/worker-login.html?error=campos&email=" . urlencode($correo));
    exit();
}

$stmt = $conn->prepare("SELECT id_empleado, nombre, clave FROM empleado WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($clave, $row['clave'])) {
        $_SESSION['empleado_id'] = $row['id_empleado'];
        $_SESSION['nombre']      = $row['nombre'];
        header("Location: ../vistas/trabajadores/vista_tra.php");
        exit();
    }
}

// Si llega acá → credenciales inválidas
header("Location: ../formularios/worker-login.html?error=cred&email=" . urlencode($correo));
exit();
