<?php
header('Content-Type: application/json');
session_start();
include("conexion.php");

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

$usuario_id = $_POST['usuario_id'] ?? '';

if (empty($usuario_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

// No permitir que el admin se elimine a sí mismo
if ($usuario_id == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta']);
    exit;
}

try {
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysqli_error($conexion)]);
    }

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>