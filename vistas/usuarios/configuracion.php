<?php
// /labora_db/vistas/usuarios/configuracion.php
session_start();

$isUser   = isset($_SESSION['usuario_id']);
$isWorker = isset($_SESSION['empleado_id']);
if (!$isUser && !$isWorker) {
    header("Location: /labora_db/vistas/formularios/login-options.html");
    exit();
}

require_once __DIR__ . '/../../config/conexion.php';
if (!isset($conexion) && isset($conn)) $conexion = $conn;
if (!$conexion) die("No se pudo obtener la conexión a la BD.");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

if ($isUser) {
    $tabla    = 'usuarios';
    $idCampo  = 'id_usuario';
    $idValor  = (int) $_SESSION['usuario_id'];
} else {
    $tabla    = 'empleado';
    $idCampo  = 'id_empleado';
    $idValor  = (int) $_SESSION['empleado_id'];
}

$mensaje=''; $error='';

// Traer datos actuales
$stmt = $conexion->prepare("SELECT nombre, correo FROM {$tabla} WHERE {$idCampo}=?");
$stmt->bind_param("i", $idValor);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Sesión expirada. Recargá la página.";
    } else {
        if (isset($_POST['cambiar_nombre'])) {
            $nombre = trim($_POST['nombre'] ?? '');
            if ($nombre==='') { $error="El nombre no puede estar vacío."; }
            else {
                $stmt = $conexion->prepare("UPDATE {$tabla} SET nombre=? WHERE {$idCampo}=?");
                $stmt->bind_param("si", $nombre, $idValor);
                if ($stmt->execute()) { $mensaje="Nombre actualizado."; $usuario['nombre']=$nombre; }
            }
        }

        if (isset($_POST['cambiar_correo'])) {
            $correo = trim($_POST['correo'] ?? '');
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) { $error="Correo inválido."; }
            else {
                $stmt = $conexion->prepare("SELECT 1 FROM {$tabla} WHERE correo=? AND {$idCampo}<>?");
                $stmt->bind_param("si", $correo, $idValor);
                $stmt->execute();
                if ($stmt->get_result()->num_rows>0) { $error="Ese correo ya está en uso."; }
                else {
                    $stmt = $conexion->prepare("UPDATE {$tabla} SET correo=? WHERE {$idCampo}=?");
                    $stmt->bind_param("si", $correo, $idValor);
                    if ($stmt->execute()) { $mensaje="Correo actualizado."; $usuario['correo']=$correo; }
                }
            }
        }

        if (isset($_POST['cambiar_contrasena'])) {
            $pass = $_POST['contrasena'] ?? '';
            $ok = strlen($pass)>=8 && preg_match('/\d/',$pass) && preg_match('/[^a-zA-Z0-9]/',$pass);
            if (!$ok) { $error="Mín. 8 caracteres, 1 número y 1 caracter especial."; }
            else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("UPDATE {$tabla} SET password=? WHERE {$idCampo}=?");
                $stmt->bind_param("si", $hash, $idValor);
                if ($stmt->execute()) { $mensaje="Contraseña actualizada."; }
            }
        }
    }
}

