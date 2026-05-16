<!-- registro.php -->
<?php
if (session_status()===PHP_SESSION_NONE) session_start();
if (isset($_SESSION['usuario_id'])) { header("Location: dashboard.php"); exit(); }
require_once 'conexion.php';
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $pass     = trim($_POST['password'] ?? '');
    $rol      = $_POST['rol'] ?? 'bodeguero';
    $pregunta = trim($_POST['pregunta_seguridad'] ?? '');
    $respuesta= trim($_POST['respuesta_seguridad'] ?? '');

    if (empty($nombre)||empty($correo)||empty($pass)) { $err = 'Completa todos los campos.'; }
    else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conexion, "INSERT INTO usuarios(nombre,correo,password,rol,pregunta_seguridad,respuesta_seguridad) VALUES(?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt,'ssssss',$nombre,$correo,$hash,$rol,$pregunta,$respuesta);
        if (mysqli_stmt_execute($stmt)) { header("Location: index.php?msg=registro_ok"); exit(); }
        else $err = 'El correo ya está registrado.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear cuenta — Bodega FET</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;min-height:100vh;background:linear-gradient(135deg,#1a4a1f,#2d7a35);display:flex;align-items:center;justify-content:center;padding:20px;}
.card{background:white;border-radius:24px;padding:40px;width:100%;max-width:500px;box-shadow:0 20px 50px rgba(0,0,0,.2);}
.logo{text-align:center;margin-bottom:28px;}
.logo h1{font-family:'Montserrat',sans-serif;font-weight:900;font-size:20px;color:#1a4a1f;}
.logo h1 span{color:#4caf50;}
h2{font-family:'Montserrat',sans-serif;font-size:17px;color:#1a4a1f;margin-bottom:22px;}
.alert{padding:11px 15px;border-radius:8px;font-size:13px;margin-bottom:18px;}
.alert-err{background:#ffebee;color:#c62828;border-left:4px solid #c62828;}
label{display:block;font-size:11px;font-weight:700;color:#1a4a1f;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;margin-top:14px;}
input,select{width:100%;padding:11px 14px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;outline:none;transition:.2s;}
input:focus,select:focus{border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.12);}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a4a1f,#2d7a35);color:white;border:none;border-radius:10px;font-size:14px;font-weight:700;font-family:'Montserrat',sans-serif;letter-spacing:1px;cursor:pointer;margin-top:20px;text-transform:uppercase;}
.btn:hover{opacity:.9;}
.back{display:block;text-align:center;margin-top:16px;font-size:12px;color:#2d7a35;text-decoration:none;}
</style>
</head>
<body>
<div class="card">
    <div class="logo">
        <h1>BODEGA <span>FET</span></h1>
        <p style="font-size:12px;color:#aaa;margin-top:4px;">Crear nueva cuenta</p>
    </div>
    <h2>Registro de Usuario</h2>
    <?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <form method="POST">
        <label>Nombre completo *</label>
        <input type="text" name="nombre" required>
        <label>Correo electrónico *</label>
        <input type="email" name="correo" required>
        <label>Rol</label>
        <select name="rol">
            <option value="bodeguero">Bodeguero</option>
            <option value="admin">Administrador</option>
        </select>
        <label>Pregunta de seguridad</label>
        <select name="pregunta_seguridad">
            <option>¿Nombre de tu primera mascota?</option>
            <option>¿Ciudad donde naciste?</option>
            <option>¿Nombre de tu colegio?</option>
            <option>¿Apodo de la infancia?</option>
        </select>
        <label>Respuesta</label>
        <input type="text" name="respuesta_seguridad">
        <label>Contraseña *</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn">Registrarse</button>
    </form>
    <a href="index.php" class="back">← Volver al login</a>
</div>
</body>
</html>
