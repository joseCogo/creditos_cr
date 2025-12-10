<?php
header('Content-Type: application/json');
include("conexion.php");

// Total prestado (solo el monto inicial, sin intereses)
$sql_prestado = "SELECT COALESCE(SUM(monto), 0) as total FROM prestamos WHERE estado IN ('activo', 'cancelado')";
$result_prestado = mysqli_query($conexion, $sql_prestado);
$total_prestado = mysqli_fetch_assoc($result_prestado)['total'];

// Total recuperado (SOLO el valor de las boletas descontadas = ganancias por primera cuota)
// $sql_recuperado = "SELECT COALESCE(SUM(b.valor_boleta), 0) as total 
//                    FROM boletas_prestamos b
//                    INNER JOIN prestamos p ON b.prestamo_id = p.id
//                    WHERE b.boleta_descontada = TRUE";
// $result_recuperado = mysqli_query($conexion, $sql_recuperado);
// $total_recuperado = mysqli_fetch_assoc($result_recuperado)['total'];

$sql_recuperado = "SELECT COALESCE(SUM(cuota_diaria), 0) as total FROM prestamos";
$result_recuperado = mysqli_query($conexion, $sql_recuperado);
$total_recuperado = mysqli_fetch_assoc($result_recuperado)['total'];

// Ganancias totales (todos los pagos recibidos)
$sql_ganancias = "SELECT COALESCE(SUM(monto_pagado), 0) as total FROM pagos";
$result_ganancias = mysqli_query($conexion, $sql_ganancias);
$total_ganancias = mysqli_fetch_assoc($result_ganancias)['total'];

// Clientes activos
$sql_activos = "SELECT COUNT(DISTINCT cliente_id) as total FROM prestamos WHERE estado = 'activo'";
$result_activos = mysqli_query($conexion, $sql_activos);
$clientes_activos = mysqli_fetch_assoc($result_activos)['total'];

// Clientes morosos
$sql_morosos = "SELECT COUNT(DISTINCT cliente_id) as total FROM prestamos WHERE estado = 'activo' AND fecha_fin < CURDATE()";
$result_morosos = mysqli_query($conexion, $sql_morosos);
$clientes_morosos = mysqli_fetch_assoc($result_morosos)['total'];

// Saldo disponible en caja
$sql_saldo = "SELECT COALESCE(saldo_actual, 0) as saldo FROM caja WHERE id = 1";
$result_saldo = mysqli_query($conexion, $sql_saldo);
$saldo_disponible = 0;
if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
    $saldo_disponible = mysqli_fetch_assoc($result_saldo)['saldo'];
}

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
    'total_recuperado' => $total_recuperado,  // Solo boletas descontadas
    'total_ganancias' => $total_ganancias,     // Todos los pagos
    'clientes_activos' => $clientes_activos,
    'clientes_morosos' => $clientes_morosos,
    'saldo_disponible' => $saldo_disponible,
    'pagos_7dias' => $pagos_7dias
];

echo json_encode($estadisticas);
mysqli_close($conexion);
?>