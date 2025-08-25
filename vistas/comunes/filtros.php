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
      font-size: 18px;
    }
    .card p {
      margin: 4px 0;
      color: #333;
      font-size: 15px;
    }

    /* Footer base (si tu CSS global no lo tiene) */
    .footer {
      margin-top: 40px;
      background: #ffffff;
      border-top: 1px solid #e5e5e5;
      padding: 20px 0;
    }
    .footer-container {
      max-width: 1200px;
      margin: auto;
      display: grid;
      gap: 12px;
      justify-items: center;
      text-align: center;
    }
    .footer-links a {
      margin: 0 10px;
      color: #005F8C;
      text-decoration: none;
      font-weight: 500;
    }
    .footer-redes a {
      margin: 0 6px;
      display: inline-block;
    }
    .footer-copy {
      color: #666;
      font-size: 14px;
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

    // Placeholder absoluto: coincide con buscar.php
    const DEFAULT_PHOTO = '/labora_db/uploads/Foto_Perfil/default.jpg';

    function crearCard(trabajador) {
      const card = document.createElement('div');
      card.className = 'card';

      const img = document.createElement('img');
      img.src = trabajador.foto_url || DEFAULT_PHOTO;
      img.alt = `Foto de ${trabajador.nombre || 'trabajador'}`;
      img.loading = 'lazy';
      img.onerror = function () {
        this.onerror = null;
        this.src = DEFAULT_PHOTO;
      };

      const nombreEl = document.createElement('h3');
      nombreEl.textContent = trabajador.nombre || 'Sin nombre';

      const profEl = document.createElement('p');
      profEl.textContent = trabajador.profesion || '';

      const zonaEl = document.createElement('p');
      zonaEl.textContent = trabajador.zona_trabajo || '';

      const descEl = document.createElement('p');
      descEl.style.fontSize = '14px';
      descEl.style.color = '#666';
      descEl.textContent = trabajador.descripcion_servicios || '';

      card.appendChild(img);
      card.appendChild(nombreEl);
      card.appendChild(profEl);
      card.appendChild(zonaEl);
      card.appendChild(descEl);

      return card;
    }

    function cargarTrabajadores() {
      const busqueda = input.value;
      const zona = zonaSelect.value;
      const profesion = profesionSelect.value;

      const url = `../../funciones/buscar.php?busqueda=${encodeURIComponent(busqueda)}&zona=${encodeURIComponent(zona)}&profesion=${encodeURIComponent(profesion)}`;

      fetch(url, { cache: 'no-store' })
        .then(async (res) => {
          if (!res.ok) {
            // Mostramos el error detallado del servidor si viene JSON/texto
            const text = await res.text();
            throw new Error(text || `HTTP ${res.status}`);
          }
          return res.json();
        })
        .then(data => {
          grid.innerHTML = '';
          if (!Array.isArray(data) || data.length === 0) {
            grid.innerHTML = '<p>No se encontraron trabajadores.</p>';
            return;
          }
          data.forEach(trabajador => {
            const card = crearCard(trabajador);
            grid.appendChild(card);
          });
        })
        .catch((err) => {
          console.error('Error al cargar:', err);
          grid.innerHTML = `<p>Ocurrió un error al cargar los trabajadores.</p><pre style="white-space:pre-wrap;font-size:12px;color:#a00;background:#fee;padding:8px;border-radius:6px;">${(err && err.message) ? err.message : err}</pre>`;
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
