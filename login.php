<?php
require_once 'config.php';

// Si el usuario ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// --- Lógica de Registro ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } else {
        try {
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'El email ya está registrado.';
            } else {
                // Hashear la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password]);
                $success = '¡Registro exitoso! Ahora puedes iniciar sesión.';
            }
        } catch (PDOException $e) {
            $error = "Error en el registro: " . $e->getMessage();
        }
    }
}


// --- Lógica de Login ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email y contraseña son obligatorios.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Inicio de sesión exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                redirect('dashboard.php');
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = "Error en el inicio de sesión: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --bg-dark: #1a202c;
            --bg-dark-2: #2d3748;
            --text-light: #e2e8f0;
            --text-gray: #a0aec0;
            --border-color: #4a5568;
            --accent-color: #38b2ac;
            --accent-color-hover: #319795;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            /* APLICANDO Suavizado de fuente y asegurando fuente Inter */
            -webkit-font-smoothing: antialiased; 
            -moz-osx-font-smoothing: grayscale; 
            font-family: 'Inter', sans-serif;
        }

        /* --- ANIMACIONES PERSONALIZADAS --- */
        
        /* 1. Animación de entrada de la tarjeta (slide-down) */
        @keyframes slide-down {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-down {
            animation: slide-down 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        /* 2. Animación de fade-in para mensajes */
        @keyframes fade-in {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        
        .animate-fade-in {
            animation: fade-in 0.4s ease-in-out forwards;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    
    <div class="w-full max-w-md mx-auto animate-slide-down">
        <div class="text-center mb-8">
            <!-- LOGO: Reemplazando el texto del título por la imagen -->
            <img src="gestprod.png" 
                 alt="Logo GESTPROD" 
                 class="h-45 mx-auto mb-2"
            >
            <p class="text-gray-400">Gestión de Productos y Contratos</p>
        </div>
        
        <div class="bg-[var(--bg-dark-2)] rounded-lg shadow-xl p-8">
            <div class="flex border-b border-[var(--border-color)] mb-6">
                <button id="login-tab" class="flex-1 py-2 text-center font-semibold text-white border-b-2 border-[var(--accent-color)] transition duration-200 ease-in-out transform hover:scale-[1.02]">Iniciar Sesión</button>
                <button id="register-tab" class="flex-1 py-2 text-center font-semibold text-[var(--text-gray)] transition duration-200 ease-in-out transform hover:scale-[1.02]">Registrarse</button>
            </div>

            <form id="login-form" method="POST" action="login.php" class="space-y-6 transition-opacity duration-300">
                <input type="hidden" name="login" value="1">
                <div>
                    <label for="login-email" class="text-sm font-medium text-[var(--text-gray)]">Email</label>
                    <input type="email" name="email" id="login-email" required class="w-full mt-1 px-3 py-2 bg-[var(--bg-dark)] border border-[var(--border-color)] rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--accent-color)] transition duration-200">
                </div>
                <div>
                    <label for="login-password" class="text-sm font-medium text-[var(--text-gray)]">Contraseña</label>
                    <input type="password" name="password" id="login-password" required class="w-full mt-1 px-3 py-2 bg-[var(--bg-dark)] border border-[var(--border-color)] rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--accent-color)] transition duration-200">
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] rounded-md text-white font-semibold transition duration-300 ease-in-out transform hover:scale-[1.02]">
                    Ingresar
                </button>
            </form>

            <form id="register-form" method="POST" action="login.php" class="space-y-6 hidden transition-opacity duration-300">
                <input type="hidden" name="register" value="1">
                <div>
                    <label for="register-name" class="text-sm font-medium text-[var(--text-gray)]">Nombre Completo</label>
                    <input type="text" name="name" id="register-name" required class="w-full mt-1 px-3 py-2 bg-[var(--bg-dark)] border border-[var(--border-color)] rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--accent-color)] transition duration-200">
                </div>
                <div>
                    <label for="register-email" class="text-sm font-medium text-[var(--text-gray)]">Email</label>
                    <input type="email" name="email" id="register-email" required class="w-full mt-1 px-3 py-2 bg-[var(--bg-dark)] border border-[var(--border-color)] rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--accent-color)] transition duration-200">
                </div>
                <div>
                    <label for="register-password" class="text-sm font-medium text-[var(--text-gray)]">Contraseña</label>
                    <input type="password" name="password" id="register-password" required class="w-full mt-1 px-3 py-2 bg-[var(--bg-dark)] border border-[var(--border-color)] rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--accent-color)] transition duration-200">
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] rounded-md text-white font-semibold transition duration-300 ease-in-out transform hover:scale-[1.02]">
                    Crear Cuenta
                </button>
            </form>
            
            <?php if ($error): ?>
                <div class="mt-4 text-center text-red-400 bg-red-900/50 p-3 rounded-md animate-fade-in"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="mt-4 text-center text-green-400 bg-green-900/50 p-3 rounded-md animate-fade-in"><?= $success ?></div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        const loginTab = document.getElementById('login-tab');
        const registerTab = document.getElementById('register-tab');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        loginTab.addEventListener('click', () => {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            loginTab.classList.add('border-[var(--accent-color)]', 'text-white');
            loginTab.classList.remove('text-[var(--text-gray)]');
            registerTab.classList.remove('border-[var(--accent-color)]', 'text-white');
            registerTab.classList.add('text-[var(--text-gray)]');
        });

        registerTab.addEventListener('click', () => {
            registerForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
            registerTab.classList.add('border-[var(--accent-color)]', 'text-white');
            registerTab.classList.remove('text-[var(--text-gray)]');
            loginTab.classList.remove('border-[var(--accent-color)]', 'text-white');
            loginTab.classList.add('text-[var(--text-gray)]');
        });
    </script>
</body>
</html>
