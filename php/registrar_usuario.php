<?php
header('Content-Type: application/json');
session_start();
include("conexion.php");

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

$correo = $_POST['correo'] ?? '';
$clave = $_POST['clave'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$rol = $_POST['rol'] ?? 'empleado';

// Validar datos
if (empty($correo) || empty($clave) || empty($nombre) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// Validar si el correo ya existe
$sql_check = "SELECT * FROM usuarios WHERE correo = ?";
$stmt_check = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $correo);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    mysqli_stmt_close($stmt_check);
    echo json_encode(['success' => false, 'message' => 'Este correo ya está registrado']);
    exit;
}
mysqli_stmt_close($stmt_check);

// Encriptar la contraseña
$clave_segura = password_hash($clave, PASSWORD_DEFAULT);

// Insertar nuevo usuario
$sql = "INSERT INTO usuarios (correo, clave, rol, nombre) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $correo, $clave_segura, $rol, $nombre);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . mysqli_error($conexion)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>