<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require '../config/conexion.php';


$zona = $_GET['zona'] ?? '';
$profesion = $_GET['profesion'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT id_empleado, nombre, profesion, zona_trabajo, descripcion_servicios FROM empleado WHERE 1";

if (!empty($zona)) {
    $sql .= " AND zona_trabajo = '" . $conn->real_escape_string($zona) . "'";
}
if (!empty($profesion)) {
    $sql .= " AND profesion = '" . $conn->real_escape_string($profesion) . "'";
}
if (!empty($busqueda)) {
    $busqueda = $conn->real_escape_string($busqueda);
    $sql .= " AND (nombre LIKE '%$busqueda%' OR profesion LIKE '%$busqueda%')";
}

$resultado = $conn->query($sql);
$trabajadores = [];

while ($fila = $resultado->fetch_assoc()) {
    $fila['foto'] = '../uploads/enzoo.jpg'; // Imagen por defecto
    $trabajadores[] = $fila;
}

echo json_encode($trabajadores);
?>
