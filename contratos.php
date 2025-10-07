<?php
include 'config.php';
// =======================================================
// === 1. LÓGICA DE PROCESAMIENTO (DEBE IR PRIMERO) ===
// =======================================================

// Asumimos que 'config.php' ya fue incluido en 'header.php'
// Si la conexión $pdo no está disponible aquí, DEBES incluir 'config.php'
// manualmente antes de 'header.php', o asegurarte que 'header.php' no 
// envíe ninguna salida visible antes de inicializar $pdo.
// Por ahora, confiamos en que $pdo está inicializado sin enviar output.

$mensaje = '';

// --- Lógica para Crear y Actualizar Contratos ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $cliente_id = $_POST['cliente_id'];
    $fecha_contrato = $_POST['fecha_contrato'];
    
    if (empty($cliente_id) || empty($fecha_contrato)) {
        // En este caso, NO se hace redirección, solo se almacena el mensaje.
        $mensaje = '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4">El Cliente y la Fecha son obligatorios.</div>';
    } else {
        $id = $_POST['id'] ?? null;
        $params = [
            'cliente_id' => $cliente_id,
            'fecha_contrato' => $fecha_contrato,
            'producto' => $_POST['producto'],
            'cosecha' => $_POST['cosecha'],
            'toneladas' => (float)$_POST['toneladas'],
            'precio' => (float)$_POST['precio'],
            'lugar_entrega' => $_POST['lugar_entrega'],
            'fecha_entrega' => $_POST['fecha_entrega'],
            'ANIO' => $_POST['anio'], // Usando ANIO
        ];

        try {
            if ($_POST['action'] === 'create') {
                $sql = "INSERT INTO contratos (cliente_id, fecha_contrato, producto, cosecha, toneladas, precio, lugar_entrega, fecha_entrega, ANIO) VALUES (:cliente_id, :fecha_contrato, :producto, :cosecha, :toneladas, :precio, :lugar_entrega, :fecha_entrega, :ANIO)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                // ESTA LLAMADA A HEADER ES SEGURA AHORA
                header("Location: contratos.php?msg=created"); 
                exit();
            } elseif ($_POST['action'] === 'update' && $id) {
                $params['id'] = $id;
                $sql = "UPDATE contratos SET cliente_id = :cliente_id, fecha_contrato = :fecha_contrato, producto = :producto, cosecha = :cosecha, toneladas = :toneladas, precio = :precio, lugar_entrega = :lugar_entrega, fecha_entrega = :fecha_entrega, ANIO = :ANIO WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                // ESTA LLAMADA A HEADER ES SEGURA AHORA
                header("Location: contratos.php?msg=updated");
                exit();
            }
        } catch (PDOException $e) {
            $mensaje = '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// --- Lógica para Eliminar Contrato ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contratos WHERE id = ?");
        $stmt->execute([$id]);
        // ESTA LLAMADA A HEADER ES SEGURA AHORA
        header("Location: contratos.php?msg=deleted"); // <-- ¡Esta es la línea que fallaba!
        exit();
    } catch (PDOException $e) {
        // En este caso, NO se hace redirección, solo se almacena el mensaje.
        $mensaje = '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4">Error al eliminar contrato.</div>';
    }
}

// =======================================================
// === 2. INCLUSIÓN DE HEADER Y OBTENCIÓN DE DATOS ===
// =======================================================

// Ahora incluimos el header que genera la salida HTML
include 'header.php';

// Obtener lista de clientes para el dropdown (Necesita $pdo)
try {
    $clientes_stmt = $pdo->query("SELECT id, razon_social FROM clientes ORDER BY razon_social");
    $lista_clientes = $clientes_stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener la lista de clientes: " . $e->getMessage());
}

// --- Mostrar mensajes después de redirección (sin cambios) ---
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $mensaje = '<div class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-4">Contrato creado exitosamente.</div>';
            break;
        case 'updated':
            $mensaje = '<div class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-4">Contrato actualizado exitosamente.</div>';
            break;
        case 'deleted':
            $mensaje = '<div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4">Contrato eliminado exitosamente.</div>';
            break;
    }
}

