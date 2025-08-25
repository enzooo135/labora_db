<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require '../config/conexion.php';

// Base absoluta pública (ajustá si tu carpeta raíz no es /labora_db)
$BASE_PUBLICA = '/labora_db/uploads/Foto_Perfil/';
$DIR_FOTOS    = __DIR__ . '/../uploads/Foto_Perfil/';

$zona      = $_GET['zona']      ?? '';
$profesion = $_GET['profesion'] ?? '';
$busqueda  = $_GET['busqueda']  ?? '';

try {
    $conds  = [];
    $types  = '';
    $params = [];

    if ($zona !== '') {
        $conds[] = 'zona_trabajo = ?';
        $types  .= 's';
        $params[] = $zona;
    }
    if ($profesion !== '') {
        $conds[] = 'profesion = ?';
        $types  .= 's';
        $params[] = $profesion;
    }
    if ($busqueda !== '') {
        $conds[] = '(nombre LIKE ? OR profesion LIKE ?)';
        $types  .= 'ss';
        $like = '%' . $busqueda . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $sql = "SELECT id_empleado, nombre, profesion, zona_trabajo, descripcion_servicios, COALESCE(foto_perfil,'') AS foto_perfil
            FROM empleado";
    if (!empty($conds)) {
        $sql .= ' WHERE ' . implode(' AND ', $conds);
    }
    $sql .= ' ORDER BY nombre ASC';

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al preparar la consulta.']);
        exit;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al ejecutar la consulta.']);
        exit;
    }

    $trabajadores = [];
    if (method_exists($stmt, 'get_result')) {
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $archivo = $fila['foto_perfil'] ?: '';
            if ($archivo !== '' && !is_file($DIR_FOTOS . $archivo)) {
            $archivo = '';
        }
            // IMPORTANTE: codificar el nombre por si tiene espacios
            $foto_url = $BASE_PUBLICA . ($archivo !== '' ? rawurlencode($archivo) : 'default.jpg');

            $trabajadores[] = [
                'id_empleado'           => (int)$fila['id_empleado'],
                'nombre'                => $fila['nombre'],
                'profesion'             => $fila['profesion'],
                'zona_trabajo'          => $fila['zona_trabajo'],
                'descripcion_servicios' => $fila['descripcion_servicios'],
                'foto'                  => $archivo,
                'foto_url'              => $foto_url
            ];
        }
    } else {
        $stmt->bind_result($id, $nombre, $prof, $zona_trab, $desc, $foto_perfil);
        while ($stmt->fetch()) {
            $archivo = $foto_perfil ?: '';
            $foto_url = $BASE_PUBLICA . ($archivo !== '' ? rawurlencode($archivo) : 'default.jpg');
            $trabajadores[] = [
                'id_empleado'           => (int)$id,
                'nombre'                => $nombre,
                'profesion'             => $prof,
                'zona_trabajo'          => $zona_trab,
                'descripcion_servicios' => $desc,
                'foto'                  => $archivo,
                'foto_url'              => $foto_url
            ];
        }
    }

    $stmt->close();
    echo json_encode($trabajadores, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Excepción: '.$e->getMessage()]);
}
