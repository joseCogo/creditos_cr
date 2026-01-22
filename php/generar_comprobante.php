<?php
header('Content-Type: application/json');
// Desactivar errores visuales
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");

$pago_id = $_GET['id'] ?? '';

if (empty($pago_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de pago no proporcionado']);
    exit;
}

try {
    // 1. Obtener información del pago y préstamo (SIN cuota_diaria)
    $sql = "SELECT 
                pg.id as pago_id,
                pg.fecha_pago,
                pg.monto_pagado,
                pg.metodo_pago,
                pg.observacion,
                p.id as prestamo_id,
                p.monto_total as monto_prestamo_total,
                p.interes,
                c.id as cliente_id,
                c.nombre as cliente_nombre,
                c.cedula,
                c.telefono,
                c.direccion,
                u.nombre as cobrador_nombre
            FROM pagos pg
            INNER JOIN prestamos p ON pg.prestamo_id = p.id
            INNER JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN usuarios u ON pg.usuario_id = u.id
            WHERE pg.id = ?";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $pago_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
        exit;
    }

    // 2. Calcular saldo histórico (Cuánto debía en ese momento)
    $sql_historial = "SELECT SUM(monto_pagado) as total_acumulado 
                      FROM pagos 
                      WHERE prestamo_id = ? AND id <= ?";
    
    $stmt_hist = mysqli_prepare($conexion, $sql_historial);
    mysqli_stmt_bind_param($stmt_hist, "ii", $data['prestamo_id'], $pago_id);
    mysqli_stmt_execute($stmt_hist);
    $res_hist = mysqli_stmt_get_result($stmt_hist);
    $fila_hist = mysqli_fetch_assoc($res_hist);
    
    $total_pagado_al_momento = floatval($fila_hist['total_acumulado']);
    $monto_total_prestamo = floatval($data['monto_prestamo_total']);
    
    $saldo_historico = $monto_total_prestamo - $total_pagado_al_momento;
    $saldo_historico = max(0, $saldo_historico);

    // 3. Determinar etiqueta simple
    $tipo_pago = ($saldo_historico <= 0) ? 'CANCELACIÓN' : 'ABONO';

    $numero_comprobante = str_pad($data['pago_id'], 6, '0', STR_PAD_LEFT);

    // 4. Respuesta limpia sin boletas ni cuotas
    $comprobante = [
        'success' => true,
        'numero_comprobante' => $numero_comprobante,
        'fecha' => date('d/m/Y h:i A', strtotime($data['fecha_pago'])),
        'tipo_pago' => $tipo_pago,
        'pago' => [
            'id' => $data['pago_id'],
            'monto' => floatval($data['monto_pagado']),
            'metodo' => ucfirst($data['metodo_pago']),
            'observacion' => $data['observacion']
        ],
        'cliente' => [
            'nombre' => $data['cliente_nombre'],
            'cedula' => $data['cedula'],
            'telefono' => $data['telefono'],
            'direccion' => $data['direccion']
        ],
        'prestamo' => [
            'id' => $data['prestamo_id'],
            'monto_total' => floatval($data['monto_prestamo_total']),
            'saldo_pendiente' => $saldo_historico, 
            'interes' => $data['interes']
        ],
        'cobrador' => $data['cobrador_nombre'] ?? 'Sistema',
        'boleta' => null // Ya no hay boletas
    ];

    echo json_encode($comprobante);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>