// --- Lógica de Filtros y Obtención de Contratos (sin cambios) ---
$filtro_cliente = $_GET['filtro_cliente'] ?? '';
$filtro_producto = $_GET['filtro_producto'] ?? '';
$filtro_anio = $_GET['filtro_anio'] ?? '';

$whereClauses = [];
$params = [];
if (!empty($filtro_cliente)) {
    $whereClauses[] = "con.cliente_id = ?";
    $params[] = $filtro_cliente;
}
if (!empty($filtro_producto)) {
    $whereClauses[] = "con.producto LIKE ?";
    $params[] = "%$filtro_producto%";
}
if (!empty($filtro_anio)) {
    $whereClauses[] = "con.ANIO = ?"; // Usando ANIO
    $params[] = $filtro_anio;
}
$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$sql = "SELECT con.*, cli.razon_social FROM contratos con JOIN clientes cli ON con.cliente_id = cli.id $whereSql ORDER BY con.fecha_contrato DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contratos = $stmt->fetchAll();

?>

<title>Gestión de Contratos | <?= APP_NAME ?></title>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-semibold text-white">Contratos</h1>
    <button onclick="openModal()" class="bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] text-white font-bold py-2 px-4 rounded-lg flex items-center shadow-lg transform transition duration-150 hover:scale-105">
        <i class="fas fa-plus mr-2"></i> Nuevo Contrato
    </button>
</div>

<?= $mensaje ?>

<div class="bg-[var(--bg-dark-2)] p-6 rounded-xl shadow-2xl">
    <form method="GET" action="contratos.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end p-4 border-b border-[var(--border-color)]">
        <div>
            <label class="block text-sm font-medium text-gray-400">Cliente</label>
            <select name="filtro_cliente" class="w-full input-style mt-1">
                <option value="">Todos</option>
                <?php foreach($lista_clientes as $cliente_item): ?>
                    <option value="<?= $cliente_item['id'] ?>" <?= $filtro_cliente == $cliente_item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente_item['razon_social']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400">Producto</label>
            <input type="text" name="filtro_producto" value="<?= htmlspecialchars($filtro_producto) ?>" class="w-full input-style mt-1" placeholder="Trigo, Soja, etc.">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400">Año</label>
            <input type="number" name="filtro_anio" value="<?= htmlspecialchars($filtro_anio) ?>" class="w-full input-style mt-1" placeholder="Ej: 2025">
        </div>
        <div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-lg transform transition duration-150 hover:scale-[1.02]">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
        </div>
    </form>

    <div class="overflow-x-auto">
         <?php if (empty($contratos)): ?>
             <div class="p-6 text-center text-gray-500 border border-dashed border-[var(--border-color)] rounded-lg">
                 <i class="fas fa-box-open text-4xl mb-3"></i>
                 <p>No se encontraron contratos con los filtros aplicados. ¡Crea uno nuevo!</p>
             </div>
         <?php else: ?>
             <table class="w-full text-left table-auto">
                 <thead>
                     <tr class="border-b border-[var(--border-color)] uppercase text-gray-400 text-xs">
                         <th class="p-3">Fecha</th>
                         <th class="p-3">Cliente</th>
                         <th class="p-3">Producto / Cosecha</th>
                         <th class="p-3 text-right">Toneladas</th>
                         <th class="p-3 text-right">Precio</th>
                         <th class="p-3 text-right">Acciones</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php foreach ($contratos as $contrato): ?>
                         <tr class="border-b border-[var(--border-color)] hover:bg-[var(--bg-dark)] transition duration-100">
                             <td class="p-3 text-sm font-light"><?= date("d/m/Y", strtotime($contrato['fecha_contrato'])) ?></td>
                             <td class="p-3 font-semibold text-white"><?= htmlspecialchars($contrato['razon_social']) ?></td>
                             <td class="p-3 text-sm text-gray-300"><?= htmlspecialchars($contrato['producto']) ?> <span class="text-gray-500 text-xs">(<?= htmlspecialchars($contrato['cosecha']) ?>)</span></td>
                             <td class="p-3 text-right font-mono text-lime-400"><?= number_format($contrato['toneladas'], 2, ',', '.') ?> Tn</td>
                             <td class="p-3 text-right font-mono text-amber-400">$<?= number_format($contrato['precio'], 2, ',', '.') ?></td>
                             <td class="p-3 text-right whitespace-nowrap">
                                 <button title="Editar Contrato" onclick='openModal(<?= json_encode($contrato) ?>)' class="text-blue-400 hover:text-blue-300 mr-3 p-1 transition duration-150 hover:scale-110">
                                     <i class="fas fa-edit"></i>
                                 </button>
                                 <button title="Eliminar Contrato" onclick='showDeleteModal(<?= $contrato['id'] ?>)' class="text-red-400 hover:text-red-300 p-1 transition duration-150 hover:scale-110">
                                     <i class="fas fa-trash"></i>
                                 </button>
                             </td>
                         </tr>
                     <?php endforeach; ?>
                 </tbody>
             </table>
         <?php endif; ?>
     </div>
