<?php
header('Content-Type: application/json');
session_start();
include("conexion.php");

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit();
}

// Esta consulta ahora funcionará porque 'rol' existe.
$sql = "SELECT id, nombre, correo, rol FROM usuarios ORDER BY id DESC";
$resultado = mysqli_query($conexion, $sql);

$usuarios = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);
mysqli_close($conexion);
?>