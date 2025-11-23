<?php
header('Content-Type: application/json');
include("conexion.php");

try {
    // Ingresos de hoy
    $sql_hoy = "SELECT COALESCE(SUM(monto_pagado), 0) as total 
                FROM pagos 
                WHERE DATE(fecha_pago) = CURDATE()";
    $result_hoy = mysqli_query($conexion, $sql_hoy);
    $ingresos_hoy = mysqli_fetch_assoc($result_hoy)['total'];

    // Ingresos de la semana
    $sql_semana = "SELECT COALESCE(SUM(monto_pagado), 0) as total 
                   FROM pagos 
                   WHERE YEARWEEK(fecha_pago, 1) = YEARWEEK(CURDATE(), 1)";
    $result_semana = mysqli_query($conexion, $sql_semana);
    $ingresos_semana = mysqli_fetch_assoc($result_semana)['total'];

    // Ingresos del mes
    $sql_mes = "SELECT COALESCE(SUM(monto_pagado), 0) as total 
                FROM pagos 
                WHERE YEAR(fecha_pago) = YEAR(CURDATE()) 
                AND MONTH(fecha_pago) = MONTH(CURDATE())";
    $result_mes = mysqli_query($conexion, $sql_mes);
    $ingresos_mes = mysqli_fetch_assoc($result_mes)['total'];

    // Actividad de los últimos 7 días
    $sql_actividad = "SELECT 
                        DATE(fecha_pago) as fecha,
                        COUNT(*) as num_pagos,
                        SUM(monto_pagado) as total
                      FROM pagos
                      WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DATE(fecha_pago)
                      ORDER BY fecha DESC";
    $result_actividad = mysqli_query($conexion, $sql_actividad);
    
    $actividad_7dias = [];
    while ($row = mysqli_fetch_assoc($result_actividad)) {
        $actividad_7dias[] = $row;
    }

    $reportes = [
        'ingresos_hoy' => floatval($ingresos_hoy),
        'ingresos_semana' => floatval($ingresos_semana),
        'ingresos_mes' => floatval($ingresos_mes),
        'actividad_7dias' => $actividad_7dias
    ];

    echo json_encode($reportes);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conexion);
?>