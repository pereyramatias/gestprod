<?php
include 'header.php'; // Incluye el header y la verificación de login

try {
    // Contar total de clientes
    $stmt_clientes = $pdo->query("SELECT COUNT(*) as total FROM clientes");
    $total_clientes = $stmt_clientes->fetchColumn();

    // Contar total de contratos
    $stmt_contratos = $pdo->query("SELECT COUNT(*) as total FROM contratos");
    $total_contratos = $stmt_contratos->fetchColumn();
    
    // Sumar toneladas totales
    $stmt_toneladas = $pdo->query("SELECT SUM(toneladas) as total FROM contratos");
    $total_toneladas = $stmt_toneladas->fetchColumn() ?: 0;
    
    // Obtener últimos 5 contratos
    $stmt_ultimos_contratos = $pdo->query("
        SELECT c.*, cl.razon_social 
        FROM contratos c
        JOIN clientes cl ON c.cliente_id = cl.id
        ORDER BY c.fecha_contrato DESC
        LIMIT 5
    ");
    $ultimos_contratos = $stmt_ultimos_contratos->fetchAll();

} catch (PDOException $e) {
    // Manejo de error simple
    die("Error al cargar datos del dashboard: " . $e->getMessage());
}
?>

<title>Dashboard | <?= APP_NAME ?></title>

<h1 class="text-3xl font-semibold text-white">Dashboard</h1>
<p class="mt-2 text-gray-400">Resumen general de la empresa.</p>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
    <div class="bg-[var(--bg-dark-2)] p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-[var(--accent-color)]/20 text-[var(--accent-color)] p-4 rounded-full">
            <i class="fas fa-users text-2xl"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-400">Total de Clientes</p>
            <p class="text-2xl font-bold text-white"><?= $total_clientes ?></p>
        </div>
    </div>
    <div class="bg-[var(--bg-dark-2)] p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-blue-500/20 text-blue-400 p-4 rounded-full">
            <i class="fas fa-file-signature text-2xl"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-400">Total de Contratos</p>
            <p class="text-2xl font-bold text-white"><?= $total_contratos ?></p>
        </div>
    </div>
    <div class="bg-[var(--bg-dark-2)] p-6 rounded-lg shadow-lg flex items-center">
         <div class="bg-green-500/20 text-green-400 p-4 rounded-full">
            <i class="fas fa-weight-hanging text-2xl"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-400">Toneladas Totales</p>
            <p class="text-2xl font-bold text-white"><?= number_format($total_toneladas, 2, ',', '.') ?> Tn.</p>
        </div>
    </div>
</div>

<!-- Últimos Contratos -->
<div class="mt-10 bg-[var(--bg-dark-2)] p-6 rounded-lg shadow-lg">
    <h2 class="text-xl font-semibold text-white mb-4">Actividad reciente</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-[var(--border-color)]">
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Cliente</th>
                    <th class="p-3">Producto</th>
                    <th class="p-3 text-right">Toneladas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimos_contratos)): ?>
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-400">No hay contratos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ultimos_contratos as $contrato): ?>
                        <tr class="border-b border-[var(--border-color)] hover:bg-[var(--bg-dark)]">
                            <td class="p-3"><?= date("d/m/Y", strtotime($contrato['fecha_contrato'])) ?></td>
                            <td class="p-3"><?= htmlspecialchars($contrato['razon_social']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($contrato['producto']) ?></td>
                            <td class="p-3 text-right font-mono"><?= number_format($contrato['toneladas'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
