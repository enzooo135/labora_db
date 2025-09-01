// Función para validar el formulario
        function validateForm() {
            // Obtiene los valores de los campos
            const password = document.getElementById('empd-password').value;
            const confirmPassword = document.getElementById('empd-confirm-password').value;
            const messageDiv = document.getElementById('message');

            // Verifica si las contraseñas coinciden
            if (password !== confirmPassword) {
                messageDiv.textContent = 'Las contraseñas no coinciden';
                return false;
            }

            // Si todo está correcto, muestra mensaje de éxito
            messageDiv.textContent = 'Registro exitoso';
            return true;
        }

        // Función para mostrar/ocultar contraseña principal
        function togglePassword() {
            const passwordInput = document.getElementById('empd-password');
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

        // Función para mostrar/ocultar confirmación de contraseña
        function toggleConfirmPassword() {
            const confirmPasswordInput = document.getElementById('empd-confirm-password');
            const icon = document.querySelectorAll('.toggle-password i')[1];
            
            // Cambia el tipo de input y el ícono
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