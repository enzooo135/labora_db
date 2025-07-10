<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin-login.php");
    exit();
}

include '../../config/conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - LABORA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
        .sidebar { width: 230px; background: #2c3e50; color: white; position: fixed; height: 100%; padding: 20px; }
        .sidebar h2 { color: #ecf0f1; }
        .sidebar a { color: #ecf0f1; text-decoration: none; display: block; padding: 10px 0; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 250px; padding: 20px; }
        h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #3498db; color: white; }
        button { padding: 5px 10px; margin: 0 5px; border: none; border-radius: 4px; cursor: pointer; }
        .edit { background-color: #3498db; color: white; }
        .delete { background-color: #e74c3c; color: white; }
        input[type="text"] { padding: 8px; width: 300px; margin-bottom: 15px; }
        .top-bar { background: #3498db; color: white; padding: 15px; text-align: center; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>LABORA Admin</h2>
    <a href="#usuarios">Usuarios</a>
    <a href="#trabajadores">Trabajadores</a>
    <a href="logout.php">Cerrar sesión</a>
</div>

<div class="content">

    <div class="top-bar"><h2>Panel de Administración</h2></div>

    <!-- Usuarios -->
    <div id="usuarios">
        <h3>Usuarios Registrados</h3>

        <form method="get">
            <input type="text" name="buscar_usuario" placeholder="Buscar usuarios..." value="<?php echo $_GET['buscar_usuario'] ?? ''; ?>">
            <button type="submit">Buscar</button>
        </form>

        <table>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>

            <?php
            $filtro = "";
            if (!empty($_GET['buscar_usuario'])) {
                $busqueda = $conn->real_escape_string($_GET['buscar_usuario']);
                $filtro = "WHERE nombre LIKE '%$busqueda%' OR correo LIKE '%$busqueda%'";
            }

            $sqlUsuarios = "SELECT * FROM usuarios $filtro";
            $resultadoUsuarios = $conn->query($sqlUsuarios);
            while($user = $resultadoUsuarios->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$user['nombre']."</td>";
                echo "<td>".$user['correo']."</td>";
                echo "<td>".$user['telefono']."</td>";
                echo "<td>
                    <a href='admin-panel-editar-usuario.php?id=".$user['id_usuario']."'><button class='edit'>Editar</button></a>
                    <a href='admin-panel-eliminar-usuario.php?id=".$user['id_usuario']."' onclick=\"return confirm('¿Seguro que deseas eliminar este usuario?')\"><button class='delete'>Eliminar</button></a>
                    </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

    <br><br>

    <!-- Trabajadores -->
    <div id="trabajadores">
        <h3>Trabajadores Registrados</h3>

        <form method="get">
            <input type="text" name="buscar_trabajador" placeholder="Buscar trabajadores..." value="<?php echo $_GET['buscar_trabajador'] ?? ''; ?>">
            <button type="submit">Buscar</button>
        </form>

        <table>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Profesión</th>
                <th>Zona de Trabajo</th>
                <th>Acciones</th>
            </tr>

            <?php
            $filtroT = "";
            if (!empty($_GET['buscar_trabajador'])) {
                $busquedaT = $conn->real_escape_string($_GET['buscar_trabajador']);
                $filtroT = "WHERE nombre LIKE '%$busquedaT%' OR correo LIKE '%$busquedaT%' OR profesion LIKE '%$busquedaT%'";
            }

            $sqlTrabajadores = "SELECT * FROM empleado $filtroT";
            $resultadoTrabajadores = $conn->query($sqlTrabajadores);
            while($emp = $resultadoTrabajadores->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$emp['nombre']."</td>";
                echo "<td>".$emp['correo']."</td>";
                echo "<td>".$emp['profesion']."</td>";
                echo "<td>".$emp['zona_trabajo']."</td>";
                echo "<td>
                    <a href='admin-panel-editar-trabajador.php?id=".$emp['id_empleado']."'><button class='edit'>Editar</button></a>
                    <a href='admin-panel-eliminar-trabajador.php?id=".$emp['id_empleado']."' onclick=\"return confirm('¿Seguro que deseas eliminar este trabajador?')\"><button class='delete'>Eliminar</button></a>
                    </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</div>

</body>
</html>

<?php $conn->close(); ?>