</div>

<div id="contratoModal" class="modal">
    <div class="bg-[var(--bg-dark-2)] rounded-xl shadow-2xl w-full max-w-3xl m-auto my-10 relative">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white text-3xl transition duration-150">&times;</button>
        <div class="p-8">
            <h2 id="modalTitle" class="text-3xl font-extrabold text-white border-b border-[var(--border-color)] pb-4 mb-6">Nuevo Contrato</h2>
            <form id="contratoForm" method="POST" action="contratos.php">
                <input type="hidden" name="id" id="contratoId">
                <input type="hidden" name="action" id="formAction" value="create">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Cliente <span class="text-red-500">*</span></label>
                        <select name="cliente_id" id="cliente_id" class="mt-1 w-full input-style" required>
                             <option value="">Seleccione un cliente</option>
                             <?php foreach($lista_clientes as $cliente_item): ?>
                                 <option value="<?= $cliente_item['id'] ?>"><?= htmlspecialchars($cliente_item['razon_social']) ?></option>
                             <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Fecha Contrato <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_contrato" id="fecha_contrato" class="mt-1 w-full input-style" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Producto</label>
                        <input type="text" name="producto" id="producto" class="mt-1 w-full input-style" placeholder="Ej: Trigo">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Cosecha</label>
                        <input type="text" name="cosecha" id="cosecha" class="mt-1 w-full input-style" placeholder="Ej: 2024-25">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Toneladas</label>
                        <input type="number" step="0.01" name="toneladas" id="toneladas" class="mt-1 w-full input-style" placeholder="Ej: 100.50">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-400">Precio (por Tn)</label>
                        <input type="number" step="0.01" name="precio" id="precio" class="mt-1 w-full input-style" placeholder="Ej: 250.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Lugar de Entrega</label>
                        <input type="text" name="lugar_entrega" id="lugar_entrega" class="mt-1 w-full input-style" placeholder="Ej: Puerto de Rosario">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Fecha de Entrega</label>
                        <input type="text" name="fecha_entrega" id="fecha_entrega" class="mt-1 w-full input-style" placeholder="Ej: Marzo - Abril">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-400">Año</label>
                        <input type="number" name="anio" id="anio" class="mt-1 w-full input-style" placeholder="Ej: 2025">
                    </div>
                 </div>
                <div class="mt-8 flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg mr-2 transition duration-150">Cancelar</button>
                    <button type="submit" class="bg-[var(--accent-color)] hover:bg-[var(--accent-color-hover)] text-white font-bold py-2 px-4 rounded-lg transition duration-150">Guardar Contrato</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="deleteConfirmModal" class="modal">
    <div class="bg-[var(--bg-dark-2)] rounded-xl shadow-2xl w-full max-w-sm m-auto my-auto relative p-6 text-center">
        <h3 class="text-xl font-bold text-red-500 mb-4 flex items-center justify-center"><i class="fas fa-exclamation-triangle mr-2"></i> Confirmar Eliminación</h3>
        <p class="text-gray-300 mb-6">¿Estás absolutamente seguro de que deseas eliminar este contrato? Esta acción es irreversible.</p>
        <div class="flex justify-center gap-4">
            <button onclick="hideDeleteModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 w-1/2">Cancelar</button>
            <a id="deleteConfirmLink" href="#" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 w-1/2">Eliminar</a>
        </div>
    </div>