/* ========= ELIMINAR CUENTA (descubre FKs y borra en cascada) ========= */
if (isset($_POST['eliminar_cuenta'])) {
    // Conexión (usa la que ya tengas)
    $db = isset($conexion) ? $conexion : (isset($conn) ? $conn : null);
    if (!($db instanceof mysqli)) { die("Sin conexión a la base de datos."); }

    // Sesión: ¿usuario o empleado?
    session_start();
    $esUsuario  = isset($_SESSION['usuario_id']);
    $esEmpleado = isset($_SESSION['empleado_id']);
    if (!$esUsuario && !$esEmpleado) {
        header("Location: /labora_db/formularios/login-options.html"); exit();
    }

    // Tabla, PK y id logueado
    if ($esUsuario) {
        $tabla    = "usuarios";
        $pkCampo  = "id_usuario";
        $idValor  = (int)$_SESSION['usuario_id'];
        $pwdCampo = "clave";            // según tu foto
    } else {
        $tabla    = "empleado";
        $pkCampo  = "id_empleado";
        $idValor  = (int)$_SESSION['empleado_id'];
        $pwdCampo = "clave";            // asumimos mismo nombre en empleado
    }

    // Contraseña tipeada en el modal
    $pass = trim($_POST['password_confirm'] ?? '');
    if ($pass === '') {
        $error = "Debés ingresar tu contraseña.";
    } else {
        // 1) Obtener hash de la contraseña
        $stmt = $db->prepare("SELECT {$pwdCampo} AS pass_hash FROM {$tabla} WHERE {$pkCampo}=? LIMIT 1");
        if (!$stmt) { die("Error de preparación"); }
        $stmt->bind_param("i", $idValor);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row || !password_verify($pass, $row['pass_hash'])) {
            $error = "La contraseña no coincide.";
        } else {
            // 2) Borrado en transacción
            $db->begin_transaction();
            try {
                // 2.a) Descubrir todas las tablas que referencian a la actual (FKs)
                // Nota: SCHEMA() devuelve el nombre de la base actual (p.ej. labora_db)
                $sqlFk = "
                    SELECT
                        KCU.TABLE_NAME       AS child_table,
                        KCU.COLUMN_NAME      AS child_column
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU
                    JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS RC
                      ON RC.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA
                     AND RC.CONSTRAINT_NAME   = KCU.CONSTRAINT_NAME
                    JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS TC
                      ON TC.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA
                     AND TC.CONSTRAINT_NAME   = KCU.CONSTRAINT_NAME
                     AND TC.CONSTRAINT_TYPE   = 'FOREIGN KEY'
                    WHERE
                        KCU.CONSTRAINT_SCHEMA = SCHEMA()
                        AND KCU.REFERENCED_TABLE_NAME  = ?
                        AND KCU.REFERENCED_COLUMN_NAME = ?
                ";
                $fk = $db->prepare($sqlFk);
                $fk->bind_param("ss", $tabla, $pkCampo);
                $fk->execute();
                $fkRes = $fk->get_result();

                // 2.b) Borrar filas hijas (nivel 1). Si tus FKs estuvieran en CASCADE, esto igual no rompe.
                while ($r = $fkRes->fetch_assoc()) {
                    $childTable  = $r['child_table'];
                    $childColumn = $r['child_column'];

                    // Evitar borrarnos a nosotros mismos por algún FK raro
                    if ($childTable === $tabla) continue;

                    // DELETE hijo
                    $q = $db->prepare("DELETE FROM `{$childTable}` WHERE `{$childColumn}` = ?");
                    if (!$q) { throw new Exception("No se pudo preparar DELETE hijo en {$childTable}"); }
                    $q->bind_param("i", $idValor);
                    $q->execute();
                    // No chequeo affected_rows: puede ser 0 y está bien
                }

                // 2.c) Borrar el registro padre
                $del = $db->prepare("DELETE FROM {$tabla} WHERE {$pkCampo}=? LIMIT 1");
                if (!$del) { throw new Exception("No se pudo preparar DELETE en {$tabla}"); }
                $del->bind_param("i", $idValor);
                $del->execute();
                if ($del->affected_rows !== 1) {
                    throw new Exception("No se eliminó la cuenta (verificá FKs o id).");
                }

                // 2.d) Commit y cerrar sesión
                $db->commit();
                session_unset();
                session_destroy();
                header("Location: /labora_db/index.html"); exit();

            } catch (Throwable $e) {
                $db->rollback();
                // Si querés loguear: error_log($e->getMessage());
                $error = "No se pudo eliminar la cuenta. Posibles causas: relaciones/FKs en cascada inversa o triggers.";
            }
        }
    }

    // Manejo básico de error para mostrar en tu vista
    if (isset($error)) {
        // Podés setearlo a sesión/flash o mostrar inline
        echo "<div class='alert alert-danger'>{$error}</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración - Labora</title>
<link rel="stylesheet" href="/labora_db/recursos/css/nav.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body{font-family:'Roboto',sans-serif;margin:0;background:#f0f8ff;}
.wrapper{max-width:900px;margin:100px auto 40px;padding:20px;}
.panel{background:#fff;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,.08);padding:24px;}
h1{margin:0 0 8px;color:#0077B6}
.sub{color:#4f6b7a;margin:0 0 20px}
.grid{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px
}
.card{
  border:2px solid #0077B6;border-radius:14px;padding:18px;background:#fff;
  display:flex;flex-direction:column;gap:10px;align-items:flex-start
}
.btn{
  display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:none;
  border-radius:12px;background:linear-gradient(135deg,#00B4D8,#0077B6);color:#fff;
  font-weight:700;cursor:pointer;transition:transform .2s, box-shadow .2s;text-decoration:none
}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 18px rgba(0,119,182,.35)}
.helper{font-size:13px;color:#46606d}
.alert-ok{margin:12px 0;padding:10px;border-radius:8px;background:#e8fff1;color:#1e7a3f;font-weight:600}
.alert-err{margin:12px 0;padding:10px;border-radius:8px;background:#ffecec;color:#b13131;font-weight:600}

/* Modal */
.modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.45);z-index:1000;padding:20px}
.modal.is-open{display:flex}
.modal-card{width:100%;max-width:460px;background:#fff;border-radius:14px;box-shadow:0 12px 28px rgba(0,0,0,.2);overflow:hidden}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #e6eef2}
.modal-head h3{margin:0;color:#005F8C}
.modal-body{padding:16px}
.modal-body form{display:flex;flex-direction:column;gap:12px}
.input{padding:10px 12px;border:1px solid #0077B6;border-radius:10px;font-size:16px}
.modal-foot{display:flex;gap:10px;justify-content:flex-end;padding:12px 16px;border-top:1px solid #e6eef2}
.btn-sec{background:#e9f6ff;color:#005F8C;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer}
.small{font-size:12px;color:#647a86}
</style>
</head>
<body>

<!-- NAV -->
<nav class="navbar">
  <div class="logo"><a href="/labora_db/vistas/comunes/filtros.php">Labora</a></div>
  <button class="hamburger" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu">
    <i class="fa-solid fa-bars"></i>
  </button>
  <ul id="menu" class="nav-links">
    <li class="menu-header">
      <img src="/labora_db/imagenes/logo-labora.png" alt="Labora" class="menu-logo">
      <button class="menu-close" aria-label="Cerrar menú"><i class="fa-solid fa-xmark"></i></button>
    </li>
    <li class="menu-cta"><a href="/labora_db/filtros.php" class="btn-primario"><i class="fa-solid fa-rocket"></i> Buscar Trabajadores</a></li>
    <li class="menu-divider"><span>Cuenta</span></li>
    <li><a href="/labora_db/vistas/usuarios/perfil_usuario.php">Perfil</a></li>
    <li><a href="/labora_db/vistas/usuarios/configuracion.php">Configuración</a></li>
    <li><a href="/labora_db/funciones/logout.php">Cerrar sesión</a></li>
  </ul>
  <div class="menu-backdrop"></div>
</nav>

<div class="wrapper">
  <div class="panel">
    <h1>Configuración de la cuenta</h1>
    <p class="sub">Gestioná tus datos de forma rápida.</p>

    <?php if($mensaje): ?><div class="alert-ok"><?=htmlspecialchars($mensaje)?></div><?php endif; ?>
    <?php if($error): ?><div class="alert-err"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <div class="grid">

      <div class="card">
        <strong>Correo</strong>
        <div class="helper">Actual: <b><?=htmlspecialchars($usuario['correo'] ?? '')?></b></div>
        <button class="btn" data-modal="modal-correo"><i class="fa-solid fa-envelope"></i> Cambiar correo</button>
      </div>

      <div class="card">
        <strong>Contraseña</strong>
        <div class="helper">Mín. 8, 1 número y 1 especial</div>
        <button class="btn" data-modal="modal-pass"><i class="fa-solid fa-key"></i> Cambiar contraseña</button>
      </div>

      <div class="card">
        <strong>Sesión</strong>
        <div class="helper">Cerrá tu sesión actual</div>
        <form action="/labora_db/funciones/logout.php" method="POST">
          <button class="btn"><i class="fa-solid fa-door-open"></i> Cerrar sesión</button>
        </form>        
       </div>
      </div>
    </div>
    <div class="card" style="border:1px solid #e33; background:#fff5f5;">
  <strong style="color:#b00000;">Eliminar cuenta</strong>
  <div class="helper">Acción permanente. El perfil se deshabilita o elimina.</div>
  <button class="btn btn-danger" data-modal="modal-eliminar">
    <i class="fa-solid fa-trash-can"></i> Eliminar mi cuenta
  </button>
</div>
  </div>
</div>

<!-- MODAL: NOMBRE -->
<div class="modal" id="modal-nombre" aria-hidden="true">
  <div class="modal-card">
    <div class="modal-head">
      <h3><i class="fa-solid fa-user-pen"></i> Cambiar nombre</h3>
      <button class="btn-sec" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=$csrf?>">
        <label>Nuevo nombre</label>
        <input class="input" type="text" name="nombre" value="<?=htmlspecialchars($usuario['nombre'] ?? '')?>" required>
        <span class="small">Se mostrará en tu perfil y tarjetas.</span>
        <div class="modal-foot">
          <button type="button" class="btn-sec" data-close>Cancelar</button>
          <button type="submit" class="btn" name="cambiar_nombre">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: CORREO -->
<div class="modal" id="modal-correo" aria-hidden="true">
  <div class="modal-card">
    <div class="modal-head">
      <h3><i class="fa-solid fa-envelope"></i> Cambiar correo</h3>
      <button class="btn-sec" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=$csrf?>">
        <label>Nuevo correo</label>
        <input class="input" type="email" name="correo" value="<?=htmlspecialchars($usuario['correo'] ?? '')?>" required>
        <span class="small">Usaremos este correo para notificaciones y acceso.</span>
        <div class="modal-foot">
          <button type="button" class="btn-sec" data-close>Cancelar</button>
          <button type="submit" class="btn" name="cambiar_correo">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: CONTRASEÑA -->
<div class="modal" id="modal-pass" aria-hidden="true">
  <div class="modal-card">
    <div class="modal-head">
      <h3><i class="fa-solid fa-key"></i> Cambiar contraseña</h3>
      <button class="btn-sec" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=$csrf?>">
        <label>Nueva contraseña</label>
        <input class="input" type="password" name="contrasena" placeholder="Mín. 8, 1 número y 1 especial" required>
        <div class="modal-foot">
          <button type="button" class="btn-sec" data-close>Cancelar</button>
          <button type="submit" class="btn" name="cambiar_contrasena">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- MODAL: ELIMINAR CUENTA -->
<div class="modal" id="modal-eliminar" aria-hidden="true">
  <div class="modal-card">
    <div class="modal-head">
      <h3 style="color:#b00000"><i class="fa-solid fa-triangle-exclamation"></i> Eliminar cuenta</h3>
      <button class="btn-sec" data-close>&times;</button>
    </div>
    <div class="modal-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=$csrf?>">
        <p>Esta acción es <b>permanente</b>. Tu perfil será deshabilitado o eliminado y no podrás volver a acceder.</p>
       
        <label>Contraseña</label>
        <input class="input" type="password" name="password_confirm" placeholder="Ingresá tu contraseña" required>
        
        <button type="button" id="toggle-pass" class="btn-sec" style="margin-top:6px;">
         <i class="fa-solid fa-eye"></i> Mostrar
        </button>

        <label class="small" style="display:flex;gap:8px;align-items:center;">
          <input type="checkbox" id="chk-irrev" required>
          <span>Entiendo que esta acción es irreversible.</span>
        </label>

        <div class="modal-foot">
          <button type="button" class="btn-sec" data-close>Cancelar</button>
          <button type="submit" name="eliminar_cuenta" id="btn-eliminar" class="btn btn-danger" disabled>Eliminar definitivamente</button>
        </div>
        
      </form>
      
    </div>
  </div>
</div>



<script src="/labora_db/recursos/js/menu-hamburguesa.js"></script>
<script>
// Abrir / cerrar modales
document.querySelectorAll('.btn[data-modal]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.getAttribute('data-modal');
    document.getElementById(id).classList.add('is-open');
  });
});
document.querySelectorAll('[data-close]').forEach(btn=>{
  btn.addEventListener('click', ()=> btn.closest('.modal').classList.remove('is-open'));
});
// Cerrar al clickear backdrop
document.querySelectorAll('.modal').forEach(m=>{
  m.addEventListener('click', e=>{
    if(e.target===m) m.classList.remove('is-open');
  });
});
document.querySelectorAll('.btn[data-modal]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.getAttribute('data-modal');
    document.getElementById(id).classList.add('is-open');
  });
});
document.querySelectorAll('[data-close]').forEach(btn=>{
  btn.addEventListener('click', ()=> btn.closest('.modal').classList.remove('is-open'));
});
document.querySelectorAll('.modal').forEach(m=>{
  m.addEventListener('click', e=>{
    if(e.target===m) m.classList.remove('is-open');
  });
});
// Habilitar el botón rojo sólo si se marca la casilla
const chk = document.getElementById('chk-irrev');
const btnDel = document.getElementById('btn-eliminar');
if (chk && btnDel) chk.addEventListener('change', ()=> btnDel.disabled = !chk.checked);

</script>
<script>
/* Toggle mostrar/ocultar contraseña SOLO para el botón clickeado */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('#toggle-pass');
  if (!btn) return;                 // si no tocaste el botón, nada

  e.preventDefault();

  // Buscamos el input dentro del MISMO formulario / modal
  const form  = btn.closest('form');
  let pass = null;
  if (form) {
    pass = form.querySelector('#password_confirm, input[name="password_confirm"]');
  }
  // Fallback por si el input está fuera del form (raro, pero por las dudas)
  if (!pass) pass = document.getElementById('password_confirm');

  if (!pass) return; // no encontró el input -> no hace nada

  const isHidden = pass.type === 'password';
  pass.type = isHidden ? 'text' : 'password';

  // Actualiza el texto/ícono del botón
  btn.innerHTML = isHidden
    ? '<i class="fa-solid fa-eye-slash"></i> Ocultar'
    : '<i class="fa-solid fa-eye"></i> Mostrar';
});
</script>
</body>
</html>
