<?php
// labora_db/funciones/logout.php
session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
session_destroy();
header("Location: /labora_db/vistas/admin/admin-login.php");
exit();
