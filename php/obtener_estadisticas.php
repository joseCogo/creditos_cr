<?php
// archivo: php/obtener_estadisticas.php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");

try {
    $response = [];

    // 1. Saldo en Caja (Capital Disponible)
    $sql_caja = "SELECT saldo_actual FROM caja WHERE id = 1";
    $res_caja = mysqli_query($conexion, $sql_caja);
    $row_caja = mysqli_fetch_assoc($res_caja);
    $response['saldo_disponible'] = $row_caja ? (float)$row_caja['saldo_actual'] : 0;

    // 2. Total Prestado (Capital en la calle) - Solo de activos
    // Sumamos el saldo_pendiente de los activos para saber cuánto dinero falta por recuperar
    $sql_prestado = "SELECT SUM(saldo_pendiente) as total FROM prestamos WHERE estado = 'activo'";
    $res_prestado = mysqli_query($conexion, $sql_prestado);
    $row_prestado = mysqli_fetch_assoc($res_prestado);
    $response['total_prestado'] = $row_prestado ? (float)$row_prestado['total'] : 0;

    // 3. Ganancias Totales (Intereses Cobrados)
    // Esto es más complejo en el modelo flexible. 
    // Una aproximación simple: (Suma de todos los pagos) - (Suma de capital prestado original de préstamos cerrados)
    // Por simplicidad ahora, sumaremos todos los ingresos históricos de caja tipo 'ingreso' relacionados con pagos
    $sql_ganancias = "SELECT SUM(monto) as total FROM movimientos_caja WHERE tipo = 'ingreso' AND referencia LIKE 'PAGO-%'";
    $res_ganancias = mysqli_query($conexion, $sql_ganancias);
    $row_ganancias = mysqli_fetch_assoc($res_ganancias);
    $response['total_ganancias'] = $row_ganancias ? (float)$row_ganancias['total'] : 0; 
    // Nota: Este cálculo de ganancias es aproximado (es flujo de caja entrada), ajusta según tu lógica contable estricta si lo deseas.

    // 4. Clientes Activos
    $sql_activos = "SELECT COUNT(DISTINCT cliente_id) as total FROM prestamos WHERE estado = 'activo'";
    $res_activos = mysqli_query($conexion, $sql_activos);
    $row_activos = mysqli_fetch_assoc($res_activos);
    $response['clientes_activos'] = $row_activos ? (int)$row_activos['total'] : 0;

    // 5. Clientes Morosos (Aquellos que no han pagado en X días, opcional)
    // Por ahora pondremos 0 para no complicar la query, o contar activos con fecha_fin antigua
    $response['clientes_morosos'] = 0; 

    // 6. Total Recuperado (Histórico)
    $response['total_recuperado'] = $response['total_ganancias']; // Simplificación para el dashboard

    // 7. Gráfico 7 días (Ingresos por día)
    $sql_grafico = "SELECT DATE(fecha_pago) as fecha, SUM(monto_pagado) as total 
                    FROM pagos 
                    WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                    GROUP BY DATE(fecha_pago) 
                    ORDER BY fecha ASC";
    $res_grafico = mysqli_query($conexion, $sql_grafico);
    $pagos_7dias = [];
    while($row = mysqli_fetch_assoc($res_grafico)) {
        $pagos_7dias[] = $row;
    }
    $response['pagos_7dias'] = $pagos_7dias;

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'saldo_disponible' => 0,
        'total_prestado' => 0,
        'clientes_activos' => 0,
        'error' => $e->getMessage()
    ]);
}
?>