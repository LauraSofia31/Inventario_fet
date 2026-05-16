<?php
require_once 'conexion.php';
verificarSesion();
$titulo_pagina = 'Inicio';

// Estadísticas
$total_productos  = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as c FROM productos WHERE activo=1"))['c'];
$valor_inventario = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT SUM(stock_actual * precio_unitario) as v FROM productos WHERE activo=1"))['v'] ?? 0;
$mov_hoy          = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as c FROM movimientos WHERE DATE(fecha)=CURDATE()"))['c'];
$agotados         = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as c FROM productos WHERE stock_actual=0 AND activo=1"))['c'];
$stock_bajo       = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as c FROM productos WHERE stock_actual>0 AND stock_actual<=stock_minimo AND activo=1"))['c'];

// Últimos movimientos
$ultimos = mysqli_query($conexion, "
    SELECT m.*, p.nombre as producto, u.nombre as usuario
    FROM movimientos m
    JOIN productos p ON m.producto_id = p.id
    JOIN usuarios u ON m.usuario_id = u.id
    ORDER BY m.fecha DESC LIMIT 8
");

// Productos con stock bajo
$bajos = mysqli_query($conexion, "
    SELECT nombre, stock_actual, stock_minimo FROM productos
    WHERE stock_actual <= stock_minimo AND activo=1
    ORDER BY stock_actual ASC LIMIT 6
");

include 'header.php';
?>

<?php $msg = $_GET['msg'] ?? ''; $err = $_GET['error'] ?? ''; ?>
<?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($err === 'sin_permiso'): ?><div class="alert alert-err">⚠️ No tienes permisos para acceder a esa sección.</div><?php endif; ?>

<!-- Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon verde">📦</div>
        <div><div class="stat-val"><?php echo $total_productos; ?></div><div class="stat-lbl">Productos activos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon azul">💰</div>
        <div><div class="stat-val">$<?php echo number_format($valor_inventario,0,',','.'); ?></div><div class="stat-lbl">Valor inventario</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon naranja">🔄</div>
        <div><div class="stat-val"><?php echo $mov_hoy; ?></div><div class="stat-lbl">Movimientos hoy</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rojo">⚠️</div>
        <div><div class="stat-val" style="color:#c62828"><?php echo $agotados; ?></div><div class="stat-lbl">Productos agotados</div></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

<!-- Últimos movimientos -->
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;">🔄 Últimos movimientos</h3>
        <a href="movimientos.php" class="btn btn-light btn-sm">Ver todos</a>
    </div>
    <?php if (mysqli_num_rows($ultimos) === 0): ?>
        <p style="color:#aaa;font-size:13px;">Sin movimientos registrados.</p>
    <?php else: ?>
    <div class="tabla-wrap">
    <table>
        <thead><tr><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Usuario</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php while($m = mysqli_fetch_assoc($ultimos)): ?>
        <tr>
            <td><?php echo htmlspecialchars($m['producto']); ?></td>
            <td>
                <?php if($m['tipo']==='entrada'): ?>
                    <span style="background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">▲ Entrada</span>
                <?php else: ?>
                    <span style="background:#ffebee;color:#c62828;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">▼ Salida</span>
                <?php endif; ?>
            </td>
            <td><strong><?php echo $m['cantidad']; ?></strong></td>
            <td><?php echo htmlspecialchars(explode(' ',$m['usuario'])[0]); ?></td>
            <td style="font-size:12px;color:#aaa;"><?php echo date('d/m/y H:i', strtotime($m['fecha'])); ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Alertas stock -->
<div class="card">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:18px;">⚠️ Alertas de stock</h3>
    <?php if (mysqli_num_rows($bajos) === 0): ?>
        <div style="text-align:center;padding:20px;">
            <div style="font-size:32px;margin-bottom:8px;">✅</div>
            <p style="color:#2e7d32;font-weight:600;font-size:13px;">Todo en orden</p>
        </div>
    <?php else: ?>
        <?php while($b = mysqli_fetch_assoc($bajos)): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0f0f0;">
            <div>
                <div style="font-size:13px;font-weight:600;color:#333;"><?php echo htmlspecialchars($b['nombre']); ?></div>
                <div style="font-size:11px;color:#aaa;">Mínimo: <?php echo $b['stock_minimo']; ?></div>
            </div>
            <span class="<?php echo $b['stock_actual']==0?'stock-cero':'stock-bajo'; ?>">
                <?php echo $b['stock_actual']; ?>
            </span>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</div>

<?php include 'footer.php'; ?>
