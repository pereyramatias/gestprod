
<?php
// Iniciar sesión para todas las páginas
session_start();

// --- Configuración de la Base de Datos ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestprod');
define('DB_USER', 'root'); // Cambia esto por tu usuario de DB
define('DB_PASS', 'admin'); // Cambia esto por tu contraseña de DB
define('DB_CHARSET', 'utf8mb4');

// --- Configuración de la Aplicación ---
define('APP_NAME', 'GESTPROD');
define('BASE_URL', 'http://localhost/gestprod/'); // URL base de tu proyecto

// --- Conexión a la Base de Datos con PDO ---
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En un entorno de producción, no mostrarías el error detallado.
    // Lo registrarías en un archivo de log y mostrarías un mensaje genérico.
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// --- Funciones de Utilidad ---

/**
 * Redirecciona a una página.
 * @param string $url La URL a la que redireccionar.
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Verifica si un usuario ha iniciado sesión.
 * Si no, lo redirecciona a la página de login.
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}
?>
