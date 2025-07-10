<?php
$host = "localhost";
$user = "root";
$pass = ""; // por defecto en XAMPP
$db = "labora_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>