<?php
require __DIR__ . '/../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

$token = $_POST['token'] ?? '';
$pass1 = $_POST['pass1'] ?? '';
$pass2 = $_POST['pass2'] ?? '';

if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
  http_response_code(400);
  exit('Token inválido.');
}
if ($pass1 === '' || $pass2 === '') {
  http_response_code(400);
  exit('Las contraseñas no pueden estar vacías.');
}
if ($pass1 !== $pass2) {
  http_response_code(400);
  exit('Las contraseñas no coinciden.');
}
// Requisitos mínimos: 8+, 1 número, 1 caracter especial
if (strlen($pass1) < 8 || !preg_match('/\d/', $pass1) || !preg_match('/[^A-Za-z0-9]/', $pass1)) {
  http_response_code(400);
  exit('La contraseña debe tener mínimo 8 caracteres, incluir al menos 1 número y 1 caracter especial.');
}

$tokenHash = hash('sha256', $token);

$sql = "SELECT id_empleado, reset_expires FROM empleado WHERE reset_token_hash = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$res = $stmt->get_result();
$emp = $res->fetch_assoc();
$stmt->close();

if (!$emp) {
  http_response_code(400);
  exit('Enlace inválido.');
}
$now = new DateTime();
$exp = new DateTime($emp['reset_expires']);
if ($exp <= $now) {
  http_response_code(400);
  exit('El enlace expiró. Volvé a solicitarlo.');
}

$nuevoHash = password_hash($pass1, PASSWORD_DEFAULT);

$up = $conn->prepare("UPDATE empleado SET clave = ?, reset_token_hash = NULL, reset_expires = NULL WHERE id_empleado = ?");
$up->bind_param("si", $nuevoHash, $emp['id_empleado']);
if ($up->execute()) {
  header('Location: /labora_db/mensajes/contraseña-actualizada.html');
  exit();
} else {
  http_response_code(500);
  exit('No se pudo actualizar la contraseña. Probá de nuevo.');
}
