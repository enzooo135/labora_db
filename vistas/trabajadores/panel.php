<?php
include 'conexion.php';
session_start();

// Por ahora lo dejamos abierto sin restricción de admin
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - LABORA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .sidebar { width: 220px; background: #fff; position: fixed; top: 0; bottom: 0; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .content { margin-left: 240px; padding: 20px; }
        h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        .verified { color: green; font-weight: bold; }
        .pending { color: gray; font-weight: bold; }
        button { padding: 5px 10px; margin: 0 5px; border: none; border-radius: 4px; cursor: pointer; }
        .edit { background-color: #3498db; color: white; }
        .delete { background-color: #e74c3c; color: white; }
        .header { font-weight: bold; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>LABORA</h2>
    <p><b>Panel de Administración</b></p>
    <ul style="list-style:none; padding:0;">
        <li><a href="#usuarios">Usuarios</a></li>
        <li><a href="#trabajadores">Trabajadores</a></li>
    </ul>
</div>

<div class="content">
    <div id="usuarios">
        <div class="header">Usuarios Registrados</div>

        <table>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>

            <?php
            $sqlUsuarios = "SELECT * FROM usuarios";
            $resultadoUsuarios = $conn->query($sqlUsuarios);
            while($user = $resultadoUsuarios->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$user['nombre']."</td>";
                echo "<td>".$user['correo']."</td>";
                echo "<td class='verified'>Verificado</td>";
                echo "<td><button class='edit'>Editar</button><button class='delete'>Eliminar</button></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

    <br><br>

    <div id="trabajadores">
        <div class="header">Trabajadores Registrados</div>

        <table>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Profesión</th>
                <th>Zona de Trabajo</th>
                <th>Acciones</th>
            </tr>

            <?php
            $sqlTrabajadores = "SELECT * FROM empleado";
            $resultadoTrabajadores = $conn->query($sqlTrabajadores);
            while($emp = $resultadoTrabajadores->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$emp['nombre']."</td>";
                echo "<td>".$emp['correo']."</td>";
                echo "<td>".$emp['profesion']."</td>";
                echo "<td>".$emp['zona_trabajo']."</td>";
                echo "<td><button class='edit'>Editar</button><button class='delete'>Eliminar</button></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
