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
$nombre = $_POST['nombre'] ?? '';
$correo = $_POST['correo'] ?? '';
$rol = $_POST['rol'] ?? '';
$clave = $_POST['clave'] ?? '';

// Validar datos
if (empty($usuario_id) || empty($nombre) || empty($correo) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

try {
    // Verificar si el correo ya existe en otro usuario
    $sql_check = "SELECT id FROM usuarios WHERE correo = ? AND id != ?";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "si", $correo, $usuario_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Este correo ya está registrado por otro usuario']);
        exit;
    }

    // Si se proporciona nueva contraseña, actualizar también la contraseña
    if (!empty($clave)) {
        $clave_segura = password_hash($clave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, clave = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $correo, $rol, $clave_segura, $usuario_id);
    } else {
        // Solo actualizar nombre, correo y rol
        $sql = "UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $nombre, $correo, $rol, $usuario_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
    }

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>