<?php
header('Content-Type: application/json');
include("conexion.php");

$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$correo = $_POST['correo'] ?? '';

// Validar campos obligatorios
if (empty($cedula) || empty($nombre) || empty($telefono) || empty($direccion)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit();
}

// Actualizar cliente
$sql = "UPDATE clientes SET nombre = ?, telefono = ?, direccion = ?, correo = ? WHERE cedula = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sssss", $nombre, $telefono, $direccion, $correo, $cedula);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el cliente o no hubo cambios']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