</div>

<style>
    /* Estilos base para los modales */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1000; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgba(0,0,0,0.8); /* Fondo oscuro con opacidad */
        justify-content: center; /* Centrar contenido horizontalmente */
        align-items: center; /* Centrar contenido verticalmente */
    }
    .modal > div {
        /* Se ajustó la duración de 0.3s a 0.5s para un efecto más suave y consistente */
        animation: scale-up 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55); /* Animación más cheta */
    }
    @keyframes scale-up {
        from { transform: scale(0.8) translateY(-30px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }
    
    /* Estilos para inputs */
    .input-style {
        background-color: var(--bg-dark); 
        border: 1px solid var(--border-color);
        border-radius: 0.5rem; 
        padding: 0.75rem 1rem; 
        color: var(--text-light);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .input-style:focus { 
        outline: none; 
        box-shadow: 0 0 0 3px rgba(var(--accent-color-rgb), 0.5); /* Sombra de acento */
        border-color: var(--accent-color); 
    }
    /* Para que el calendario del input date sea oscuro */
    input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0.8); }

    /* Fix para el anti-aliasing (efecto "sierra") y asegurar la fuente */
    body {
        -webkit-font-smoothing: antialiased; /* Suavizado para Chrome/Safari */
        -moz-osx-font-smoothing: grayscale; /* Suavizado para Firefox */
        font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
    }
</style>

<script>
    // Variables para el Modal de Crear/Editar
    const contratoModal = document.getElementById('contratoModal');
    const modalTitle = document.getElementById('modalTitle');
    const contratoForm = document.getElementById('contratoForm');
    const contratoId = document.getElementById('contratoId');
    const formAction = document.getElementById('formAction');

    // Variables para el Modal de Eliminación
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    const deleteConfirmLink = document.getElementById('deleteConfirmLink');

    // --- Funciones del Modal de Crear/Editar ---

    function openModal(contrato = null) {
        contratoForm.reset();
        if (contrato) {
            modalTitle.innerText = 'Editar Contrato';
            formAction.value = 'update';
            contratoId.value = contrato.id;
            // Rellenar campos
            document.getElementById('cliente_id').value = contrato.cliente_id;
            document.getElementById('fecha_contrato').value = contrato.fecha_contrato;
            document.getElementById('producto').value = contrato.producto;
            document.getElementById('cosecha').value = contrato.cosecha;
            document.getElementById('toneladas').value = contrato.toneladas;
            document.getElementById('precio').value = contrato.precio;
            document.getElementById('lugar_entrega').value = contrato.lugar_entrega;
            document.getElementById('fecha_entrega').value = contrato.fecha_entrega;
            document.getElementById('anio').value = contrato.ANIO; // Usando ANIO
        } else {
            modalTitle.innerText = 'Nuevo Contrato';
            formAction.value = 'create';
            contratoId.value = '';
            // Poner la fecha actual por defecto en modo creación
            document.getElementById('fecha_contrato').valueAsDate = new Date();
        }
        contratoModal.style.display = 'flex';
    }

    function closeModal() {
        contratoModal.style.display = 'none';
    }

    // --- Funciones del Modal de Eliminación Personalizado ---
    
    function showDeleteModal(id) {
        // Establece el enlace de eliminación en el modal con el ID correcto
        deleteConfirmLink.href = 'contratos.php?delete=' + id;
        deleteConfirmModal.style.display = 'flex';
    }

    function hideDeleteModal() {
        deleteConfirmModal.style.display = 'none';
        deleteConfirmLink.href = '#'; // Limpiar el enlace
    }
    
    // Cierra cualquier modal si se hace clic fuera de él
    window.onclick = function(event) {
        if (event.target == contratoModal) {
            closeModal();
        }
        if (event.target == deleteConfirmModal) {
            hideDeleteModal();
        }
    }
</script>

<?php include 'footer.php'; ?>