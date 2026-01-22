<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");

$cedula = $_GET['cedula'] ?? '';

if (empty($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Cédula requerida']);
    exit;
}

// 1. Obtener el cliente
$sql_cliente = "SELECT id, nombre, telefono, direccion, correo FROM clientes WHERE cedula = ?";
$stmt_cliente = mysqli_prepare($conexion, $sql_cliente);
mysqli_stmt_bind_param($stmt_cliente, "s", $cedula);
mysqli_stmt_execute($stmt_cliente);
$result_cliente = mysqli_stmt_get_result($stmt_cliente);
$cliente = mysqli_fetch_assoc($result_cliente);

if (!$cliente) {
    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
    exit;
}

// 2. Obtener préstamos activos
// Eliminadas columnas inexistentes: cuotas, cuota_diaria
$sql_prestamos = "SELECT 
                    p.id, 
                    p.monto, 
                    p.interes, 
                    p.fecha_inicio, 
                    p.fecha_fin, -- Fecha último movimiento 
                    p.monto_total, 
                    p.estado,
                    (SELECT COALESCE(SUM(monto_pagado), 0) FROM pagos WHERE prestamo_id = p.id) as total_pagado_real
                  FROM prestamos p
                  WHERE p.cliente_id = ? AND p.estado IN ('activo', 'mora')
                  ORDER BY p.fecha_inicio DESC";

$stmt_prestamos = mysqli_prepare($conexion, $sql_prestamos);
mysqli_stmt_bind_param($stmt_prestamos, "i", $cliente['id']);
mysqli_stmt_execute($stmt_prestamos);
$result_prestamos = mysqli_stmt_get_result($stmt_prestamos);

$prestamos = [];
while ($row = mysqli_fetch_assoc($result_prestamos)) {
    
    // Cálculo en vivo del saldo
    $monto_total = floatval($row['monto_total']);
    $pagado_real = floatval($row['total_pagado_real']);
    $saldo_calculado = $monto_total - $pagado_real;

    $row['saldo_pendiente'] = max(0, $saldo_calculado);

    $prestamos[] = $row;
}

echo json_encode([
    'success' => true,
    'cliente' => $cliente,
    'prestamos' => $prestamos
]);

mysqli_close($conexion);
?>