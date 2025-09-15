<?php
// labora_db/funciones/buscar.php

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

session_start();
require '../config/conexion.php';
mysqli_set_charset($conn, 'utf8mb4');

/**
 * Detecta el ID del usuario a partir de varias claves de sesión posibles.
 */
function current_user_id(): int {
    $candidates = [
        $_SESSION['id_usuario'] ?? null,
        $_SESSION['user_id'] ?? null,
        $_SESSION['usuario_id'] ?? null,
        $_SESSION['usuario']['id_usuario'] ?? null,
        $_SESSION['usuario']['id'] ?? null,
    ];
    foreach ($candidates as $v) {
        if (is_numeric($v) && (int)$v > 0) return (int)$v;
    }
    return 0;
}

/**
 * BASE URL (ej: /labora_db) para normalizar rutas de imagen
 */
function base_url_root(): string {
    $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    return '/' . ($parts[0] ?? '');
}

/**
 * Normaliza la foto guardada en BD a una URL servible
 */
function foto_bd_a_url(?string $v, string $BASE_URL, string $default): string {
    $v = trim((string)$v);
    if ($v === '') return $default;
    if (preg_match('#^https?://#i', $v)) return $v;          // URL completa
    if (strpos($v, '/') === 0) return $v;                    // Ruta absoluta web
    if (strpos($v, '/') !== false) {                         // Carpeta + archivo
        return rtrim($BASE_URL, '/') . '/' . ltrim($v, '/');
    }
    return rtrim($BASE_URL, '/') . '/uploads/' . $v;         // Solo archivo
}

// ===== Gate: sólo usuarios APROBADOS =====
$userId = current_user_id();
if ($userId <= 0) {
    echo json_encode([]); // No logueado
    exit;
}

// Intentamos chequear estado_verificacion; si la columna no existe, impedir acceso.
$gateOk = false;
$stmtGate = $conn->prepare("SELECT estado_verificacion FROM usuarios WHERE id_usuario = ?");
if ($stmtGate) {
    $stmtGate->bind_param("i", $userId);
    if ($stmtGate->execute()) {
        $resGate = $stmtGate->get_result();
        $rowGate = $resGate ? $resGate->fetch_assoc() : null;
        if ($rowGate && ($rowGate['estado_verificacion'] ?? '') === 'aprobado') {
            $gateOk = true;
        }
    }
    $stmtGate->close();
}
if (!$gateOk) {
    echo json_encode([]); // pendiente / rechazado / columna faltante => sin resultados
    exit;
}

// ===== Filtros =====
$BASE_URL        = base_url_root();                          // ej: /labora_db
$DEFAULT_IMG_URL = $BASE_URL . '/imagenes/default_user.jpg';

$zona      = $_GET['zona']      ?? '';
$profesion = $_GET['profesion'] ?? '';
$busqueda  = $_GET['busqueda']  ?? '';

// ===== Consulta: sólo empleados APROBADOS =====
$sql = "SELECT id_empleado, nombre, profesion, zona_trabajo, descripcion_servicios,
               foto_perfil AS foto_bd
        FROM empleado
        WHERE estado_verificacion = 'aprobado'";

$params = [];
$types  = '';

if ($zona !== '') {
    $sql .= " AND zona_trabajo = ?";
    $params[] = $zona;
    $types .= 's';
}
if ($profesion !== '') {
    $sql .= " AND profesion = ?";
    $params[] = $profesion;
    $types .= 's';
}
if ($busqueda !== '') {
    $sql .= " AND (nombre LIKE ? OR profesion LIKE ?)";
    $like = "%$busqueda%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}
$sql .= " ORDER BY id_empleado DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Error preparando consulta', 'detalle' => $conn->error], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error ejecutando consulta', 'detalle' => $stmt->error], JSON_UNESCAPED_UNICODE);
    exit;
}

$res = $stmt->get_result();
$trabajadores = [];
while ($fila = $res->fetch_assoc()) {
    $fila['foto'] = foto_bd_a_url($fila['foto_bd'] ?? '', $BASE_URL, $DEFAULT_IMG_URL);
    unset($fila['foto_bd']);
    $trabajadores[] = $fila;
}

echo json_encode($trabajadores, JSON_UNESCAPED_UNICODE);
