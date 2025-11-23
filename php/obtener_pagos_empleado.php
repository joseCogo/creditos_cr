<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$fecha = $_GET['fecha'] ?? date('Y-m-d');

$sql = "SELECT p.id, p.fecha_pago, p.monto_pagado, p.metodo_pago, p.observacion,
        c.nombre as cliente_nombre, c.cedula as cliente_cedula
        FROM pagos p
        INNER JOIN prestamos pr ON p.prestamo_id = pr.id
        INNER JOIN clientes c ON pr.cliente_id = c.id
        WHERE p.usuario_id = ? AND DATE(p.fecha_pago) = ?
        ORDER BY p.fecha_pago DESC";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "is", $usuario_id, $fecha);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pagos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pagos[] = $row;
}

echo json_encode(['success' => true, 'pagos' => $pagos]);
mysqli_close($conexion);
?>
