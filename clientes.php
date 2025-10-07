<?php
include 'header.php';

// Variables para el manejo de mensajes
$mensaje = '';

// --- Lógica para Crear y Actualizar Clientes ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validación de datos (ejemplo simple)
    $cuit = trim($_POST['cuit']);
    $razon_social = trim($_POST['razon_social']);
    if (empty($cuit) || empty($razon_social)) {
        $mensaje = '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4">El CUIT y la Razón Social son obligatorios.</div>';
    } else {
        $id = $_POST['id'] ?? null;
        $params = [
            'cuit' => $cuit,
            'razon_social' => $razon_social,
            'nombre_fantasia' => $_POST['nombre_fantasia'],
            'tipo_cliente' => $_POST['tipo_cliente'],
            'email' => $_POST['email'],
            'telefono' => $_POST['telefono'],
            'direccion' => $_POST['direccion'],
            'localidad' => $_POST['localidad'],
            'provincia' => $_POST['provincia'],
            'contacto_nombre' => $_POST['contacto_nombre'],
            'contacto_puesto' => $_POST['contacto_puesto']
        ];

        try {
            if ($_POST['action'] === 'create') {
                $sql = "INSERT INTO clientes (cuit, razon_social, nombre_fantasia, tipo_cliente, email, telefono, direccion, localidad, provincia, contacto_nombre, contacto_puesto) VALUES (:cuit, :razon_social, :nombre_fantasia, :tipo_cliente, :email, :telefono, :direccion, :localidad, :provincia, :contacto_nombre, :contacto_puesto)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $mensaje = '<div class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-4">Cliente creado exitosamente.</div>';
            } elseif ($_POST['action'] === 'update' && $id) {
                $params['id'] = $id;
                $sql = "UPDATE clientes SET cuit = :cuit, razon_social = :razon_social, nombre_fantasia = :nombre_fantasia, tipo_cliente = :tipo_cliente, email = :email, telefono = :telefono, direccion = :direccion, localidad = :localidad, provincia = :provincia, contacto_nombre = :contacto_nombre, contacto_puesto = :contacto_puesto WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $mensaje = '<div class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-4">Cliente actualizado exitosamente.</div>';
            }
        } catch (PDOException $e) {
            $mensaje = '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// --- Lógica para Eliminar Cliente ---
// Nota: Aquí se maneja la eliminación real DESPUÉS de la confirmación por parte del usuario.
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = '<div class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-4">Cliente eliminado exitosamente.</div>';
    } catch (PDOException $e) {
        $mensaje = '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4">Error al eliminar cliente. Es posible que tenga contratos asociados.</div>';
    }
}


