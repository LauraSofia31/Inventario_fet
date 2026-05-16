<?php
require_once 'conexion.php';
verificarSesion();
$titulo_pagina = 'Proveedores';
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = intval($_POST['id'] ?? 0);
    $nombre   = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $direccion= trim($_POST['direccion'] ?? '');

    if (empty($nombre)) { $err = 'El nombre es obligatorio.'; }
    elseif ($id === 0) {
        $stmt = mysqli_prepare($conexion, "INSERT INTO proveedores(nombre,contacto,telefono,correo,direccion) VALUES(?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt,'sssss',$nombre,$contacto,$telefono,$correo,$direccion);
        mysqli_stmt_execute($stmt);
        $msg = 'Proveedor creado.';
    } else {
        $stmt = mysqli_prepare($conexion, "UPDATE proveedores SET nombre=?,contacto=?,telefono=?,correo=?,direccion=? WHERE id=?");
        mysqli_stmt_bind_param($stmt,'sssssi',$nombre,$contacto,$telefono,$correo,$direccion,$id);
        mysqli_stmt_execute($stmt);
        $msg = 'Proveedor actualizado.';
    }
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    mysqli_query($conexion, "UPDATE proveedores SET activo=0 WHERE id=$id");
    $msg = 'Proveedor eliminado.';
}

$editar = null;
if (isset($_GET['editar'])) {
    $res = mysqli_query($conexion, "SELECT * FROM proveedores WHERE id=".intval($_GET['editar']));
    $editar = mysqli_fetch_assoc($res);
}

$proveedores = mysqli_query($conexion, "SELECT * FROM proveedores WHERE activo=1 ORDER BY nombre");
include 'header.php';
?>

<?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:24px;">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:20px;">
        <?php echo $editar ? '✏️ Editar Proveedor' : '➕ Nuevo Proveedor'; ?>
    </h3>
    <form method="POST">
        <?php if ($editar): ?><input type="hidden" name="id" value="<?php echo $editar['id']; ?>"><?php endif; ?>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($editar['nombre'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Persona de contacto</label>
                <input type="text" name="contacto" class="form-control" value="<?php echo htmlspecialchars($editar['contacto'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($editar['telefono'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($editar['correo'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control" value="<?php echo htmlspecialchars($editar['direccion'] ?? ''); ?>">
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">💾 <?php echo $editar?'Actualizar':'Guardar'; ?></button>
            <?php if ($editar): ?><a href="proveedores.php" class="btn btn-light">✖ Cancelar</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:18px;">🚚 Lista de Proveedores</h3>
    <div class="tabla-wrap">
    <table>
        <thead><tr><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Correo</th><th>Dirección</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($proveedores)===0): ?>
            <tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">Sin proveedores.</td></tr>
        <?php else: ?>
        <?php while($p=mysqli_fetch_assoc($proveedores)): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($p['nombre']); ?></strong></td>
            <td><?php echo htmlspecialchars($p['contacto'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($p['telefono'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($p['correo'] ?: '—'); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($p['direccion'] ?: '—'); ?></td>
            <td style="white-space:nowrap;">
                <a href="proveedores.php?editar=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">✏️</a>
                <a href="proveedores.php?eliminar=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">🗑️</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include 'footer.php'; ?>
