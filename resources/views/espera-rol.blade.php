{{--
/**
 * @descripcion    Vista de espera de asignación de rol para usuarios SAM autenticados sin rol local.
 * @autor          Antigravity <support@google.com>
 * @autorizador    Rubén Alejandro Nolasco Ruiz <correo@dominio.com>
 * @prueba         Antigravity <support@google.com>
 * @mantenimiento  Antigravity <support@google.com>
 * @version        1.0.0
 * @creado         2026-05-24
 * @modificado     2026-05-24
 */
--}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espera de Rol - Sistema de Control de Aulas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --gama-background: #0b0f19;
            --gama-card-bg: rgba(255, 255, 255, 0.03);
            --gama-border: rgba(255, 255, 255, 0.08);
            --gama-primary: #3b82f6;
            --gama-primary-hover: #2563eb;
            --gama-text-primary: #f3f4f6;
            --gama-text-secondary: #9ca3af;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', sans-serif;
            background-color: var(--gama-background);
            color: var(--gama-text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Glows */
        .ambient-glow-1 {
            position: absolute;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0) 70%);
            top: 15%;
            left: 20%;
            pointer-events: none;
            z-index: 1;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, rgba(99, 102, 241, 0) 70%);
            bottom: 10%;
            right: 15%;
            pointer-events: none;
            z-index: 1;
        }

        .container {
            width: 100%;
            max-width: 520px;
            padding: 24px;
            position: relative;
            z-index: 10;
        }

        .card {
            background-color: var(--gama-card-bg);
            border: 1px solid var(--gama-border);
            border-radius: 20px;
            padding: 48px 40px;
            text-align: center;
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.6s ease-out;
        }

        .icon-container {
            position: relative;
            width: 96px;
            height: 96px;
            margin: 0 auto 32px auto;
        }

        .icon-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px dashed rgba(59, 130, 246, 0.3);
            animation: spin 8s linear infinite;
        }

        .icon-box {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 80px;
            height: 80px;
            background-color: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-box i {
            font-size: 32px;
            color: var(--gama-primary);
            animation: pulse 2s ease-in-out infinite;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 16px;
            color: var(--gama-text-primary);
        }

        .description {
            font-size: 15px;
            line-height: 1.6;
            color: var(--gama-text-secondary);
            margin-bottom: 32px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--gama-border);
            color: var(--gama-text-primary);
        }

        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.08);
                opacity: 0.8;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="container">
        <div class="card">
            <div class="icon-container">
                <div class="icon-ring"></div>
                <div class="icon-box">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>

            <h1 class="title">Espera de Asignación de Rol</h1>
            <p class="description">
                Tu cuenta de SAM ha sido vinculada correctamente, pero aún no tienes un rol asignado en este sistema local. Por favor, solicita a un administrador que te asigne el rol correspondiente (Docente o Administrador) para poder acceder.
            </p>

            <button id="logoutBtn" class="btn btn-outline">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar sesión
            </button>
        </div>
    </div>

    <script>
        function clearSession() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            document.cookie = 'sam_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT; SameSite=Lax';
        }

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            var btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cerrando sesión...';

            fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                }
            })
            .then(function() {
                clearSession();
                window.location.href = '/';
            })
            .catch(function() {
                clearSession();
                window.location.href = '/';
            });
        });
    </script>
</body>
</html>
