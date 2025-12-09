<?php
session_start();
header('Content-Type: application/json');
include("conexion.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$sql = "SELECT m.*, u.nombre as usuario_nombre 
        FROM movimientos_caja m
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        ORDER BY m.fecha_movimiento DESC
        LIMIT 50";

$result = mysqli_query($conexion, $sql);
$movimientos = [];

while ($row = mysqli_fetch_assoc($result)) {
    $movimientos[] = $row;
}

echo json_encode($movimientos);
mysqli_close($conexion);
?>