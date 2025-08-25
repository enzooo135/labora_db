  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>Buscar Trabajadores</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../recursos/css/index.css">

    <style>
      body {
        margin: 0;
        font-family: 'Roboto', sans-serif;
        background-color: #f0f8ff;
        padding: 20px;
      }
      .container {
        max-width: 1200px;
        margin: auto;
      }
      h1 {
        text-align: center;
        color: #005F8C;
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
    </style>

  </head>
  <body>
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

        <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-links">
                <a href="/labora_db/vistas/comunes/terminos_y_condiciones.html">Términos y Condiciones</a>
                <a href="/labora_db/vistas/comunes/politica_privacidad.html">Política de Privacidad</a>
                <a href="/labora_db/vistas/comunes/preguntas_frecuentes.html">Preguntas Frecuentes</a>
            </div>

            <div class="footer-redes">
                <a href="https://www.facebook.com/" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/24/733/733547.png" alt="Facebook">
                </a>
                <a href="https://www.instagram.com/" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/24/2111/2111463.png" alt="Instagram">
                </a>
                <a href="https://wa.me/1234567890" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/24/733/733585.png" alt="WhatsApp">
                </a>
            </div>

            <div class="footer-copy">
                &copy; 2025 Labora - Todos los derechos reservados.
            </div>
        </div>
    </footer>

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
              card.className = 'card';
              card.innerHTML = `
                <img src="../../uploads/enzoo.jpg" alt="Foto">
                <h3>${trabajador.nombre}</h3>
                <p>${trabajador.profesion}</p>
                <p>${trabajador.zona_trabajo}</p>
                <p style="font-size: 14px; color: #666;">${trabajador.descripcion_servicios || ''}</p>
              `;
              grid.appendChild(card);
            });
          });
      }

      input.addEventListener('input', cargarTrabajadores);
      zonaSelect.addEventListener('change', cargarTrabajadores);
      profesionSelect.addEventListener('change', cargarTrabajadores);

      cargarTrabajadores(); // carga inicial
    });
    </script>
  </body>
  </html>
