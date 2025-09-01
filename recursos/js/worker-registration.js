// ==== toggles de password ====
    function togglePassword() {
      const passwordInput = document.getElementById('emp-password');
      const icon = document.querySelector('.toggle-password i');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
    function toggleConfirmPassword() {
      const confirmPasswordInput = document.getElementById('emp-confirm-password');
      const icon = document.querySelectorAll('.toggle-password i')[1];
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        confirmPasswordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }

    // ==== Dropdown selección ÚNICA de trabajo (robusto con IDs) ====
    (function initTrabajoSingleSelect() {
      const trigger = document.getElementById('emp-trabajo-trigger');
      const menu = document.getElementById('emp-trabajo-menu');
      const hiddenInput = document.getElementById('emp-trabajo');
      const errEl = document.querySelector('[data-error-for="emp-trabajo"]');
      const placeholder = trigger.querySelector('.ms-placeholder');

      function openMenu() {
        menu.removeAttribute('hidden');
        trigger.setAttribute('aria-expanded', 'true');
      }
      function closeMenu() {
        menu.setAttribute('hidden','');
        trigger.setAttribute('aria-expanded','false');
      }
      function toggleMenu(e) {
        e.preventDefault();
        e.stopPropagation();
        if (menu.hasAttribute('hidden')) openMenu(); else closeMenu();
      }

      trigger.addEventListener('click', toggleMenu);

      // Cerrar al click afuera
      document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !trigger.contains(e.target)) {
          closeMenu();
        }
      });

      // Elegir opción
      menu.querySelectorAll('input[type="radio"][name="__trabajo_radio"]').forEach((radio) => {
        const row = radio.closest('.ms-option');
        row.addEventListener('click', (e) => {
          if (e.target.tagName !== 'INPUT') radio.checked = true;
          const val = radio.value;
          hiddenInput.value = val;
          const labelText = row.innerText.trim();
          placeholder.textContent = labelText;
          setError('');
          closeMenu();
        });
      });

      function setError(msg) {
        errEl.textContent = msg || '';
        const group = document.getElementById('grupo-trabajo');
        if (msg) group.classList.add('error'); else group.classList.remove('error');
      }

      // Validación pública que usa validateForm()
      window.validateTrabajo = function () {
        if (!hiddenInput.value) {
          setError('Seleccioná un trabajo');
          openMenu();
          return false;
        }
        setError('');
        return true;
      };
    })();

    // ==== Helpers UI de errores ====
    function ensureErrorNode(inputId) {
      const input = document.getElementById(inputId);
      const group = input.closest('.input-group') || input.parentElement;
      let node = group.querySelector(`small.error-text[data-error-for="${inputId}"]`);
      if (!node) {
        node = document.createElement('small');
        node.className = 'error-text';
        node.setAttribute('data-error-for', inputId);
        group.appendChild(node);
      }
      return node;
    }
    function setFieldError(inputId, msg) {
      const input = document.getElementById(inputId);
      const group = input.closest('.input-group') || input.parentElement;
      const node = ensureErrorNode(inputId);
      node.textContent = msg || '';
      if (msg) group.classList.add('error'); else group.classList.remove('error');
    }
    function clearAllErrors() {
      document.querySelectorAll('.input-group.error').forEach(g => g.classList.remove('error'));
      document.querySelectorAll('small.error-text').forEach(n => n.textContent = '');
    }

    // ==== Reglas cliente ====
    function isAdult(yyyy_mm_dd) {
      if (!yyyy_mm_dd) return false;
      const parts = yyyy_mm_dd.split('-');
      if (parts.length !== 3) return false;
      const d = new Date(+parts[0], +parts[1]-1, +parts[2]);
      if (isNaN(d)) return false;
      const today = new Date();
      const eighteen = new Date(d.getFullYear() + 18, d.getMonth(), d.getDate());
      return eighteen <= new Date(today.getFullYear(), today.getMonth(), today.getDate());
    }
    function passwordOK(pw) {
      // mín 8, al menos 1 letra y 1 número
      return /^(?=.*[A-Za-z])(?=.*\d).{8,}$/.test(pw);
    }

    // ==== Validación general ====
    function validateForm() {
      const messageDiv = document.getElementById('message');
      messageDiv.textContent = '';
      clearAllErrors();

      const ALLOWED_NAC   = ['Argentina', 'Chilena', 'Brasilera'];
      const ALLOWED_ZONAS = ['Merlo', 'Moron', 'Padua', 'Ituzaingo']; // alineado con backend

      let ok = true;

      // Trabajo
      if (!window.validateTrabajo || !window.validateTrabajo()) {
        ok = false;
      }

      // Fecha de nacimiento >= 18
      const fecha = document.getElementById('emp-fecha-nacimiento').value;
      if (!isAdult(fecha)) {
        setFieldError('emp-fecha-nacimiento', 'Debés ser mayor de 18 años');
        ok = false;
      }

      // Contraseña
      const pw = document.getElementById('emp-password').value;
      const pw2 = document.getElementById('emp-confirm-password').value;
      if (!passwordOK(pw)) {
        setFieldError('emp-password', 'Mínimo 8 caracteres, incluir letras y números');
        ok = false;
      }
      if (pw !== pw2) {
        setFieldError('emp-confirm-password', 'Las contraseñas no coinciden');
        ok = false;
      }

      // Teléfono 10 dígitos
      const telRaw = document.getElementById('emp-telefono').value;
      const telDigits = (telRaw || '').replace(/\D/g, '');
      if (telDigits.length !== 10) {
        setFieldError('emp-telefono', 'Ingresá 10 dígitos (solo números)');
        ok = false;
      }

      // Nacionalidad
      const nac = document.getElementById('emp-nacionalidad').value || '';
      if (!ALLOWED_NAC.includes(nac)) {
        setFieldError('emp-nacionalidad', 'Elegí una nacionalidad válida');
        ok = false;
      }

      // Zona
      const zona = document.getElementById('emp-zona').value || '';
      if (!ALLOWED_ZONAS.includes(zona)) {
        setFieldError('emp-zona', 'Elegí una zona válida');
        ok = false;
      }

      if (!ok) {
        messageDiv.textContent = 'Revisá los campos marcados';
        return false;
      }

      return true;
    }

    // Limpieza de errores en interacción
    (function attachLiveClear() {
      const ids = [
        'emp-fecha-nacimiento','emp-password','emp-confirm-password',
        'emp-telefono','emp-nacionalidad','emp-zona','emp-trabajo',
        'emp-nombre','emp-dni','emp-email','emp-experiencia'
      ];
      ids.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const ev = (el.tagName === 'SELECT' || el.type === 'date') ? 'change' : 'input';
        el.addEventListener(ev, () => setFieldError(id, ''));
      });
    })();