<?php
require_once 'conexion.php';
soloAdmin();
$titulo_pagina = 'Usuarios';
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = intval($_POST['id'] ?? 0);
    $nombre  = trim($_POST['nombre'] ?? '');
    $correo  = trim($_POST['correo'] ?? '');
    $rol     = $_POST['rol'] ?? 'bodeguero';
    $pass    = trim($_POST['password'] ?? '');

    if (empty($nombre) || empty($correo)) { $err = 'Nombre y correo son obligatorios.'; }
    elseif ($id === 0) {
        if (empty($pass)) { $err = 'La contraseña es obligatoria para nuevos usuarios.'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexion, "INSERT INTO usuarios(nombre,correo,password,rol) VALUES(?,?,?,?)");
            mysqli_stmt_bind_param($stmt,'ssss',$nombre,$correo,$hash,$rol);
            if (mysqli_stmt_execute($stmt)) $msg = 'Usuario creado.';
            else $err = 'El correo ya está registrado.';
        }
    } else {
        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexion, "UPDATE usuarios SET nombre=?,correo=?,rol=?,password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt,'ssssi',$nombre,$correo,$rol,$hash,$id);
        } else {
            $stmt = mysqli_prepare($conexion, "UPDATE usuarios SET nombre=?,correo=?,rol=? WHERE id=?");
            mysqli_stmt_bind_param($stmt,'sssi',$nombre,$correo,$rol,$id);
        }
        mysqli_stmt_execute($stmt);
        $msg = 'Usuario actualizado.';
    }
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if ($id !== $_SESSION['usuario_id']) {
        mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id");
        $msg = 'Usuario desactivado.';
    } else { $err = 'No puedes eliminarte a ti mismo.'; }
}

$editar = null;
if (isset($_GET['editar'])) {
    $res = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id=".intval($_GET['editar']));
    $editar = mysqli_fetch_assoc($res);
}

$usuarios = mysqli_query($conexion, "SELECT * FROM usuarios ORDER BY nombre");
include 'header.php';
?>

<?php if ($msg): ?><div class="alert alert-ok">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:24px;">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:20px;">
        <?php echo $editar ? '✏️ Editar Usuario' : '➕ Nuevo Usuario'; ?>
    </h3>
    <form method="POST">
        <?php if ($editar): ?><input type="hidden" name="id" value="<?php echo $editar['id']; ?>"><?php endif; ?>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Nombre completo *</label>
                <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($editar['nombre'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Correo electrónico *</label>
                <input type="email" name="correo" class="form-control" required value="<?php echo htmlspecialchars($editar['correo'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="rol" class="form-control">
                    <option value="bodeguero" <?php echo ($editar['rol']??'')==='bodeguero'?'selected':''; ?>>Bodeguero</option>
                    <option value="admin" <?php echo ($editar['rol']??'')==='admin'?'selected':''; ?>>Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>Contraseña <?php echo $editar?'(dejar vacío para no cambiar)':'*'; ?></label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" <?php echo !$editar?'required':''; ?>>
            </div>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">💾 <?php echo $editar?'Actualizar':'Crear usuario'; ?></button>
            <?php if ($editar): ?><a href="usuarios.php" class="btn btn-light">✖ Cancelar</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="font-family:Montserrat,sans-serif;font-size:15px;color:#1a4a1f;margin-bottom:18px;">👥 Lista de Usuarios</h3>
    <div class="tabla-wrap">
    <table>
        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php while($u=mysqli_fetch_assoc($usuarios)): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
            <td><?php echo htmlspecialchars($u['correo']); ?></td>
            <td>
                <?php if($u['rol']==='admin'): ?>
                    <span class="badge-rol badge-admin">Admin</span>
                <?php else: ?>
                    <span class="badge-rol badge-bodeguero">Bodeguero</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($u['activo']): ?>
                    <span style="color:#2e7d32;font-weight:600;font-size:12px;">● Activo</span>
                <?php else: ?>
                    <span style="color:#aaa;font-size:12px;">● Inactivo</span>
                <?php endif; ?>
            </td>
            <td style="font-size:12px;color:#aaa;"><?php echo date('d/m/Y',strtotime($u['fecha_registro'])); ?></td>
            <td style="white-space:nowrap;">
                <a href="usuarios.php?editar=<?php echo $u['id']; ?>" class="btn btn-warning btn-sm">✏️</a>
                <?php if($u['id']!==$_SESSION['usuario_id']): ?>
                <a href="usuarios.php?eliminar=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Desactivar usuario?')">🗑️</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include 'footer.php'; ?>
