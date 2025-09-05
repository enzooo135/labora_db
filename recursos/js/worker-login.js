// Mostrar mensajes según la query string
(function () {
  const params = new URLSearchParams(window.location.search);
  const msg = document.getElementById('message');
  const emailInput = document.getElementById('login-email');

  // Prellenar email si vino en la URL
  const emailFromQS = params.get('email');
  if (emailFromQS) emailInput.value = emailFromQS;

  const error = params.get('error');
  if (!error) return;

  let text = '';
  switch (error) {
    case 'cred':
      text = 'Mail o contraseña incorrectos';
      break;
    case 'campos':
      text = 'Por favor, complete todos los campos';
      break;
    case 'acceso':
      text = 'Acceso no válido';
      break;
    default:
      text = 'Ocurrió un error. Intente nuevamente';
  }
  msg.textContent = text;
  msg.classList.add('error'); // agregá clase CSS para que se vea rojo
})();

// Validación rápida en cliente
function validateForm() {
  const email = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value.trim();
  const messageDiv = document.getElementById('message');

  if (!email || !password) {
    messageDiv.textContent = 'Por favor, complete todos los campos';
    messageDiv.classList.add('error');
    return false;
  }
  messageDiv.textContent = '';
  messageDiv.classList.remove('error');
  return true;
}

// Mostrar/ocultar contraseña
function togglePassword() {
  const passwordInput = document.getElementById('login-password');
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
