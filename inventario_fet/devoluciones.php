<?php
require_once 'conexion.php';
verificarSesion();
$titulo_pagina = 'Devoluciones';

$msg = ''; $err = '';

// Registrar devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='crear') {
    $producto_id = intval($_POST['producto_id'] ?? 0);
    $cantidad    = intval($_POST['cantidad'] ?? 0);
    $motivo      = trim($_POST['motivo'] ?? '');
    $tipo        = $_POST['tipo'] ?? 'entrada';

    if ($producto_id <= 0 || $cantidad <= 0 || empty($motivo)) {
        $err = 'Completa todos los campos.';
    } else {
        $stmt = mysqli_prepare($conexion, "INSERT INTO devoluciones(producto_id,cantidad,motivo,tipo,usuario_id) VALUES(?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt,'iissi',$producto_id,$cantidad,$motivo,$tipo,$_SESSION['usuario_id']);
        mysqli_stmt_execute($stmt);
        $msg = 'Devolución registrada. Pendiente de aprobación.';
    }
}

// Aprobar / Rechazar (solo admin)
if (isset($_GET['aprobar']) && $_SESSION['rol']==='admin') {
    $id = intval($_GET['aprobar']);
    $dev = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM devoluciones WHERE id=$id AND estado='pendiente'"));
    if ($dev) {
        mysqli_query($conexion, "UPDATE devoluciones SET estado='aprobada' WHERE id=$id");
        $op = $dev['tipo']==='entrada' ? '+' : '-';
        mysqli_query($conexion, "UPDATE productos SET stock_actual=stock_actual $op {$dev['cantidad']} WHERE id={$dev['producto_id']}");
        $msg = 'Devolución aprobada y stock actualizado.';
    }
}

if (isset($_GET['rechazar']) && $_SESSION['rol']==='admin') {
    $id = intval($_GET['rechazar']);
    mysqli_query($conexion, "UPDATE devoluciones SET estado='rechazada' WHERE id=$id");
    $msg = 'Devolución rechazada.';
}

$productos   = mysqli_query($conexion, "SELECT id,codigo,nombre FROM productos WHERE activo=1 ORDER BY nombre");
$devoluciones= mysqli_query($conexion, "
    SELECT d.*, p.nombre as producto, u.nombre as usuario
    FROM devoluciones d
    JOIN productos p ON d.producto_id=p.id
    JOIN usuarios u ON d.usuario_id=u.id
    ORDER BY d.fecha DESC
");

include 'header.php';
?>

<?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;align-items:start;">

<!-- Formulario -->
<div class="card">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:20px;">↩️ Nueva Devolución</h3>
    <form method="POST">
        <input type="hidden" name="action" value="crear">
        <div class="form-group">
            <label>Producto *</label>
            <select name="producto_id" class="form-control" required>
                <option value="">— Seleccionar —</option>
                <?php while($p=mysqli_fetch_assoc($productos)): ?>
                <option value="<?php echo $p['id']; ?>">[<?php echo htmlspecialchars($p['codigo']); ?>] <?php echo htmlspecialchars($p['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Tipo de devolución *</label>
            <select name="tipo" class="form-control">
                <option value="entrada">Devolución a bodega (entrada)</option>
                <option value="salida">Devolución a proveedor (salida)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Cantidad *</label>
            <input type="number" name="cantidad" class="form-control" min="1" required>
        </div>
        <div class="form-group">
            <label>Motivo *</label>
            <input type="text" name="motivo" class="form-control" required placeholder="Explica el motivo...">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">📤 Registrar devolución</button>
    </form>
</div>

<!-- Listado -->
<div class="card">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:18px;">📋 Historial de devoluciones</h3>
    <div class="tabla-wrap">
    <table>
        <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cant.</th><th>Motivo</th><th>Estado</th><?php if($_SESSION['rol']==='admin'): ?><th>Acción</th><?php endif; ?></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($devoluciones)===0): ?>
            <tr><td colspan="7" style="text-align:center;color:#aaa;padding:20px;">Sin devoluciones.</td></tr>
        <?php else: ?>
        <?php while($d=mysqli_fetch_assoc($devoluciones)): ?>
        <tr>
            <td style="font-size:12px;color:#aaa;"><?php echo date('d/m/y H:i',strtotime($d['fecha'])); ?></td>
            <td><strong><?php echo htmlspecialchars($d['producto']); ?></strong></td>
            <td style="font-size:12px;"><?php echo $d['tipo']==='entrada'?'↩ A bodega':'↪ A proveedor'; ?></td>
            <td><strong><?php echo $d['cantidad']; ?></strong></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($d['motivo']); ?></td>
            <td>
                <?php
                $colores = ['pendiente'=>'#fff3e0;color:#e65100','aprobada'=>'#e8f5e9;color:#2e7d32','rechazada'=>'#ffebee;color:#c62828'];
                $labels  = ['pendiente'=>'⏳ Pendiente','aprobada'=>'✅ Aprobada','rechazada'=>'❌ Rechazada'];
                $c = $colores[$d['estado']];
                echo "<span style='background:{$c};padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;'>{$labels[$d['estado']]}</span>";
                ?>
            </td>
            <?php if($_SESSION['rol']==='admin'): ?>
            <td>
                <?php if($d['estado']==='pendiente'): ?>
                <a href="devoluciones.php?aprobar=<?php echo $d['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('¿Aprobar?')">✅</a>
                <a href="devoluciones.php?rechazar=<?php echo $d['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Rechazar?')">❌</a>
                <?php else: echo '—'; endif; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</div>

<?php include 'footer.php'; ?>
