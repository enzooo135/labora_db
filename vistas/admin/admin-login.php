<?php
// labora_db/funciones/admin-login.php
session_start();
require_once __DIR__ . '/../../config/conexion.php';


$login_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = trim($_POST['usuario'] ?? '');
    $adminPass = $_POST['clave'] ?? '';

    $stmt = $conn->prepare("SELECT id_admin, usuario, clave FROM administradores WHERE usuario = ?");
    $stmt->bind_param("s", $adminUser);
    $stmt->execute();
    $res = $stmt->get_result();

    $login_error = "Usuario o contraseña inválidos.";

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $hash = $row['clave'];

        $ok = false;
        if (password_verify($adminPass, $hash)) {
            $ok = true;
            // rehash si hace falta
            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                $nuevo = password_hash($adminPass, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE administradores SET clave = ? WHERE id_admin = ?");
                $upd->bind_param("si", $nuevo, $row['id_admin']);
                $upd->execute();
            }
        } elseif (hash('sha256', $adminPass) === $hash) {
            // compat opcional si alguna vez guardaste SHA256 (borralo si no lo usás)
            $ok = true;
            $nuevo = password_hash($adminPass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE administradores SET clave = ? WHERE id_admin = ?");
            $upd->bind_param("si", $nuevo, $row['id_admin']);
            $upd->execute();
        } elseif ($adminPass === $hash) {
            // fallback si quedó en texto plano: convierte a hash seguro
            $ok = true;
            $nuevo = password_hash($adminPass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE administradores SET clave = ? WHERE id_admin = ?");
            $upd->bind_param("si", $nuevo, $row['id_admin']);
            $upd->execute();
        }

        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = (int)$row['id_admin'];
            $_SESSION['admin_user'] = $row['usuario'];
            header("Location: admin-panel.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login Administrador - LABORA</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root { --bg:#0f172a; --card:#111827; --acc:#2563eb; --mut:#94a3b8; --txt:#e5e7eb; --danger:#ef4444; }
  *{box-sizing:border-box} body{margin:0; font-family:system-ui,Segoe UI,Roboto,Arial; background:linear-gradient(135deg,#0b1220,#0f172a);}
  .wrap{min-height:100dvh; display:grid; place-items:center; padding:24px;}
  .card{width:100%; max-width:420px; background:var(--card); color:var(--txt); border-radius:18px; padding:28px; box-shadow:0 10px 30px rgba(0,0,0,.35)}
  .brand{display:flex; align-items:center; gap:10px; margin-bottom:18px}
  .brand .dot{width:12px;height:12px;border-radius:50%;background:var(--acc)}
  h1{font-size:22px; margin:0}
  p.sub{color:var(--mut); margin:6px 0 18px}
  label{display:block; font-size:14px; color:#cbd5e1; margin-bottom:6px}
  input{width:100%; padding:12px 14px; border:1px solid #1f2937; background:#0b1220; color:var(--txt); border-radius:12px; outline:none}
  input:focus{border-color:#334155}
  .row{display:flex; flex-direction:column; gap:14px; margin-bottom:16px}
  .btn{width:100%; padding:12px 16px; border:0; border-radius:12px; background:var(--acc); color:white; font-weight:600; cursor:pointer}
  .btn:hover{filter:brightness(1.05)}
  .error{color:white; background:linear-gradient(90deg,var(--danger),#f97316); padding:10px 12px; border-radius:10px; font-size:14px; margin-bottom:14px}
  .muted{font-size:12px; color:var(--mut); text-align:center; margin-top:10px}
</style>
</head>
<body>
<div class="wrap">
  <form class="card" method="post" autocomplete="off">
    <div class="brand"><span class="dot"></span><h1>LABORA · Admin</h1></div>
    <p class="sub">Ingresá tus credenciales de administrador</p>

    <?php if (!empty($login_error)): ?>
      <div class="error"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>

    <div class="row">
      <div>
        <label for="usuario">Usuario</label>
        <input id="usuario" name="usuario" required>
      </div>
      <div>
        <label for="clave">Contraseña</label>
        <input id="clave" type="password" name="clave" required>
      </div>
    </div>

    <button class="btn" type="submit">Ingresar</button>
    <div class="muted">¿Problemas para entrar? Contactá al superadmin.</div>
  </form>
</div>
</body>
</html>
