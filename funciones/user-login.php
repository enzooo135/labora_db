<?php
// ¡No pongas espacios/echo antes del header!
include '../config/conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../formularios/user-login.html?error=acceso");
    exit();
}

$correo = $_POST['correo'] ?? '';
$clave  = $_POST['clave']  ?? '';

// Si vino vacío algo, devolvemos con aviso y conservamos el email
if ($correo === '' || $clave === '') {
    header("Location: ../formularios/user-login.html?error=campos&email=" . urlencode($correo));
    exit();
}

// Consulta preparada (más seguro contra SQLi)
$stmt = $conn->prepare("SELECT id_usuario, nombre, clave FROM usuarios WHERE correo = ?");
if (!$stmt) {
    // Si falla la preparación, devolvemos error genérico
    header("Location: ../formularios/user-login.html?error=cred&email=" . urlencode($correo));
    exit();
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verificamos hash de contraseña
    if (password_verify($clave, $row['clave'])) {
        $_SESSION['usuario_id'] = $row['id_usuario'];
        $_SESSION['nombre']     = $row['nombre'];
        // Redirigir a la vista de filtros tras login correcto
        header("Location: ../vistas/comunes/filtros.php");
        exit();
    }
}

// Si llega acá, credenciales inválidas (mensaje genérico por seguridad)
header("Location: ../formularios/user-login.html?error=cred&email=" . urlencode($correo));
exit();
