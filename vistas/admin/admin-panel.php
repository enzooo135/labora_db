<?php
// labora_db/funciones/admin-panel.php
session_start();
if (empty($_SESSION['admin'])) {
    header("Location: admin-login.php");
    exit();
}
require_once __DIR__ . '/../../config/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Administración - LABORA</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  :root{--bg:#f4f6f8; --side:#1f2937; --acc:#2563eb; --danger:#ef4444;}
  *{box-sizing:border-box} body{margin:0; font-family:system-ui,Segoe UI,Roboto,Arial; background:var(--bg)}
  .sidebar{width:250px; background:var(--side); color:#e5e7eb; position:fixed; inset:0 auto 0 0; padding:20px}
  .sidebar h2{margin:0 0 12px}
  .sidebar a{color:#cbd5e1; text-decoration:none; display:block; padding:10px 8px; border-radius:8px}
  .sidebar a:hover{background:#111827}
  .content{margin-left:270px; padding:20px}
  .top-bar{background:linear-gradient(90deg,#0f172a,#1f2937); color:white; padding:16px; border-radius:12px; margin-bottom:16px}
  h3{margin:10px 0}
  .card{background:white; border-radius:12px; padding:16px; box-shadow:0 6px 18px rgba(0,0,0,.06); margin-bottom:26px}
  table{width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden}
  th,td{padding:12px; border-bottom:1px solid #e5e7eb; text-align:left; vertical-align:top}
  th{background:#0f172a; color:white}
  .btn{padding:8px 10px; border:0; border-radius:8px; cursor:pointer; font-weight:600}
  .btn.edit{background:#3b82f6; color:white}
  .btn.del{background:#ef4444; color:white}
  .btn.sec{background:#e5e7eb}
  .btn.ok{background:#16a34a; color:white}
  .btn.warn{background:#f59e0b; color:white}
  .badge{display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:700}
  .b-pend{background:#fde68a; color:#92400e}
  .b-apr{background:#bbf7d0; color:#065f46}
  .b-rech{background:#fecaca; color:#7f1d1d}
  form.inline{display:flex; gap:8px; align-items:center; flex-wrap:wrap}
  input[type="text"], select{padding:10px 12px; border:1px solid #d1d5db; border-radius:10px}
  .files a{display:inline-block; margin-right:10px}
</style>
</head>
<body>
<div class="sidebar">
  <h2>LABORA Admin</h2>
  <a href="#usuarios">Usuarios</a>
  <a href="#trabajadores">Trabajadores</a>
  <a href="#pendientes">Trabajadores pendientes</a>
  <a href="../../funciones/admin-logout.php">Cerrar sesión</a>
</div>

<div class="content">
  <div class="top-bar"><h2>Panel de Administración</h2></div>

  <!-- ========== USUARIOS ========== -->
  <div id="usuarios" class="card">
    <h3>Usuarios Registrados 🙍‍♂️🟢</h3>
    <form class="inline" method="get">
      <input type="text" name="buscar_usuario" placeholder="Buscar por nombre o correo" value="<?php echo htmlspecialchars($_GET['buscar_usuario'] ?? '') ?>">
      <button class="btn sec" type="submit">Buscar</button>
    </form>
    <div style="overflow:auto; margin-top:12px">
      <table>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Teléfono</th>
          <th>Acciones</th>
        </tr>
        <?php
        $filtroU = "";
        if (!empty($_GET['buscar_usuario'])) {
            $q = "%".$conn->real_escape_string($_GET['buscar_usuario'])."%";
            $filtroU = "WHERE nombre LIKE '$q' OR correo LIKE '$q'";
        }
        $sqlUsuarios = "SELECT id_usuario, nombre, correo, telefono FROM usuarios $filtroU ORDER BY id_usuario DESC";
        if ($resultadoUsuarios = $conn->query($sqlUsuarios)):
          while($user = $resultadoUsuarios->fetch_assoc()):
        ?>
        <tr>
          <td><?= htmlspecialchars($user['nombre']) ?></td>
          <td><?= htmlspecialchars($user['correo']) ?></td>
          <td><?= htmlspecialchars($user['telefono']) ?></td>
          <td>
            <a href="../../funciones/admin-panel-editar-usuario.php?id=<?= (int)$user['id_usuario'] ?>"><button class="btn edit" type="button">Editar</button></a>
            <a href="../../funciones/admin-panel-eliminar-usuario.php?id=<?= (int)$user['id_usuario'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')"><button class="btn del" type="button">Eliminar</button></a>
          </td>
        </tr>
        <?php endwhile; endif; ?>
      </table>
    </div>
  </div>

    <!-- ========== USUARIOS PENDIENTES ========== -->
  <div id="usuarios-pendientes" class="card">
    <h3>Usuarios pendientes 🙍‍♂️🟡</h3>

    <form method="get" class="inline" action="/labora_db/vistas/admin/admin-panel.php#usuarios-pendientes">
      <input type="text" name="buscar_up" placeholder="Buscar por nombre/correo/dni" value="<?php echo htmlspecialchars($_GET['buscar_up'] ?? ''); ?>">
      <button class="btn sec" type="submit">Buscar</button>
    </form>

    <div style="overflow:auto; margin-top:12px">
      <table>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>DNI</th>
          <th>Localidad</th>
          <th>Docs</th>
          <th>Acciones</th>
        </tr>
        <?php
          $condsU = ["estado_verificacion = 'pendiente'"];
          if (!empty($_GET['buscar_up'])) {
            $q = "%".$conn->real_escape_string($_GET['buscar_up'])."%";
            $condsU[] = "(nombre LIKE '$q' OR correo LIKE '$q' OR dni LIKE '$q')";
          }
          $whereU = "WHERE ".implode(" AND ", $condsU);

          $sqlUP = "
            SELECT id_usuario, nombre, correo, dni, localidad,
                   dni_frente_path, dni_dorso_path, matricula_path
            FROM usuarios
            $whereU
            ORDER BY id_usuario DESC
          ";
          if ($resUP = $conn->query($sqlUP)):
            while($u = $resUP->fetch_assoc()):
              $links = [];
              if (!empty($u['dni_frente_path'])) $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($u['dni_frente_path'])."'>DNI Frente</a>";
              if (!empty($u['dni_dorso_path']))  $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($u['dni_dorso_path'])."'>DNI Dorso</a>";
              if (!empty($u['matricula_path']))  $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($u['matricula_path'])."'>Adjunto</a>";
              $docs = $links ? implode(' · ', $links) : '<em>Sin archivos</em>';
        ?>
        <tr>
          <td><a href="/labora_db/vistas/admin/admin-usuario.php?id=<?= (int)$u['id_usuario'] ?>" style="text-decoration:none;color:#2563eb;font-weight:600"><?= htmlspecialchars($u['nombre']) ?></a></td>
          <td><?= htmlspecialchars($u['correo']) ?></td>
          <td><?= htmlspecialchars($u['dni']) ?></td>
          <td><?= htmlspecialchars($u['localidad']) ?></td>
          <td class="files"><?= $docs ?></td>
          <td>
            <a href="/labora_db/vistas/admin/admin-usuario.php?id=<?= (int)$u['id_usuario'] ?>"><button class="btn edit" type="button">Revisar</button></a>
          </td>
        </tr>
        <?php endwhile; endif; ?>
      </table>
    </div>
  </div>


  <!-- ========== TRABAJADORES PENDIENTES ========== -->
  <div id="pendientes" class="card">
    <h3>Trabajadores pendientes 🛠️ 🟡</h3>

    <form method="get" class="inline">
      <input type="text" name="buscar_pendiente" placeholder="Buscar por nombre/correo/profesión" value="<?php echo htmlspecialchars($_GET['buscar_pendiente'] ?? ''); ?>">
      <button class="btn sec" type="submit">Buscar</button>
    </form>

    <div style="overflow:auto; margin-top:12px">
      <table>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Profesión</th>
          <th>Zona</th>
          <th>DNI</th>
          <th>Docs</th>
          <th>Acciones</th>
        </tr>
        <?php
          $condsP = ["estado_verificacion = 'pendiente'"];
          if (!empty($_GET['buscar_pendiente'])) {
            $q = "%".$conn->real_escape_string($_GET['buscar_pendiente'])."%";
            $condsP[] = "(nombre LIKE '$q' OR correo LIKE '$q' OR profesion LIKE '$q')";
          }
          $whereP = "WHERE ".implode(" AND ", $condsP);

          $sqlPend = "
            SELECT id_empleado, nombre, correo, profesion, zona_trabajo, dni,
                   dni_frente_path, dni_dorso_path, matricula_path
              FROM empleado
              $whereP
             ORDER BY id_empleado DESC
          ";
          if ($resPend = $conn->query($sqlPend)):
            while($p = $resPend->fetch_assoc()):
              $links = [];
              if (!empty($p['dni_frente_path'])) $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($p['dni_frente_path'])."'>DNI Frente</a>";
              if (!empty($p['dni_dorso_path']))  $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($p['dni_dorso_path'])."'>DNI Dorso</a>";
              if (!empty($p['matricula_path']))  $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($p['matricula_path'])."'>Matrícula</a>";
              $docs = $links ? implode(' · ', $links) : '<em>Sin archivos</em>';
        ?>
          <tr>
            <td><a href="admin-trabajador.php?id=<?= (int)$p['id_empleado'] ?>" style="text-decoration:none;color:#2563eb;font-weight:600"><?= htmlspecialchars($p['nombre']) ?></a></td>
            <td><?= htmlspecialchars($p['correo']) ?></td>
            <td><?= htmlspecialchars($p['profesion']) ?></td>
            <td><?= htmlspecialchars($p['zona_trabajo']) ?></td>
            <td><?= htmlspecialchars($p['dni']) ?></td>
            <td class="files"><?= $docs ?></td>
            <td>
              <a href="admin-trabajador.php?id=<?= (int)$p['id_empleado'] ?>"><button class="btn edit" type="button">Revisar</button></a>
            </td>
          </tr>
        <?php endwhile; endif; ?>
      </table>
    </div>
  </div>

  <!-- ========== TRABAJADORES ========== -->
  <div id="trabajadores" class="card">
    <h3>Trabajadores Registrados 🛠️🟢 </h3>

    <form method="get" class="inline">
      <input type="text" name="buscar_trabajador" placeholder="Buscar por nombre/correo/profesión" value="<?php echo htmlspecialchars($_GET['buscar_trabajador'] ?? ''); ?>">
      <select name="estado">
        <?php $estadoSel = $_GET['estado'] ?? ''; ?>
        <option value="">Todos</option>
        <option value="pendiente"  <?= $estadoSel==='pendiente'?'selected':'' ?>>Pendiente</option>
        <option value="aprobado"   <?= $estadoSel==='aprobado'?'selected':'' ?>>Aprobado</option>
        <option value="rechazado"  <?= $estadoSel==='rechazado'?'selected':'' ?>>Rechazado</option>
      </select>
      <button class="btn sec" type="submit">Buscar</button>
    </form>

    <div style="overflow:auto; margin-top:12px">
      <table>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Profesión</th>
          <th>Zona</th>
          <th>DNI</th>
          <th>Docs</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>

        <?php
          $conds = [];
          if (!empty($_GET['buscar_trabajador'])) {
            $q = "%".$conn->real_escape_string($_GET['buscar_trabajador'])."%";
            $conds[] = "(nombre LIKE '$q' OR correo LIKE '$q' OR profesion LIKE '$q')";
          }
          if (!empty($_GET['estado'])) {
            $est = $conn->real_escape_string($_GET['estado']);
            $conds[] = "estado_verificacion = '$est'";
          }
          $where = $conds ? ("WHERE ".implode(" AND ", $conds)) : "";

          $sqlTrabajadores = "
            SELECT id_empleado, nombre, correo, profesion, zona_trabajo, dni,
                   estado_verificacion, dni_frente_path, dni_dorso_path, matricula_path
              FROM empleado
              $where
             ORDER BY id_empleado DESC
          ";

          if ($resultadoTrabajadores = $conn->query($sqlTrabajadores)):
            while($emp = $resultadoTrabajadores->fetch_assoc()):
              $badge = "<span class='badge b-pend'>Pendiente</span>";
              if ($emp['estado_verificacion']==='aprobado')  $badge = "<span class='badge b-apr'>Aprobado</span>";
              if ($emp['estado_verificacion']==='rechazado') $badge = "<span class='badge b-rech'>Rechazado</span>";

              $links = [];
              if (!empty($emp['dni_frente_path'])) $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($emp['dni_frente_path'])."'>DNI Frente</a>";
              if (!empty($emp['dni_dorso_path']))  $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($emp['dni_dorso_path'])."'>DNI Dorso</a>";
              if (!empty($emp['matricula_path']))  $links[] = "<a target='_blank' href='/labora_db/funciones/admin-file.php?f=".urlencode($emp['matricula_path'])."'>Matrícula</a>";
              $docs = $links ? implode(' · ', $links) : '<em>Sin archivos</em>';
        ?>
          <tr>
            <td><?= htmlspecialchars($emp['nombre']) ?></td>
            <td><?= htmlspecialchars($emp['correo']) ?></td>
            <td><?= htmlspecialchars($emp['profesion']) ?></td>
            <td><?= htmlspecialchars($emp['zona_trabajo']) ?></td>
            <td><?= htmlspecialchars($emp['dni']) ?></td>
            <td class="files"><?= $docs ?></td>
            <td><?= $badge ?></td>
            <td>
              <form method="post" action="admin-verificar-trabajador.php" style="display:inline">
                <input type="hidden" name="id_empleado" value="<?= (int)$emp['id_empleado'] ?>">
                <button class="btn ok"   name="accion" value="aprobar">Aprobar</button>
                <button class="btn warn" name="accion" value="rechazar">Rechazar</button>
              </form>
              <a href="../../funciones/admin-panel-editar-trabajador.php?id=<?= (int)$emp['id_empleado'] ?>"><button class="btn edit" type="button">Editar</button></a>
              <a href="../../funciones/admin-panel-eliminar-trabajador.php?id=<?= (int)$emp['id_empleado'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este trabajador?')"><button class="btn del" type="button">Eliminar</button></a>
            </td>
          </tr>
        <?php endwhile; endif; ?>
      </table>
    </div>
  </div>
</div>
</body>
</html>
