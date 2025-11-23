<?php
header('Content-Type: application/json');
include("conexion.php");

// Total prestado
$sql_prestado = "SELECT COALESCE(SUM(monto), 0) as total FROM prestamos WHERE estado IN ('activo', 'cancelado')";
$result_prestado = mysqli_query($conexion, $sql_prestado);
$total_prestado = mysqli_fetch_assoc($result_prestado)['total'];

// Total recuperado
$sql_recuperado = "SELECT COALESCE(SUM(monto_pagado), 0) as total FROM pagos";
$result_recuperado = mysqli_query($conexion, $sql_recuperado);
$total_recuperado = mysqli_fetch_assoc($result_recuperado)['total'];

// Clientes activos (con préstamos activos)
$sql_activos = "SELECT COUNT(DISTINCT cliente_id) as total FROM prestamos WHERE estado = 'activo'";
$result_activos = mysqli_query($conexion, $sql_activos);
$clientes_activos = mysqli_fetch_assoc($result_activos)['total'];

// Clientes morosos (préstamos vencidos)
$sql_morosos = "SELECT COUNT(DISTINCT cliente_id) as total FROM prestamos WHERE estado = 'activo' AND fecha_fin < CURDATE()";
$result_morosos = mysqli_query($conexion, $sql_morosos);
$clientes_morosos = mysqli_fetch_assoc($result_morosos)['total'];

// Pagos últimos 7 días
$sql_pagos_7dias = "SELECT DATE(fecha_pago) as fecha, SUM(monto_pagado) as total 
                    FROM pagos 
                    WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE(fecha_pago)
                    ORDER BY fecha ASC";
$result_7dias = mysqli_query($conexion, $sql_pagos_7dias);
$pagos_7dias = [];
while ($row = mysqli_fetch_assoc($result_7dias)) {
    $pagos_7dias[] = $row;
}

$estadisticas = [
    'total_prestado' => $total_prestado,
    'total_recuperado' => $total_recuperado,
    'clientes_activos' => $clientes_activos,
    'clientes_morosos' => $clientes_morosos,
    'pagos_7dias' => $pagos_7dias
];

echo json_encode($estadisticas);
mysqli_close($conexion);
?>
