<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    // Redirigir al login (index.php está en /home/)
    header("Location: /home/");
    exit();
}

// Función para verificar si es admin
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Función para verificar si es empleado
function esEmpleado() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'empleado';
}

// Función para obtener el nombre del usuario
function getNombreUsuario() {
    return $_SESSION['nombre'] ?? 'Usuario';
}
?>