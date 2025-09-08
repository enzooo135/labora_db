<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Trabajadores - Labora</title>
    <link rel="icon" href="/labora_db/imagenes/logo-labora.png" type="image/x-icon"/>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/labora_db/recursos/css/nav.css">
    <link rel="stylesheet" href="/labora_db/recursos/css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f0f8ff;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        h1 {
            text-align: center;
            color: #005F8C;
            margin-top: 20px;
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 30px;
        }
        .filters input, .filters select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #0077B6;
            border-radius: 8px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .card {
            background: #ffffff;
            border: 2px solid #0077B6;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #005F8C;
        }
        .card h3 {
            color: #005F8C;
            margin: 10px 0 5px;
        }
        .card p {
            margin: 4px 0;
            color: #333;
        }
        .card button {
            margin-top: 10px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #00B4D8, #0077B6);
            color: #fff;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,119,182,.35);
        }
    </style>
</head>
<body>

    <!-- NAV hamburguesa (igual que en index.html) -->
    <nav class="navbar">
        <div class="logo">
            <a href="/labora_db/filtros.php">Labora</a>
        </div>
        <button class="hamburger" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <ul id="menu" class="nav-links">
            <li class="menu-header">
                <img src="/labora_db/imagenes/logo-labora.png" alt="Labora" class="menu-logo">
                <button class="menu-close" aria-label="Cerrar menú">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </li>
            <li class="menu-cta">
                <a href="#" class="btn-primario"><i class="fa-solid fa-rocket"></i> Mis Favoritos</a>
            </li>
            <li class="menu-divider"><span>Cuenta</span></li>
            <li><a href="#">Perfil</a></li>
            <li><a href="../usuarios/configuracion.php">Configuración</a></li>
            <li><a href="../../funciones/logout.php">Cerrar sesión</a></li>
        </ul>
        <div class="menu-backdrop"></div>
    </nav>

    <div class="container">
        <h1>Buscar Trabajadores</h1>
        <div class="filters">
            <input type="text" placeholder="Buscar por nombre o profesión">
            <select>
                <option value="">Zona de trabajo</option>
                <option>Zona Norte</option>
                <option>Zona Sur</option>
                <option>Zona Oeste</option>
                <option>Zona Este</option>
            </select>
            <select>
                <option value="">Profesión</option>
                <option>Carpintería</option>
                <option>Plomería</option>
                <option>Electricidad</option>
                <option>Educación</option>
            </select>
        </div>

        <div class="grid"></div>
    </div>

    <!-- Scripts -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="/labora_db/recursos/js/menu-hamburguesa.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const grid = document.querySelector('.grid');
            const input = document.querySelector('input[type="text"]');
            const zonaSelect = document.querySelectorAll('select')[0];
            const profesionSelect = document.querySelectorAll('select')[1];

            function cargarTrabajadores() {
                const busqueda = input.value;
                const zona = zonaSelect.value;
                const profesion = profesionSelect.value;

                const url = `../../funciones/buscar.php?busqueda=${encodeURIComponent(busqueda)}&zona=${encodeURIComponent(zona)}&profesion=${encodeURIComponent(profesion)}`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        grid.innerHTML = '';
                        if (data.length === 0) {
                            grid.innerHTML = '<p>No se encontraron trabajadores.</p>';
                            return;
                        }

                        data.forEach(trabajador => {
                            const card = document.createElement('div');
                            const href = `/labora_db/vistas/usuarios/vistaperfiltrabajador.php?id=${encodeURIComponent(trabajador.id_empleado)}`;

                            card.className = 'card';
                            card.innerHTML = `
                                <img src="${trabajador.foto}" alt="Foto">
                                <h3>${trabajador.nombre}</h3>
                                <p>${trabajador.profesion}</p>
                                <p>${trabajador.zona_trabajo}</p>
                                <p style="font-size: 14px; color: #666;">${trabajador.descripcion_servicios || ''}</p>
                                <a href="${href}"><button>Ver perfil</button></a>
                            `;
                            grid.appendChild(card);
                        });
                    });
            }

            input.addEventListener('input', cargarTrabajadores);
            zonaSelect.addEventListener('change', cargarTrabajadores);
            profesionSelect.addEventListener('change', cargarTrabajadores);

            cargarTrabajadores();
        });
    </script>

</body>
</html>
