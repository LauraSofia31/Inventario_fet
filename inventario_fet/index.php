<?php
session_start();
require_once 'conexion.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $pass   = trim($_POST['password'] ?? '');

    if (empty($correo) || empty($pass)) {
        $error = 'Completa todos los campos.';
    } else {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE correo = ? AND activo = 1");
        mysqli_stmt_bind_param($stmt, "s", $correo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['correo']     = $user['correo'];
            $_SESSION['rol']        = $user['rol'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}

$msg = $_GET['msg'] ?? '';
if ($msg === 'registro_ok') $ok = 'Cuenta creada. Ya puedes iniciar sesión.';
if ($msg === 'pass_ok')     $ok = 'Contraseña actualizada correctamente.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BODEGA FET — Iniciar Sesión</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
    --verde-oscuro: #1a4a1f;
    --verde-medio: #2d7a35;
    --verde-claro: #4caf50;
    --verde-suave: #e8f5e9;
    --blanco: #ffffff;
    --gris-claro: #f5f5f5;
    --gris-texto: #555;
    --sombra: 0 8px 32px rgba(26,74,31,0.15);
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    background: linear-gradient(135deg, #1a4a1f 0%, #2d7a35 50%, #1a4a1f 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* Patrón de fondo decorativo */
body::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.05) 0%, transparent 50%),
                      radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
}

body::after {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 50%;
    top: -200px; right: -200px;
}

.card {
    background: var(--blanco);
    border-radius: 24px;
    padding: 48px 44px;
    width: 100%;
    max-width: 440px;
    box-shadow: var(--sombra), 0 0 0 1px rgba(255,255,255,0.1);
    position: relative;
    z-index: 1;
    animation: slideUp 0.5s ease;
}

@keyframes slideUp {
    from { opacity:0; transform:translateY(24px); }
    to   { opacity:1; transform:translateY(0); }
}

.logo-area {
    text-align: center;
    margin-bottom: 32px;
}

.logo-img {
    width: 90px;
    margin-bottom: 12px;
}

.logo-area h1 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    font-size: 22px;
    color: var(--verde-oscuro);
    letter-spacing: -0.5px;
}

.logo-area h1 span {
    color: var(--verde-claro);
}

.logo-area p {
    font-size: 12px;
    color: var(--gris-texto);
    margin-top: 4px;
    font-weight: 300;
    letter-spacing: 0.5px;
}

.divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--verde-claro), transparent);
    margin: 0 auto 28px;
    width: 60%;
}

h2 {
    font-family: 'Montserrat', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--verde-oscuro);
    margin-bottom: 24px;
    letter-spacing: -0.3px;
}

.alert {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 20px;
}
.alert-err { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
.alert-ok  { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #4caf50; }

label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--verde-oscuro);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 8px;
}

input {
    width: 100%;
    padding: 13px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: var(--gris-claro);
    margin-bottom: 18px;
}

input:focus {
    border-color: var(--verde-claro);
    background: var(--blanco);
    box-shadow: 0 0 0 3px rgba(76,175,80,0.15);
}

.btn {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, var(--verde-oscuro), var(--verde-medio));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    margin-top: 8px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(26,74,31,0.35);
}

.links {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.links a {
    font-size: 12px;
    color: var(--verde-medio);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.links a:hover { color: var(--verde-oscuro); text-decoration: underline; }

.footer-text {
    text-align: center;
    margin-top: 28px;
    font-size: 11px;
    color: #aaa;
}
</style>
</head>
<body>


<div class="card">
    <div class="logo-area">
        <!-- Logo FET en SVG inline (verde institucional) -->
        <svg class="logo-img" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" rx="12" fill="#1a4a1f"/>
            <text x="50" y="62" text-anchor="middle" font-family="Montserrat,sans-serif" font-weight="900" font-size="38" fill="white">FET</text>
        </svg>
        <h1>BODEGA <span>FET</span></h1>
        <p>Fundación Escuela Tecnológica de Neiva</p>
    </div>
    <div class="divider"></div>

    <h2>Iniciar Sesión</h2>

    <?php if ($error): ?><div class="alert alert-err">⚠️ <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($ok):    ?><div class="alert alert-ok">✅ <?php echo htmlspecialchars($ok); ?></div><?php endif; ?>

    <form method="POST">
        <label>Correo electrónico</label>
        <input type="email" name="correo" placeholder="usuario@fet.edu.co" required>

        <label>Contraseña</label>
        <input type="password" name="password" placeholder="••••••••" required>

        <button type="submit" class="btn">Acceder</button>
    </form>

    <div class="links">
        <a href="recuperar.php">¿Olvidé mi clave?</a>
        <a href="registro.php">Crear cuenta</a>
    </div>

    <p class="footer-text">Sistema de Gestión de Bodega · FET Neiva © 2026</p>
</div>

</body>
</html>
