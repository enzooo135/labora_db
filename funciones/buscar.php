<?php
// No mostremos warnings como HTML (rompen el JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require '../config/conexion.php';

// Deriva /labora_db desde la URL actual: /labora_db/funciones/buscar.php -> /labora_db
function base_url_root(): string {
    $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    return '/' . ($parts[0] ?? '');
}

// Normaliza el valor de foto guardado en BD a una URL servible desde el navegador
function foto_bd_a_url(?string $v, string $BASE_URL, string $default): string {
    $v = trim((string)$v);

    if ($v === '') return $default;

    // Si guardaste una URL completa
    if (preg_match('#^https?://#i', $v)) return $v;

    // Si empieza con / ya es ruta web absoluta
    if (strpos($v, '/') === 0) return $v;

    // Si trae una carpeta (ej: uploads/xxxx.jpg), anteponemos BASE_URL
    if (strpos($v, '/') !== false) {
        return rtrim($BASE_URL, '/') . '/' . ltrim($v, '/');
    }

    // Si solo es el nombre de archivo (ej: 23.jpg),
    // asumimos que vive en /uploads (ajustá si usás otra carpeta pública)
    return rtrim($BASE_URL, '/') . '/uploads/' . $v;
}

$BASE_URL        = base_url_root();                      // ej: /labora_db
$DEFAULT_IMG_URL = $BASE_URL . '/imagenes/default_user.jpg'; // tu imagen default

// Filtros
$zona      = $_GET['zona']      ?? '';
$profesion = $_GET['profesion'] ?? '';
$busqueda  = $_GET['busqueda']  ?? '';

// Armamos SQL con bind params
$sql = "SELECT id_empleado, nombre, profesion, zona_trabajo, descripcion_servicios, 
               foto_perfil AS foto_bd
        FROM empleado
        WHERE 1=1";

$params = [];
$types  = '';

if ($zona !== '') {
    $sql .= " AND zona_trabajo = ?";
    $params[] = $zona;
    $types .= 's';
}

if ($profesion !== '') {
    // Si querés exacto, usá "="; si querés por coincidencia parcial, usa LIKE con % en PHP
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
