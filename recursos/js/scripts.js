// Validar el formulario de empleados
document.getElementById('formularioEmpleado').addEventListener('submit', function (evento) {
    evento.preventDefault();
    alert('Empleado registrado exitosamente.');
});

// Validar el formulario de empleadores
document.getElementById('formularioEmpleador').addEventListener('submit', function (evento) {
    evento.preventDefault();
    alert('Empleador registrado exitosamente.');
});
card.href = `/labora_db/perfil_trabajador_usuario.php?id=${encodeURIComponent(tr.id_empleado)}`;