// --- Lógica de Búsqueda y Paginación ---
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];
if (!empty($search)) {
    $whereClause = "WHERE razon_social LIKE ? OR cuit LIKE ? OR nombre_fantasia LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Contar total de registros para paginación
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes $whereClause");
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Obtener registros para la página actual
$sql = "SELECT * FROM clientes $whereClause ORDER BY razon_social LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

?>

<title>Gestión de Clientes | <?= APP_NAME ?></title>

<!-- Contenedor principal con animación de entrada -->
<div class="animate-slide-down">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold text-white">Clientes</h1>
        <!-- Botón con transición de escala y sombra al hover -->
        <button onclick="openModal()" class="bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] text-white font-bold py-2 px-4 rounded-lg flex items-center transition duration-300 ease-in-out transform hover:scale-[1.05] hover:shadow-xl">
            <i class="fas fa-plus mr-2"></i> Nuevo Cliente
        </button>
    </div>
</div>

<!-- Mensajes con animación de fade-in -->
<?php if ($mensaje): ?>
    <div class="animate-fade-in">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<!-- Contenedor de la tabla con animación de entrada y leve delay -->
<div class="bg-[var(--bg-dark-2)] p-6 rounded-lg shadow-lg animate-slide-down" style="animation-delay: 0.1s;">
    <!-- Buscador -->
    <form method="GET" action="clientes.php" class="mb-4">
        <div class="relative">
            <input type="text" name="search" placeholder="Buscar por Razón Social, CUIT..." value="<?= htmlspecialchars($search) ?>"
                   class="w-full bg-[var(--bg-dark)] border border-[var(--border-color)] rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-[var(--accent-color)] transition duration-200">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
        </div>
    </form>

    <!-- Tabla de Clientes -->
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-[var(--border-color)]">
                    <th class="p-3">Razón Social</th>
                    <th class="p-3">CUIT</th>
                    <th class="p-3">Teléfono</th>
                    <th class="p-3">Contacto</th>
                    <th class="p-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clientes)): ?>
                    <tr><td colspan="5" class="p-4 text-center text-gray-400">No se encontraron clientes.</td></tr>
                <?php else: ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <!-- Fila con transición al hover -->
                        <tr class="border-b border-[var(--border-color)] hover:bg-[var(--bg-dark)] transition duration-150 ease-in-out">
                            <td class="p-3"><?= htmlspecialchars($cliente['razon_social']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($cliente['cuit']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($cliente['telefono']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($cliente['contacto_nombre']) ?></td>
                            <td class="p-3 text-right">
                                <!-- Botones de acción con hover de escala -->
                                <button onclick='openModal(<?= json_encode($cliente) ?>)' class="text-blue-400 hover:text-blue-300 mr-3 transition transform hover:scale-125 duration-150"><i class="fas fa-edit"></i></button>
                                <button onclick='openConfirmModal(<?= $cliente['id'] ?>)' class="text-red-400 hover:text-red-300 transition transform hover:scale-125 duration-150"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación con transiciones -->
    <div class="mt-4 flex justify-between items-center">
        <span class="text-gray-400">Mostrando <?= count($clientes) ?> de <?= $total_rows ?> registros</span>
        <div class="flex">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-[var(--bg-dark)] hover:bg-gray-700 rounded-md mr-2 transition duration-150">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 rounded-md transition duration-150 <?= $i == $page ? 'bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] text-white' : 'bg-[var(--bg-dark)] hover:bg-gray-700' ?> mr-2"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                   <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-[var(--bg-dark)] hover:bg-gray-700 rounded-md transition duration-150">&raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Modal para Crear/Editar Cliente (con transiciones) -->
<!-- Clases iniciales: opacity-0, pointer-events-none para la transición -->
<div id="clienteModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-start justify-center p-4 transition-opacity duration-300 opacity-0 pointer-events-none z-50">
    <!-- Contenido del modal con transición de escala -->
    <div id="modalContent" class="bg-[var(--bg-dark-2)] rounded-lg shadow-2xl w-full max-w-3xl relative transition duration-300 transform scale-90 mt-10">
        <button onclick="closeModal()" class="absolute top-0 right-0 mt-4 mr-4 text-gray-400 hover:text-white transition transform hover:scale-110">&times;</button>
        <div class="p-8">
            <h2 id="modalTitle" class="text-2xl font-bold text-white mb-6">Nuevo Cliente</h2>
            <form id="clienteForm" method="POST" action="clientes.php">
                <input type="hidden" name="id" id="clienteId">
                <input type="hidden" name="action" id="formAction" value="create">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Razón Social *</label>
                        <input type="text" name="razon_social" id="razon_social" class="mt-1 w-full input-style" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">CUIT *</label>
                        <input type="text" name="cuit" id="cuit" class="mt-1 w-full input-style" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Nombre de Fantasía</label>
                        <input type="text" name="nombre_fantasia" id="nombre_fantasia" class="mt-1 w-full input-style">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Tipo de Cliente</label>
                        <select name="tipo_cliente" id="tipo_cliente" class="mt-1 w-full input-style">
                            <option value="REGULAR">REGULAR</option>
                            <option value="ESPECIAL">ESPECIAL</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Email</label>
                        <input type="email" name="email" id="email" class="mt-1 w-full input-style">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="mt-1 w-full input-style">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-400">Dirección</label>
                        <input type="text" name="direccion" id="direccion" class="mt-1 w-full input-style">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Localidad</label>
                        <input type="text" name="localidad" id="localidad" class="mt-1 w-full input-style">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Provincia</label>
                        <input type="text" name="provincia" id="provincia" class="mt-1 w-full input-style">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Nombre Contacto</label>
                        <input type="text" name="contacto_nombre" id="contacto_nombre" class="mt-1 w-full input-style">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-400">Puesto Contacto</label>
                        <input type="text" name="contacto_puesto" id="contacto_puesto" class="mt-1 w-full input-style">
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <!-- Botones con hover de escala -->
                    <button type="button" onclick="closeModal()" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg mr-2 transition transform hover:scale-[1.05]">Cancelar</button>
                    <button type="submit" class="bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] text-white font-bold py-2 px-4 rounded-lg transition transform hover:scale-[1.05]">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación (Custom, reemplaza alert/confirm) -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center p-4 transition-opacity duration-300 opacity-0 pointer-events-none z-50">
    <div id="confirmContent" class="bg-[var(--bg-dark-2)] rounded-lg shadow-2xl w-full max-w-sm relative p-6 transition duration-300 transform scale-90">
        <h3 class="text-xl font-bold text-red-400 mb-4">Confirmar Eliminación</h3>
        <p class="text-gray-300 mb-6">¿Estás seguro de que quieres eliminar este cliente? Esta acción no se puede deshacer.</p>
        <div class="flex justify-end">
            <button onclick="closeConfirmModal()" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg mr-2 transition transform hover:scale-[1.05]">Cancelar</button>
            <a id="confirmDeleteLink" href="#" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition transform hover:scale-[1.05]">Eliminar</a>
        </div>
    </div>
</div>

<style>
    /* VARIABLES (Mantienes las tuyas) */
    :root {
        --bg-dark: #1a202c;
        --bg-dark-2: #2d3748;
        --text-light: #e2e8f0;
        --text-gray: #a0aec0;
        --border-color: #4a5568;
        --accent-color: #38b2ac;
        --accent-color-hover: #319795;
    }

    /* ESTILOS DE INPUT Y SELECT con transición al focus */
    .input-style {
        background-color: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        color: var(--text-light);
        transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
    }
    .input-style:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(56, 178, 172, 0.5); /* ring-2 */
        border-color: var(--accent-color);
    }

    /* --- KEYFRAMES PERSONALIZADOS --- */
    
    /* Para el contenido principal */
    @keyframes slide-down-quick {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    .animate-slide-down {
        animation: slide-down-quick 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
    }

    /* Para mensajes de éxito/error */
    @keyframes fade-in {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
    
    .animate-fade-in {
        animation: fade-in 0.3s ease-in-out forwards;
    }
</style>

<script>
    const modal = document.getElementById('clienteModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    const clienteForm = document.getElementById('clienteForm');
    const clienteId = document.getElementById('clienteId');
    const formAction = document.getElementById('formAction');
    
    // Elementos del modal de confirmación
    const confirmModal = document.getElementById('confirmModal');
    const confirmContent = document.getElementById('confirmContent');
    const confirmDeleteLink = document.getElementById('confirmDeleteLink');


    // --- Funciones para Modal de Crear/Editar ---
    function openModal(cliente = null) {
        clienteForm.reset();
        if (cliente) {
            modalTitle.innerText = 'Editar Cliente';
            formAction.value = 'update';
            clienteId.value = cliente.id;
            document.getElementById('razon_social').value = cliente.razon_social;
            document.getElementById('cuit').value = cliente.cuit;
            document.getElementById('nombre_fantasia').value = cliente.nombre_fantasia;
            document.getElementById('tipo_cliente').value = cliente.tipo_cliente;
            document.getElementById('email').value = cliente.email;
            document.getElementById('telefono').value = cliente.telefono;
            document.getElementById('direccion').value = cliente.direccion;
            document.getElementById('localidad').value = cliente.localidad;
            document.getElementById('provincia').value = cliente.provincia;
            document.getElementById('contacto_nombre').value = cliente.contacto_nombre;
            document.getElementById('contacto_puesto').value = cliente.contacto_puesto;
        } else {
            modalTitle.innerText = 'Nuevo Cliente';
            formAction.value = 'create';
            clienteId.value = '';
        }

        // Aplicar clases para mostrar con animación
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        modalContent.classList.remove('scale-90');
        modalContent.classList.add('scale-100');
    }

    function closeModal() {
        // Aplicar clases para esconder con animación
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-90');
        
        // Esperar la transición antes de deshabilitar la interacción
        setTimeout(() => {
             modal.classList.add('pointer-events-none');
        }, 300);
    }
    
    // --- Funciones para Modal de Confirmación ---
    function openConfirmModal(id) {
        // Establecer el enlace de eliminación con el ID
        confirmDeleteLink.href = 'clientes.php?delete_id=' + id;
        
        // Aplicar clases para mostrar con animación
        confirmModal.classList.remove('opacity-0', 'pointer-events-none');
        confirmModal.classList.add('opacity-100');
        confirmContent.classList.remove('scale-90');
        confirmContent.classList.add('scale-100');
    }

    function closeConfirmModal() {
        // Aplicar clases para esconder con animación
        confirmModal.classList.remove('opacity-100');
        confirmModal.classList.add('opacity-0');
        confirmContent.classList.remove('scale-100');
        confirmContent.classList.add('scale-90');
        
        // Esperar la transición antes de deshabilitar la interacción
        setTimeout(() => {
             confirmModal.classList.add('pointer-events-none');
        }, 300);
    }


    // Cierre de modales al hacer clic fuera
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
        if (event.target == confirmModal) {
            closeConfirmModal();
        }
    }
</script>

<?php include 'footer.php'; ?>
