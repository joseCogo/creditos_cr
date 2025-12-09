<?php
session_start();
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json');
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$tipo_reporte = $_GET['tipo'] ?? 'diario'; // diario, semanal, mensual, personalizado
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Calcular fechas según el tipo de reporte
switch ($tipo_reporte) {
    case 'diario':
        $fecha_inicio = date('Y-m-d');
        $fecha_fin = date('Y-m-d');
        break;
    
    case 'semanal':
        $fecha_inicio = date('Y-m-d', strtotime('-7 days'));
        $fecha_fin = date('Y-m-d');
        break;
    
    case 'mensual':
        $fecha_inicio = date('Y-m-01'); // Primer día del mes
        $fecha_fin = date('Y-m-d');
        break;
}

// Obtener movimientos de caja
$sql_movimientos = "SELECT m.*, u.nombre as usuario_nombre,
                    CASE 
                        WHEN m.referencia LIKE 'PRESTAMO-%' THEN 
                            (SELECT c.cedula FROM prestamos p 
                             INNER JOIN clientes c ON p.cliente_id = c.id 
                             WHERE CONCAT('PRESTAMO-', p.id) = m.referencia LIMIT 1)
                        WHEN m.referencia LIKE 'PAGO-%' THEN 
                            (SELECT c.cedula FROM pagos pag
                             INNER JOIN prestamos p ON pag.prestamo_id = p.id
                             INNER JOIN clientes c ON p.cliente_id = c.id
                             WHERE CONCAT('PAGO-', p.id) = m.referencia LIMIT 1)
                        ELSE NULL
                    END as cedula_cliente
                    FROM movimientos_caja m
                    LEFT JOIN usuarios u ON m.usuario_id = u.id
                    WHERE DATE(m.fecha_movimiento) BETWEEN ? AND ?
                    ORDER BY m.fecha_movimiento DESC";

$stmt = mysqli_prepare($conexion, $sql_movimientos);
mysqli_stmt_bind_param($stmt, "ss", $fecha_inicio, $fecha_fin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$movimientos = [];
$total_ingresos = 0;
$total_egresos = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $movimientos[] = $row;
    
    if ($row['tipo'] === 'ingreso') {
        $total_ingresos += floatval($row['monto']);
    } else {
        $total_egresos += floatval($row['monto']);
    }
}

// Obtener saldo inicial (al inicio del período)
$sql_saldo_inicial = "SELECT saldo_actual FROM caja WHERE id = 1";
$result_saldo = mysqli_query($conexion, $sql_saldo_inicial);
$saldo_actual = mysqli_fetch_assoc($result_saldo)['saldo_actual'];

// Calcular saldo al inicio del período
$sql_movimientos_previos = "SELECT 
                            COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) as ingresos_previos,
                            COALESCE(SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END), 0) as egresos_previos
                            FROM movimientos_caja
                            WHERE DATE(fecha_movimiento) < ?";

$stmt_previos = mysqli_prepare($conexion, $sql_movimientos_previos);
mysqli_stmt_bind_param($stmt_previos, "s", $fecha_inicio);
mysqli_stmt_execute($stmt_previos);
$result_previos = mysqli_stmt_get_result($stmt_previos);
$previos = mysqli_fetch_assoc($result_previos);

$saldo_inicial = $saldo_actual - ($total_ingresos - $total_egresos);
$saldo_final = $saldo_actual;

// Agrupar por fecha
$movimientos_por_fecha = [];
foreach ($movimientos as $mov) {
    $fecha = date('Y-m-d', strtotime($mov['fecha_movimiento']));
    if (!isset($movimientos_por_fecha[$fecha])) {
        $movimientos_por_fecha[$fecha] = [
            'fecha' => $fecha,
            'ingresos' => 0,
            'egresos' => 0,
            'movimientos' => []
        ];
    }
    
    $movimientos_por_fecha[$fecha]['movimientos'][] = $mov;
    
    if ($mov['tipo'] === 'ingreso') {
        $movimientos_por_fecha[$fecha]['ingresos'] += floatval($mov['monto']);
    } else {
        $movimientos_por_fecha[$fecha]['egresos'] += floatval($mov['monto']);
    }
}

$response = [
    'success' => true,
    'tipo_reporte' => $tipo_reporte,
    'fecha_inicio' => $fecha_inicio,
    'fecha_fin' => $fecha_fin,
    'saldo_inicial' => $saldo_inicial,
    'total_ingresos' => $total_ingresos,
    'total_egresos' => $total_egresos,
    'saldo_final' => $saldo_final,
    'balance' => $total_ingresos - $total_egresos,
    'movimientos' => $movimientos,
    'movimientos_por_fecha' => array_values($movimientos_por_fecha),
    'total_movimientos' => count($movimientos)
];

echo json_encode($response);
mysqli_close($conexion);
?>