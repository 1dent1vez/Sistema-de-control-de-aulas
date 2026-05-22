{{--
/**
 * G.A.M.A. SOLUTIONS S.A. de C.V.
 * "El factor de cambio en tu tecnología"
 *
 * @descripcion    Interfaz de autenticación dual (Login y Registro) con validación de fuerza de contraseña.
 * @autor          Rubén Alejandro Nolasco Ruiz
 * @autorizador    Rubén Alejandro Nolasco Ruiz
 * @prueba         Diego Miguel Hernandez Fabela  
 * @mantenimiento  Ghael Garcia Manjarrez 
 * @version        1.0.0
 * @creado         11/04/2026
 * @modificado     11/04/2026
 *
 * @cambios
 * Fecha       | Autor             | Descripción
 * ------------|-------------------|------------------------------------------
 * 11/04/2026  | Rubén Alejandro   | Implementación de vista de Login/Registro con validaciones JS.
 * 11/04/2026  | Rubén Alejandro   | Estandarización de prólogo según manual GAMA-MPL-03.
 */
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - G.A.M.A Solutions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/gama-login.css">
    <script src="js/auth-tabs.js" defer></script>
    <script>
        // If already authenticated, redirect to dashboard
        (function() {
            if (localStorage.getItem('auth_token')) {
                window.location.href = '/dashboard';
            }
        })();
    </script>
</head>
<body class="auth-page">
    <!-- Left Side - Branding -->
    <div class="auth-branding">
        <div class="branding-content">
            <div class="branding-logo">
                <img src="{{ asset('img/gama-logo.png') }}" alt="G.A.M.A Solutions" class="logo-image">
            </div>
            <h1 class="branding-title">"El factor de cambio en tu tecnología"</h1>
            <p class="branding-subtitle">
                Sistemas modulares diseñados para evolucionar al ritmo de su demanda
            </p>

            <div class="branding-features">
                <div class="branding-feature">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span>Ética y Resguardo de Activos</span>
                </div>
                <div class="branding-feature">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span>Manejo de datos en tiempo real y análisis</span>
                </div>
                <div class="branding-feature">
                    <div class="feature-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <span>Fácil de utilizar</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Form -->
    <div class="auth-form-container">
        <div class="auth-form-wrapper">
            <div class="form-header">
                <h1>Bienvenido de nuevo</h1>
                <p>Ingresa tus credenciales para acceder a tu cuenta</p>
            </div>

            <!-- Login Form -->
            <form class="auth-form active" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Número de empleado</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-input" id="loginUsername" placeholder="Ingresa tu número de empleado" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-input" id="loginPassword" placeholder="Ingresa tu contraseña" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Código de verificación</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-shield-alt input-icon"></i>
                        <input type="text" class="form-input" id="captchaCode" placeholder="Ingresa el código" required>
                    </div>
                    <img id="captchaImage" src="" alt="CAPTCHA" style="margin-top:8px;cursor:pointer;border:1px solid var(--gama-gris-300);border-radius:4px;max-width:200px;" onclick="loadCaptcha()">
                </div>

                <div id="loginError" class="form-error" style="display:none;color:var(--gama-rojo-500, #dc2626);font-size:13px;margin-bottom:12px;text-align:center;"></div>

                <button type="submit" class="btn btn-primary btn-lg">Iniciar sesión</button>

            </form>

        </div>

        <p class="copyright">
            Copyright &copy; 2026 G.A.M.A Solutions. Todos los derechos reservados.
        </p>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            var input = document.getElementById(inputId);
            var icon = input.parentElement.querySelector('.password-toggle i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Calculate password strength
        function calculatePasswordStrength(password) {
            var strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;
            return strength;
        }

        function updatePasswordStrength(inputId) {
            var input = document.getElementById(inputId);
            var passwordStrength = input.parentElement.parentElement.querySelector('.password-strength');
            if (!passwordStrength) return;
            var bars = passwordStrength.querySelectorAll('.strength-bar');
            var strength = calculatePasswordStrength(input.value);
            bars.forEach(function(bar) { bar.classList.remove('weak', 'medium', 'strong'); });
            var className = strength <= 2 ? 'weak' : strength <= 4 ? 'medium' : 'strong';
            var filledBars = strength <= 2 ? Math.min(strength, 2) : strength <= 4 ? strength - 2 : 4;
            for (var i = 0; i < filledBars; i++) bars[i].classList.add(className);
        }

        function toggleCheckbox(wrapper) {
            wrapper.querySelector('.checkbox').classList.toggle('checked');
        }

        // Password strength listener
        var registerPw = document.getElementById('registerPassword');
        if (registerPw) {
            registerPw.addEventListener('input', function() { updatePasswordStrength('registerPassword'); });
        }

        // === Login handler ===
        function loadCaptcha() {
            var img = document.getElementById('captchaImage');
            img.src = '/api/v1/auth/captcha?' + Date.now();
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadCaptcha();

            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var username = document.getElementById('loginUsername').value;
                var password = document.getElementById('loginPassword').value;
                var captchaCode = document.getElementById('captchaCode').value;
                document.getElementById('loginError').style.display = 'none';
                var btn = this.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.textContent = 'Ingresando...';

                fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '' },
                    body: JSON.stringify({ username: username, password: password, captchaCode: captchaCode })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success && data.data && data.data.accessToken) {
                        localStorage.setItem('auth_token', data.data.accessToken);
                        localStorage.setItem('auth_user', JSON.stringify(data.data.user || {}));
                        document.cookie = 'sam_token=' + data.data.accessToken + '; path=/; max-age=86400; SameSite=Lax';
                        window.location.href = data.data.redirectUrl || '/dashboard';
                    } else {
                        document.getElementById('captchaCode').value = '';
                        document.getElementById('loginError').textContent = data.message || 'Error al iniciar sesión';
                        document.getElementById('loginError').style.display = 'block';
                        loadCaptcha();
                    }
                })
                .catch(function() {
                    document.getElementById('loginError').textContent = 'Error de conexión con el servidor.';
                    document.getElementById('loginError').style.display = 'block';
                    loadCaptcha();
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.textContent = 'Iniciar sesión';
                });
            });
        });
    </script>
</body>
</html>
