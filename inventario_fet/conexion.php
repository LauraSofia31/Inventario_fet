<?php
session_start();

$servidor = "127.0.0.1";
$usuario  = "admin";
$password = "1234";
$bd       = "inventario_fet";

$conexion = mysqli_connect($servidor, $usuario, $password, $bd);

if (!$conexion) {
    error_log("DB Error: " . mysqli_connect_error());
    die("Error de conexión. Intente más tarde.");
}

mysqli_set_charset($conexion, "utf8mb4");

// Función para verificar si hay sesión activa
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /Inventario_FET/index.php");
        exit();
    }
}

// Función para verificar rol admin
function soloAdmin() {
    verificarSesion();
    if ($_SESSION['rol'] !== 'admin') {
        header("Location: /Inventario_FET/dashboard.php?error=sin_permiso");
        exit();
    }
}
?>
