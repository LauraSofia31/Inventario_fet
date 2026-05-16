<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once 'conexion.php';
$msg=''; $err=''; $paso=1; $usuario=null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['correo'])) {
        $correo = trim($_POST['correo']);
        $stmt = mysqli_prepare($conexion,"SELECT * FROM usuarios WHERE correo=? AND activo=1");
        mysqli_stmt_bind_param($stmt,'s',$correo);
        mysqli_stmt_execute($stmt);
        $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$usuario) $err='Correo no encontrado.';
        else { $_SESSION['rec_id']=$usuario['id']; $paso=2; }
    } elseif (isset($_POST['respuesta'])) {
        $id = intval($_SESSION['rec_id'] ?? 0);
        $resp = strtolower(trim($_POST['respuesta']));
        $stmt = mysqli_prepare($conexion,"SELECT respuesta_seguridad FROM usuarios WHERE id=?");
        mysqli_stmt_bind_param($stmt,'i',$id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (strtolower($row['respuesta_seguridad']) === $resp) { $_SESSION['rec_ok']=$id; $paso=3; }
        else { $err='Respuesta incorrecta.'; $paso=2; }
    } elseif (isset($_POST['nueva_pass'])) {
        $id = intval($_SESSION['rec_ok'] ?? 0);
        if ($id && !empty($_POST['nueva_pass'])) {
            $hash = password_hash($_POST['nueva_pass'], PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexion,"UPDATE usuarios SET password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt,'si',$hash,$id);
            mysqli_stmt_execute($stmt);
            unset($_SESSION['rec_id'],$_SESSION['rec_ok']);
            header("Location: index.php?msg=pass_ok"); exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Recuperar clave — Bodega FET</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;min-height:100vh;background:linear-gradient(135deg,#1a4a1f,#2d7a35);display:flex;align-items:center;justify-content:center;}
.card{background:white;border-radius:20px;padding:40px;width:100%;max-width:420px;box-shadow:0 20px 50px rgba(0,0,0,.2);}
h1{font-family:'Montserrat',sans-serif;font-weight:900;font-size:18px;color:#1a4a1f;margin-bottom:6px;}
h1 span{color:#4caf50;}
p.sub{font-size:12px;color:#aaa;margin-bottom:24px;}
.alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;}
.alert-err{background:#ffebee;color:#c62828;border-left:3px solid #c62828;}
label{display:block;font-size:11px;font-weight:700;color:#1a4a1f;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;}
input{width:100%;padding:11px 14px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;outline:none;transition:.2s;margin-bottom:16px;}
input:focus{border-color:#4caf50;}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a4a1f,#2d7a35);color:white;border:none;border-radius:10px;font-size:14px;font-weight:700;font-family:'Montserrat',sans-serif;cursor:pointer;letter-spacing:1px;text-transform:uppercase;}
.back{display:block;text-align:center;margin-top:14px;font-size:12px;color:#2d7a35;text-decoration:none;}
.pregunta{background:#e8f5e9;padding:12px;border-radius:8px;font-size:13px;color:#1a4a1f;margin-bottom:16px;font-weight:500;}
</style>
</head>
<body>
<div class="card">
    <h1>BODEGA <span>FET</span></h1>
    <p class="sub">Recuperación de contraseña</p>
    <?php if ($err): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

    <?php if ($paso===1): ?>
    <form method="POST">
        <label>Correo electrónico</label>
        <input type="email" name="correo" required placeholder="usuario@fet.edu.co">
        <button class="btn" type="submit">Continuar →</button>
    </form>

    <?php elseif ($paso===2): ?>
    <?php
    $stmt = mysqli_prepare($conexion,"SELECT pregunta_seguridad FROM usuarios WHERE id=?");
    $rid = $_SESSION['rec_id'];
    mysqli_stmt_bind_param($stmt,'i',$rid);
    mysqli_stmt_execute($stmt);
    $row2 = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    ?>
    <div class="pregunta">🔒 <?php echo htmlspecialchars($row2['pregunta_seguridad']); ?></div>
    <form method="POST">
        <label>Tu respuesta</label>
        <input type="text" name="respuesta" required>
        <button class="btn" type="submit">Verificar →</button>
    </form>

    <?php elseif ($paso===3): ?>
    <form method="POST">
        <label>Nueva contraseña</label>
        <input type="password" name="nueva_pass" required>
        <button class="btn" type="submit">Cambiar contraseña</button>
    </form>
    <?php endif; ?>

    <a href="index.php" class="back">← Volver al login</a>
</div>
</body>
</html>
