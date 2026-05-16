<?php
require_once 'conexion.php';
verificarSesion();
$titulo_pagina = 'Entradas / Salidas';

$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = intval($_POST['producto_id'] ?? 0);
    $tipo        = $_POST['tipo'] ?? '';
    $cantidad    = intval($_POST['cantidad'] ?? 0);
    $motivo      = trim($_POST['motivo'] ?? '');

    if ($producto_id <= 0 || !in_array($tipo,['entrada','salida']) || $cantidad <= 0) {
        $err = 'Completa todos los campos correctamente.';
    } else {
        // Verificar stock disponible para salidas
        if ($tipo === 'salida') {
            $stk = mysqli_fetch_assoc(mysqli_prepare_and_execute($conexion, "SELECT stock_actual FROM productos WHERE id=?", "i", $producto_id));
            // usamos query directa
            $res = mysqli_query($conexion, "SELECT stock_actual FROM productos WHERE id=$producto_id");
            $row = mysqli_fetch_assoc($res);
            if ($row['stock_actual'] < $cantidad) {
                $err = "Stock insuficiente. Disponible: {$row['stock_actual']}";
            }
        }

        if (!$err) {
            // Registrar movimiento
            $stmt = mysqli_prepare($conexion, "INSERT INTO movimientos(producto_id,tipo,cantidad,motivo,usuario_id) VALUES(?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt,'isisi',$producto_id,$tipo,$cantidad,$motivo,$_SESSION['usuario_id']);
            mysqli_stmt_execute($stmt);

            // Actualizar stock
            $op = $tipo === 'entrada' ? '+' : '-';
            mysqli_query($conexion, "UPDATE productos SET stock_actual = stock_actual $op $cantidad WHERE id=$producto_id");

            $msg = ucfirst($tipo) . ' registrada correctamente.';
        }
    }
}

$productos   = mysqli_query($conexion, "SELECT id, codigo, nombre, stock_actual FROM productos WHERE activo=1 ORDER BY nombre");
$movimientos = mysqli_query($conexion, "
    SELECT m.*, p.nombre as producto, p.codigo, u.nombre as usuario
    FROM movimientos m
    JOIN productos p ON m.producto_id=p.id
    JOIN usuarios u ON m.usuario_id=u.id
    ORDER BY m.fecha DESC LIMIT 50
");

include 'header.php';
?>

<?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;align-items:start;">

<!-- Formulario -->
<div class="card">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:20px;">🔄 Registrar Movimiento</h3>
    <form method="POST">
        <div class="form-group">
            <label>Producto *</label>
            <select name="producto_id" class="form-control" required>
                <option value="">— Seleccionar —</option>
                <?php while($p=mysqli_fetch_assoc($productos)): ?>
                <option value="<?php echo $p['id']; ?>">[<?php echo htmlspecialchars($p['codigo']); ?>] <?php echo htmlspecialchars($p['nombre']); ?> (Stock: <?php echo $p['stock_actual']; ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Tipo *</label>
            <select name="tipo" class="form-control" required>
                <option value="entrada">▲ Entrada</option>
                <option value="salida">▼ Salida</option>
            </select>
        </div>
        <div class="form-group">
            <label>Cantidad *</label>
            <input type="number" name="cantidad" class="form-control" min="1" required placeholder="0">
        </div>
        <div class="form-group">
            <label>Motivo / Observación</label>
            <input type="text" name="motivo" class="form-control" placeholder="Ej: Compra proveedor, uso aula 3...">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">💾 Registrar</button>
    </form>
</div>

<!-- Historial -->
<div class="card">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:18px;">📋 Historial de movimientos</h3>
    <div class="tabla-wrap">
    <table>
        <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cant.</th><th>Motivo</th><th>Usuario</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($movimientos)===0): ?>
            <tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">Sin movimientos.</td></tr>
        <?php else: ?>
        <?php while($m=mysqli_fetch_assoc($movimientos)): ?>
        <tr>
            <td style="font-size:12px;color:#aaa;"><?php echo date('d/m/y H:i',strtotime($m['fecha'])); ?></td>
            <td><strong><?php echo htmlspecialchars($m['producto']); ?></strong></td>
            <td>
                <?php if($m['tipo']==='entrada'): ?>
                    <span style="background:#e8f5e9;color:#2e7d32;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;">▲ Entrada</span>
                <?php else: ?>
                    <span style="background:#ffebee;color:#c62828;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;">▼ Salida</span>
                <?php endif; ?>
            </td>
            <td><strong><?php echo $m['cantidad']; ?></strong></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($m['motivo'] ?: '—'); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars(explode(' ',$m['usuario'])[0]); ?></td>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</div>

<?php include 'footer.php'; ?>
