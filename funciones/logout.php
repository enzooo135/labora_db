<?php
// /labora_db/funciones/logout.php
// Cierra la sesión y vuelve al inicio

// Permitir tanto POST como GET para comodidad
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Método no permitido');
}

session_start();

// Limpiar variables de sesión
$_SESSION = [];

// Borrar cookie de sesión si existe
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destruir sesión en servidor
session_destroy();

// Redirigir al inicio
header('Location: /labora_db/index.html');
exit;
