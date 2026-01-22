<?php
// archivo: php/obtener_reportes.php (VERSIÓN COMPLETA CON GRÁFICOS)
header('Content-Type: application/json');
date_default_timezone_set('America/Bogota');
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");
include("verificar_sesion.php");

try {
    $hoy = date('Y-m-d');
    $inicio_semana = date('Y-m-d', strtotime('monday this week'));
    $inicio_mes = date('Y-m-01');

    // ==========================================
    // 1. TARJETAS DE RESUMEN
    // ==========================================
    
    // Ingresos HOY
    $sql_hoy = "SELECT COALESCE(SUM(monto_pagado), 0) as total 
                FROM pagos 
                WHERE DATE(fecha_pago) = ?";
    $stmt_hoy = mysqli_prepare($conexion, $sql_hoy);
    mysqli_stmt_bind_param($stmt_hoy, "s", $hoy);
    mysqli_stmt_execute($stmt_hoy);
    $result_hoy = mysqli_stmt_get_result($stmt_hoy);
    $ingresos_hoy = mysqli_fetch_assoc($result_hoy)['total'];

    // Ingresos SEMANA
    $sql_semana = "SELECT COALESCE(SUM(monto_pagado), 0) as total 
                   FROM pagos 
                   WHERE DATE(fecha_pago) >= ?";
    $stmt_semana = mysqli_prepare($conexion, $sql_semana);
    mysqli_stmt_bind_param($stmt_semana, "s", $inicio_semana);
    mysqli_stmt_execute($stmt_semana);
    $result_semana = mysqli_stmt_get_result($stmt_semana);
    $ingresos_semana = mysqli_fetch_assoc($result_semana)['total'];

    // Ingresos MES
    $sql_mes = "SELECT COALESCE(SUM(monto_pagado), 0) as total 
                FROM pagos 
                WHERE DATE(fecha_pago) >= ?";
    $stmt_mes = mysqli_prepare($conexion, $sql_mes);
    mysqli_stmt_bind_param($stmt_mes, "s", $inicio_mes);
    mysqli_stmt_execute($stmt_mes);
    $result_mes = mysqli_stmt_get_result($stmt_mes);
    $ingresos_mes = mysqli_fetch_assoc($result_mes)['total'];

    // ==========================================
    // 2. TABLA DE ACTIVIDAD (Últimos 7 días)
    // ==========================================
    $sql_actividad = "SELECT 
                        DATE(fecha_pago) as fecha,
                        COUNT(*) as numero_pagos,
                        SUM(monto_pagado) as total_recaudado
                      FROM pagos
                      WHERE DATE(fecha_pago) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DATE(fecha_pago)
                      ORDER BY fecha DESC";
    
    $result_actividad = mysqli_query($conexion, $sql_actividad);
    $actividad = [];
    
    while ($row = mysqli_fetch_assoc($result_actividad)) {
        $actividad[] = [
            'fecha' => date('d/m/Y', strtotime($row['fecha'])),
            'numero_pagos' => (int)$row['numero_pagos'],
            'total_recaudado' => (float)$row['total_recaudado']
        ];
    }

    // ==========================================
    // 3. DATOS PARA GRÁFICO: Dinero en Caja vs En la Calle
    // ==========================================
    $sql_caja = "SELECT saldo_actual FROM caja WHERE id = 1";
    $result_caja = mysqli_query($conexion, $sql_caja);
    $saldo_caja = mysqli_fetch_assoc($result_caja)['saldo_actual'] ?? 0;

    $sql_prestado = "SELECT COALESCE(SUM(monto), 0) as total_prestado,
                            COALESCE(SUM(saldo_pendiente), 0) as total_pendiente
                     FROM prestamos 
                     WHERE estado = 'activo'";
    $result_prestado = mysqli_query($conexion, $sql_prestado);
    $row_prestado = mysqli_fetch_assoc($result_prestado);
    
    $capital_prestado = floatval($row_prestado['total_prestado']);
    $capital_recuperado = $capital_prestado - floatval($row_prestado['total_pendiente']);

    // ==========================================
    // 4. DATOS PARA GRÁFICO: Ingresos últimos 7 días
    // ==========================================
    $sql_ingresos_7dias = "SELECT 
                            DATE(fecha_pago) as fecha,
                            SUM(monto_pagado) as total
                          FROM pagos
                          WHERE DATE(fecha_pago) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                          GROUP BY DATE(fecha_pago)
                          ORDER BY fecha ASC";
    
    $result_ingresos = mysqli_query($conexion, $sql_ingresos_7dias);
    $ingresos_7dias = [];
    
    // Crear array con todos los últimos 7 días (incluso si no hay datos)
    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $ingresos_7dias[$fecha] = [
            'fecha' => date('d/m', strtotime($fecha)),
            'total' => 0
        ];
    }
    
    // Llenar con datos reales
    while ($row = mysqli_fetch_assoc($result_ingresos)) {
        $fecha = $row['fecha'];
        if (isset($ingresos_7dias[$fecha])) {
            $ingresos_7dias[$fecha]['total'] = (float)$row['total'];
        }
    }
    
    $ingresos_7dias = array_values($ingresos_7dias);

    // ==========================================
    // 5. DATOS PARA GRÁFICO: Estado de Préstamos
    // ==========================================
    $sql_estados = "SELECT 
                        estado,
                        COUNT(*) as cantidad,
                        SUM(saldo_pendiente) as total_pendiente
                    FROM prestamos
                    GROUP BY estado";
    
    $result_estados = mysqli_query($conexion, $sql_estados);
    $estados = [
        'activo' => ['cantidad' => 0, 'total' => 0],
        'cancelado' => ['cantidad' => 0, 'total' => 0]
    ];
    
    while ($row = mysqli_fetch_assoc($result_estados)) {
        $estado = $row['estado'];
        $estados[$estado] = [
            'cantidad' => (int)$row['cantidad'],
            'total' => (float)$row['total_pendiente']
        ];
    }

    // ==========================================
    // 6. GANANCIAS TOTALES (Intereses cobrados)
    // ==========================================
    $sql_ganancias = "SELECT 
                        COALESCE(SUM(p.monto * (p.interes / 100)), 0) as total_ganancias_teoricas,
                        COALESCE(SUM(pg.monto_pagado), 0) as total_cobrado
                      FROM prestamos p
                      LEFT JOIN pagos pg ON p.id = pg.prestamo_id";
    
    $result_ganancias = mysqli_query($conexion, $sql_ganancias);
    $row_ganancias = mysqli_fetch_assoc($result_ganancias);
    
    $ganancias_totales = floatval($row_ganancias['total_cobrado']) - $capital_recuperado;

    // ==========================================
    // RESPUESTA COMPLETA
    // ==========================================
    echo json_encode([
        'success' => true,
        
        // Tarjetas resumen
        'ingresos_hoy' => (float)$ingresos_hoy,
        'ingresos_semana' => (float)$ingresos_semana,
        'ingresos_mes' => (float)$ingresos_mes,
        
        // Tabla actividad
        'actividad' => $actividad,
        
        // Gráficos
        'grafico_capital' => [
            'en_caja' => (float)$saldo_caja,
            'prestado' => $capital_prestado,
            'recuperado' => $capital_recuperado,
            'pendiente' => $capital_prestado - $capital_recuperado
        ],
        
        'grafico_ingresos_7dias' => $ingresos_7dias,
        
        'grafico_estados' => [
            'activos' => $estados['activo']['cantidad'],
            'cancelados' => $estados['cancelado']['cantidad'],
            'monto_activo' => $estados['activo']['total']
        ],
        
        'estadisticas_extra' => [
            'ganancias_totales' => $ganancias_totales,
            'total_prestamos' => $estados['activo']['cantidad'] + $estados['cancelado']['cantidad']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reportes: ' . $e->getMessage()
    ]);
}

mysqli_close($conexion);
?>