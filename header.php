<?php
// Este archivo se incluye en todas las páginas seguras.
require_once 'config.php';
require_login(); // Asegura que el usuario esté logueado.

$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- El título será establecido en cada página -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            /* Corregir el anti-aliasing y asegurar la fuente Inter */
            -webkit-font-smoothing: antialiased; 
            -moz-osx-font-smoothing: grayscale; 
            font-family: 'Inter', sans-serif;
        }
        .sidebar-link {
            transition: all 0.2s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background-color: var(--accent-color);
            color: white;
            transform: translateX(5px);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
        }
        
        /* Estilos específicos para la barra lateral en móvil */
        .sidebar-mobile-hidden {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            position: fixed; /* Fija la posición en móvil */
            z-index: 40; /* Por debajo del modal, pero por encima del contenido */
            height: 100vh;
        }
        .sidebar-mobile-visible {
            transform: translateX(0);
        }
    </style>
</head>
<!-- La clase 'flex' ahora solo se aplica en pantallas grandes (lg:) -->
<body class="h-screen overflow-hidden lg:flex">
    
    <!-- Botón de Menú (Solo en Móvil) -->
    <button id="menu-toggle" class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] rounded-lg text-white transition duration-300">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Sidebar -->
    <!-- Se añadieron clases responsive para ocultar en móvil por defecto -->
    <aside id="sidebar" class="w-64 bg-[var(--bg-dark-2)] shadow-lg flex-shrink-0 flex-col 
                                lg:flex lg:relative 
                                sidebar-mobile-hidden lg:translate-x-0">
        <div class="p-6 text-center border-b border-[var(--border-color)]">
            <!-- CORRECCIÓN: El enlace ahora apunta a 'dashboard.php' -->
            <a href="dashboard.php" class="block w-full text-center py-2">
                <img src="gestprod.png" 
                     alt="Logo GESTPROD" 
                     class="h-16 mx-auto transition duration-300 hover:scale-105"
                >
            </a>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="sidebar-link flex items-center p-3 rounded-lg <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt w-6 mr-3"></i> Dashboard
            </a>
            <a href="clientes.php" class="sidebar-link flex items-center p-3 rounded-lg <?= $current_page == 'clientes.php' ? 'active' : '' ?>">
                <i class="fas fa-users w-6 mr-3"></i> Clientes
            </a>
            <a href="contratos.php" class="sidebar-link flex items-center p-3 rounded-lg <?= $current_page == 'contratos.php' ? 'active' : '' ?>">
                <i class="fas fa-file-signature w-6 mr-3"></i> Contratos
            </a>
        </nav>
        <div class="p-4 border-t border-[var(--border-color)]">
            <div class="flex items-center">
                <i class="fas fa-user-circle text-2xl text-[var(--text-gray)] mr-3"></i>
                <div>
                    <p class="font-semibold text-white"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                    <a href="logout.php" class="text-sm text-[var(--accent-color)] hover:underline">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Overlay para cuando el menú está abierto en móvil -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>


    <!-- Main Content -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto">
        
        <!-- Aquí estaba el div container, lo mantengo -->
        <div class="container mx-auto px-6 py-8">
            <!-- El contenido de dashboard.php y clientes.php debe usar w-full internamente -->
            

    <script>
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menu-toggle');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('sidebar-mobile-hidden');
            sidebar.classList.toggle('sidebar-mobile-visible');
            overlay.classList.toggle('hidden');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
        
        // Cierra el menú si se hace click en un enlace (útil en móvil)
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (!sidebar.classList.contains('sidebar-mobile-hidden')) {
                    toggleSidebar();
                }
            });
        });

        // Asegura que la barra lateral siempre esté visible en desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) { // 1024px es el breakpoint 'lg' de Tailwind
                sidebar.classList.remove('sidebar-mobile-hidden', 'sidebar-mobile-visible');
                overlay.classList.add('hidden');
            }
        });

    </script>
