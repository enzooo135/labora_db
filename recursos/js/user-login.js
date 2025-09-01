// Función para validar el formulario
        function validateForm() {
            // Obtiene los valores de los campos
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const messageDiv = document.getElementById('message');

            // Verifica si los campos están vacíos
            if (!email || !password) {
                messageDiv.textContent = 'Por favor, complete todos los campos';
                return false;
            }

            // Si todo está correcto, muestra mensaje de éxito
            messageDiv.textContent = 'Inicio de sesión exitoso';
            return true;
        }

        // Función para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('login-password');
            const icon = document.querySelector('.toggle-password i');
            
            // Cambia el tipo de input y el ícono
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