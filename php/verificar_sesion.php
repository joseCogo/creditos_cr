<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si no existe sesión activa
if (!isset($_SESSION['usuario'])) {
    header("Location: ../home/index.php");
    exit();
}

// Función para verificar si el usuario es admin
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Función para verificar si el usuario es empleado
function esEmpleado() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'empleado';
}

function getNombreUsuario() {
    return $_SESSION['nombre'] ?? 'Usuario';
}
?>
