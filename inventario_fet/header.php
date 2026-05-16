<?php
// Este archivo se incluye en todas las páginas internas
// Requiere que conexion.php ya esté incluido y la sesión iniciada
verificarSesion();
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $titulo_pagina ?? 'Bodega FET'; ?> — Inventario FET</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --verde-oscuro: #1a4a1f;
    --verde-medio: #2d7a35;
    --verde-claro: #4caf50;
    --verde-suave: #e8f5e9;
    --blanco: #ffffff;
    --gris-bg: #f4f6f4;
    --gris-claro: #ececec;
    --gris-texto: #555;
    --sombra: 0 2px 12px rgba(26,74,31,0.10);
    --sidebar-w: 240px;
}

* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:var(--gris-bg); display:flex; min-height:100vh; }

/* SIDEBAR */
.sidebar {
    width: var(--sidebar-w);
    background: var(--verde-oscuro);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: fixed;
    top:0; left:0;
    z-index: 100;
    transition: transform 0.3s;
}

.sidebar-logo {
    padding: 24px 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-logo svg { flex-shrink:0; }

.sidebar-logo-text h1 {
    font-family:'Montserrat',sans-serif;
    font-weight:900;
    font-size:16px;
    color:white;
    line-height:1.1;
}
.sidebar-logo-text h1 span { color:var(--verde-claro); }
.sidebar-logo-text p {
    font-size:10px;
    color:rgba(255,255,255,0.5);
    margin-top:2px;
    line-height:1.3;
}

.sidebar-nav { flex:1; padding:16px 0; }

.nav-section {
    font-size:10px;
    font-weight:700;
    letter-spacing:1px;
    color:rgba(255,255,255,0.35);
    text-transform:uppercase;
    padding:16px 20px 6px;
}

.nav-item {
    display:flex;
    align-items:center;
    gap:12px;
    padding:11px 20px;
    color:rgba(255,255,255,0.7);
    text-decoration:none;
    font-size:13.5px;
    font-weight:500;
    transition:all 0.2s;
    border-left:3px solid transparent;
    position:relative;
}

.nav-item:hover {
    background:rgba(255,255,255,0.07);
    color:white;
}

.nav-item.active {
    background:rgba(76,175,80,0.15);
    color:white;
    border-left-color:var(--verde-claro);
    font-weight:600;
}

.nav-item .icon { font-size:18px; width:22px; text-align:center; }

.sidebar-user {
    padding:16px 20px;
    border-top:1px solid rgba(255,255,255,0.1);
}

.user-info {
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:12px;
}

.user-avatar {
    width:36px; height:36px;
    background:var(--verde-claro);
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    font-size:14px;
    color:white;
    flex-shrink:0;
}

.user-name { font-size:13px; font-weight:600; color:white; line-height:1.2; }
.user-role {
    font-size:10px;
    color:var(--verde-claro);
    text-transform:uppercase;
    letter-spacing:0.5px;
}

.btn-salir {
    display:block;
    text-align:center;
    padding:8px;
    background:rgba(255,255,255,0.08);
    color:rgba(255,255,255,0.7);
    text-decoration:none;
    border-radius:8px;
    font-size:12px;
    font-weight:500;
    transition:all 0.2s;
}
.btn-salir:hover { background:rgba(220,53,69,0.3); color:white; }

/* MAIN CONTENT */
.main {
    margin-left: var(--sidebar-w);
    flex:1;
    display:flex;
    flex-direction:column;
    min-height:100vh;
}

.topbar {
    background:var(--blanco);
    padding:16px 28px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:var(--sombra);
    border-bottom:1px solid var(--gris-claro);
    position:sticky;
    top:0;
    z-index:50;
}

.topbar h2 {
    font-family:'Montserrat',sans-serif;
    font-weight:700;
    font-size:20px;
    color:var(--verde-oscuro);
}

.topbar-right {
    display:flex;
    align-items:center;
    gap:16px;
}

.badge-rol {
    padding:4px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:0.5px;
}
.badge-admin    { background:#e8f5e9; color:#2e7d32; }
.badge-bodeguero{ background:#e3f2fd; color:#1565c0; }

.content { padding:28px; flex:1; }

/* Alerts globales */
.alert {
    padding:12px 18px;
    border-radius:10px;
    font-size:13px;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
}
.alert-ok  { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
.alert-err { background:#ffebee; color:#c62828; border:1px solid #ef9a9a; }
.alert-warn{ background:#fff8e1; color:#e65100; border:1px solid #ffcc02; }

/* Cards */
.card {
    background:var(--blanco);
    border-radius:16px;
    padding:24px;
    box-shadow:var(--sombra);
    border:1px solid var(--gris-claro);
}

/* Tabla */
.tabla-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; font-size:13.5px; }
thead th {
    background:var(--verde-oscuro);
    color:white;
    padding:12px 14px;
    text-align:left;
    font-weight:600;
    font-size:12px;
    letter-spacing:0.3px;
}
thead th:first-child { border-radius:8px 0 0 0; }
thead th:last-child  { border-radius:0 8px 0 0; }
tbody td { padding:11px 14px; border-bottom:1px solid #f0f0f0; color:var(--gris-texto); }
tbody tr:hover { background:#f9fdf9; }
tbody tr:last-child td { border-bottom:none; }

/* Botones */
.btn { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:all 0.2s; font-family:'Inter',sans-serif; }
.btn-primary { background:var(--verde-oscuro); color:white; }
.btn-primary:hover { background:var(--verde-medio); transform:translateY(-1px); box-shadow:0 4px 12px rgba(26,74,31,0.25); }
.btn-success { background:var(--verde-claro); color:white; }
.btn-success:hover { background:#388e3c; }
.btn-warning { background:#ff9800; color:white; }
.btn-warning:hover { background:#f57c00; }
.btn-danger  { background:#e53935; color:white; }
.btn-danger:hover  { background:#c62828; }
.btn-light   { background:var(--gris-claro); color:var(--gris-texto); }
.btn-light:hover   { background:#d5d5d5; }
.btn-sm { padding:6px 12px; font-size:12px; }

/* Formularios */
.form-group { margin-bottom:18px; }
.form-group label { display:block; font-size:11px; font-weight:700; color:var(--verde-oscuro); text-transform:uppercase; letter-spacing:0.7px; margin-bottom:7px; }
.form-control { width:100%; padding:11px 14px; border:2px solid var(--gris-claro); border-radius:10px; font-size:14px; font-family:'Inter',sans-serif; outline:none; transition:border-color 0.2s, box-shadow 0.2s; background:#fafafa; }
.form-control:focus { border-color:var(--verde-claro); background:white; box-shadow:0 0 0 3px rgba(76,175,80,0.12); }
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }

/* Badge stock */
.stock-ok   { color:#2e7d32; font-weight:700; }
.stock-bajo { color:#e65100; font-weight:700; }
.stock-cero { color:#c62828; font-weight:700; }

/* Stat cards */
.stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:24px; }
.stat-card { background:white; border-radius:16px; padding:20px 24px; box-shadow:var(--sombra); border:1px solid var(--gris-claro); display:flex; align-items:center; gap:16px; }
.stat-icon { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0; }
.stat-icon.verde  { background:var(--verde-suave); }
.stat-icon.azul   { background:#e3f2fd; }
.stat-icon.naranja{ background:#fff3e0; }
.stat-icon.rojo   { background:#ffebee; }
.stat-val { font-family:'Montserrat',sans-serif; font-weight:800; font-size:28px; color:var(--verde-oscuro); line-height:1; }
.stat-lbl { font-size:12px; color:#888; margin-top:4px; font-weight:500; }

@media(max-width:768px) {
    .sidebar { transform:translateX(-100%); }
    .main { margin-left:0; }
    .form-grid-2, .form-grid-3 { grid-template-columns:1fr; }
}
</style>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <svg width="36" height="36" viewBox="0 0 36 36">
            <rect width="36" height="36" rx="8" fill="#4caf50"/>
            <text x="18" y="25" text-anchor="middle" font-family="Montserrat,sans-serif" font-weight="900" font-size="14" fill="white">FET</text>
        </svg>
        <div class="sidebar-logo-text">
            <h1>BODEGA <span>FET</span></h1>
            <p>Gestión de Inventario</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="dashboard.php" class="nav-item <?php echo ($pagina_actual=='dashboard.php')?'active':''; ?>">
            <span class="icon">📊</span> Inicio
        </a>

        <div class="nav-section">Inventario</div>
        <a href="productos.php" class="nav-item <?php echo ($pagina_actual=='productos.php')?'active':''; ?>">
            <span class="icon">📦</span> Productos
        </a>
        <a href="movimientos.php" class="nav-item <?php echo ($pagina_actual=='movimientos.php')?'active':''; ?>">
            <span class="icon">🔄</span> Entradas / Salidas
        </a>
        <a href="devoluciones.php" class="nav-item <?php echo ($pagina_actual=='devoluciones.php')?'active':''; ?>">
            <span class="icon">↩️</span> Devoluciones
        </a>

        <div class="nav-section">Gestión</div>
        <a href="proveedores.php" class="nav-item <?php echo ($pagina_actual=='proveedores.php')?'active':''; ?>">
            <span class="icon">🚚</span> Proveedores
        </a>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
        <a href="usuarios.php" class="nav-item <?php echo ($pagina_actual=='usuarios.php')?'active':''; ?>">
            <span class="icon">👥</span> Usuarios
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nombre'],0,1)); ?></div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars(explode(' ',$_SESSION['nombre'])[0]); ?></div>
                <div class="user-role"><?php echo $_SESSION['rol']; ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn-salir">⬅ Cerrar sesión</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main">
<div class="topbar">
    <h2><?php echo $titulo_pagina ?? 'Dashboard'; ?></h2>
    <div class="topbar-right">
        <span class="badge-rol badge-<?php echo $_SESSION['rol']; ?>"><?php echo $_SESSION['rol']; ?></span>
        <span style="font-size:13px;color:#888;">👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
    </div>
</div>
<div class="content">
