<?php
require_once 'conexion.php';
verificarSesion();
$titulo_pagina = 'Productos';

$msg = ''; $err = '';

// GUARDAR / ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = intval($_POST['id'] ?? 0);
    $codigo      = trim($_POST['codigo'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria   = intval($_POST['categoria_id'] ?? 0);
    $proveedor   = intval($_POST['proveedor_id'] ?? 0);
    $stock_min   = intval($_POST['stock_minimo'] ?? 5);
    $precio      = floatval($_POST['precio_unitario'] ?? 0);
    $unidad      = trim($_POST['unidad_medida'] ?? 'unidad');

    if (empty($codigo) || empty($nombre)) {
        $err = 'Código y nombre son obligatorios.';
    } elseif ($id === 0) {
        $stmt = mysqli_prepare($conexion, "INSERT INTO productos(codigo,nombre,descripcion,categoria_id,proveedor_id,stock_minimo,precio_unitario,unidad_medida) VALUES(?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt,'sssiiids',$codigo,$nombre,$descripcion,$categoria,$proveedor,$stock_min,$precio,$unidad);
        if (mysqli_stmt_execute($stmt)) $msg = 'Producto creado correctamente.';
        else $err = 'Error: ese código ya existe.';
    } else {
        $stmt = mysqli_prepare($conexion, "UPDATE productos SET codigo=?,nombre=?,descripcion=?,categoria_id=?,proveedor_id=?,stock_minimo=?,precio_unitario=?,unidad_medida=? WHERE id=?");
        mysqli_stmt_bind_param($stmt,'sssiiidsi',$codigo,$nombre,$descripcion,$categoria,$proveedor,$stock_min,$precio,$unidad,$id);
        mysqli_stmt_execute($stmt);
        $msg = 'Producto actualizado.';
    }
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = mysqli_prepare($conexion, "UPDATE productos SET activo=0 WHERE id=?");
    mysqli_stmt_bind_param($stmt,'i',$id);
    mysqli_stmt_execute($stmt);
    $msg = 'Producto eliminado.';
}

// EDITAR (cargar datos)
$editar = null;
if (isset($_GET['editar'])) {
    $stmt = mysqli_prepare($conexion, "SELECT * FROM productos WHERE id=?");
    mysqli_stmt_bind_param($stmt,'i',$_GET['editar']);
    mysqli_stmt_execute($stmt);
    $editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// LISTAR
$buscar = trim($_GET['buscar'] ?? '');
if ($buscar) {
    $b = "%$buscar%";
    $stmt = mysqli_prepare($conexion, "SELECT p.*,c.nombre as cat, pr.nombre as prov FROM productos p LEFT JOIN categorias c ON p.categoria_id=c.id LEFT JOIN proveedores pr ON p.proveedor_id=pr.id WHERE p.activo=1 AND (p.nombre LIKE ? OR p.codigo LIKE ?) ORDER BY p.nombre");
    mysqli_stmt_bind_param($stmt,'ss',$b,$b);
    mysqli_stmt_execute($stmt);
    $productos = mysqli_stmt_get_result($stmt);
} else {
    $productos = mysqli_query($conexion, "SELECT p.*,c.nombre as cat, pr.nombre as prov FROM productos p LEFT JOIN categorias c ON p.categoria_id=c.id LEFT JOIN proveedores pr ON p.proveedor_id=pr.id WHERE p.activo=1 ORDER BY p.nombre");
}

$categorias  = mysqli_query($conexion, "SELECT * FROM categorias ORDER BY nombre");
$proveedores = mysqli_query($conexion, "SELECT * FROM proveedores WHERE activo=1 ORDER BY nombre");

include 'header.php';
?>

<?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

<!-- Formulario -->
<div class="card" style="margin-bottom:24px;">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:20px;">
        <?php echo $editar ? '✏️ Editar Producto' : '➕ Nuevo Producto'; ?>
    </h3>
    <form method="POST">
        <?php if ($editar): ?><input type="hidden" name="id" value="<?php echo $editar['id']; ?>"><?php endif; ?>
        <div class="form-grid-3">
            <div class="form-group">
                <label>Código *</label>
                <input type="text" name="codigo" class="form-control" value="<?php echo htmlspecialchars($editar['codigo'] ?? ''); ?>" required placeholder="Ej: PRD-001">
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label>Nombre del producto *</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($editar['nombre'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" class="form-control" value="<?php echo htmlspecialchars($editar['descripcion'] ?? ''); ?>">
        </div>
        <div class="form-grid-3">
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id" class="form-control">
                    <option value="">— Sin categoría —</option>
                    <?php mysqli_data_seek($categorias,0); while($c=mysqli_fetch_assoc($categorias)): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($editar['categoria_id']??'')==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Proveedor</label>
                <select name="proveedor_id" class="form-control">
                    <option value="">— Sin proveedor —</option>
                    <?php mysqli_data_seek($proveedores,0); while($p=mysqli_fetch_assoc($proveedores)): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($editar['proveedor_id']??'')==$p['id']?'selected':''; ?>><?php echo htmlspecialchars($p['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Unidad de medida</label>
                <select name="unidad_medida" class="form-control">
                    <?php foreach(['unidad','caja','resma','paquete','litro','kg','metro'] as $u): ?>
                    <option <?php echo ($editar['unidad_medida']??'unidad')===$u?'selected':''; ?>><?php echo $u; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Stock mínimo</label>
                <input type="number" name="stock_minimo" class="form-control" value="<?php echo $editar['stock_minimo'] ?? 5; ?>" min="0">
            </div>
            <div class="form-group">
                <label>Precio unitario ($)</label>
                <input type="number" step="0.01" name="precio_unitario" class="form-control" value="<?php echo $editar['precio_unitario'] ?? 0; ?>" min="0">
            </div>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">💾 <?php echo $editar?'Actualizar':'Guardar'; ?></button>
            <?php if ($editar): ?><a href="productos.php" class="btn btn-light">✖ Cancelar</a><?php endif; ?>
        </div>
    </form>
</div>

<!-- Buscador + Tabla -->
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
        <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;">📦 Lista de Productos</h3>
        <form method="GET" style="display:flex;gap:8px;">
            <input type="text" name="buscar" class="form-control" style="width:220px;" placeholder="🔍 Buscar..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            <?php if ($buscar): ?><a href="productos.php" class="btn btn-light btn-sm">✖</a><?php endif; ?>
        </form>
    </div>
    <div class="tabla-wrap">
    <table>
        <thead><tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Precio</th><th>Proveedor</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($productos) === 0): ?>
            <tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">Sin productos registrados.</td></tr>
        <?php else: ?>
        <?php while($p = mysqli_fetch_assoc($productos)): ?>
        <tr>
            <td><code style="background:#f0f4f0;padding:2px 8px;border-radius:4px;font-size:12px;"><?php echo htmlspecialchars($p['codigo']); ?></code></td>
            <td><strong><?php echo htmlspecialchars($p['nombre']); ?></strong></td>
            <td><?php echo htmlspecialchars($p['cat'] ?? '—'); ?></td>
            <td>
                <?php
                $s = $p['stock_actual'];
                $cls = $s==0?'stock-cero':($s<=$p['stock_minimo']?'stock-bajo':'stock-ok');
                echo "<span class='$cls'>$s</span>";
                ?>
            </td>
            <td>$<?php echo number_format($p['precio_unitario'],0,',','.'); ?></td>
            <td><?php echo htmlspecialchars($p['prov'] ?? '—'); ?></td>
            <td style="white-space:nowrap;">
                <a href="productos.php?editar=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">✏️</a>
                <a href="productos.php?eliminar=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este producto?')">🗑️</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include 'footer.php'; ?>
