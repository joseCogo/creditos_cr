<?php
header('Content-Type: application/json');
include("conexion.php");

$pago_id = $_GET['id'] ?? '';

if (empty($pago_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de pago no proporcionado']);
    exit;
}

try {
    // Obtener información completa del pago
    $sql = "SELECT 
                pg.id as pago_id,
                pg.fecha_pago,
                pg.monto_pagado,
                pg.metodo_pago,
                pg.observacion,
                p.id as prestamo_id,
                p.monto as monto_prestamo,
                p.cuota_diaria,
                p.saldo_pendiente,
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

    // Obtener información de la boleta
    $sql_boleta = "SELECT valor_boleta, boleta_descontada, gano_rifa, fecha_rifa, observacion_rifa 
                   FROM boletas_prestamos WHERE prestamo_id = ?";
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "i", $data['prestamo_id']);
    mysqli_stmt_execute($stmt_boleta);
    $result_boleta = mysqli_stmt_get_result($stmt_boleta);
    $boleta = mysqli_fetch_assoc($result_boleta);

    // Verificar si es el primer pago
    $sql_count = "SELECT COUNT(*) as total FROM pagos 
                  WHERE prestamo_id = ? AND id <= ?";
    $stmt_count = mysqli_prepare($conexion, $sql_count);
    mysqli_stmt_bind_param($stmt_count, "ii", $data['prestamo_id'], $pago_id);
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $count_data = mysqli_fetch_assoc($result_count);
    $es_primer_pago = ($count_data['total'] == 1);

    // Calcular tipo de pago
    $valor_boleta = $boleta ? floatval($boleta['valor_boleta']) : 0;
    $cuota_diaria = floatval($data['cuota_diaria']);
    
    if ($es_primer_pago && $valor_boleta > 0) {
        $cuota_esperada = $cuota_diaria - $valor_boleta;
        $tipo_pago = ($data['monto_pagado'] >= $cuota_esperada) ? 'CUOTA COMPLETA' : 'PAGO PARCIAL';
    } else {
        $tipo_pago = ($data['monto_pagado'] >= $cuota_diaria) ? 'CUOTA COMPLETA' : 'PAGO PARCIAL';
    }

    // Generar número de comprobante
    $numero_comprobante = str_pad($data['pago_id'], 8, '0', STR_PAD_LEFT);

    $comprobante = [
        'success' => true,
        'numero_comprobante' => $numero_comprobante,
        'fecha' => $data['fecha_pago'],
        'tipo_pago' => $tipo_pago,
        'pago' => [
            'id' => $data['pago_id'],
            'monto' => $data['monto_pagado'],
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
            'monto_total' => $data['monto_prestamo'],
            'cuota_diaria' => $cuota_diaria,
            'saldo_pendiente' => $data['saldo_pendiente'],
            'interes' => $data['interes']
        ],
        'cobrador' => $data['cobrador_nombre'] ?? 'Sistema',
        'es_primer_pago' => $es_primer_pago,
        'boleta' => ($es_primer_pago && $boleta) ? [
            'valor' => $valor_boleta,
            'descontada' => (bool)$boleta['boleta_descontada'],
            'cuota_esperada' => $cuota_diaria - $valor_boleta
        ] : null
    ];

    echo json_encode($comprobante);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>