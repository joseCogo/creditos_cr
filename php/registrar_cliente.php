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

// Verificar si el cliente ya existe
$sql_check = "SELECT cedula FROM clientes WHERE cedula = ?";
$stmt_check = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $cedula);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un cliente con esta cédula']);
    exit();
}

// Insertar nuevo cliente
$sql = "INSERT INTO clientes (cedula, nombre, telefono, direccion, correo) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sssss", $cedula, $nombre, $telefono, $direccion, $correo);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Cliente registrado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar el cliente: ' . mysqli_error($conexion)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
