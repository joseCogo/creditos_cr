<?php
header('Content-Type: application/json');
include("conexion.php");

$pago_id = $_GET['id'] ?? '';

if (empty($pago_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de pago no proporcionado']);
    exit;
}

try {
    // 1. Obtener información completa del pago y del préstamo
    $sql = "SELECT 
                pg.id as pago_id,
                pg.fecha_pago,
                pg.monto_pagado,
                pg.metodo_pago,
                pg.observacion,
                p.id as prestamo_id,
                p.monto_total as monto_prestamo_total, -- Usamos monto_total (con intereses)
                p.cuota_diaria,
                -- p.saldo_pendiente,  <-- NO USAMOS ESTE, ES EL SALDO ACTUAL
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

    // =================================================================================
    // 2. LÓGICA DE SALDO HISTÓRICO (CORRECCIÓN IMPORTANTE)
    // Calculamos cuánto se había pagado hasta ese momento exacto.
    // =================================================================================
    
    $sql_historial = "SELECT SUM(monto_pagado) as total_acumulado 
                      FROM pagos 
                      WHERE prestamo_id = ? AND id <= ?";
    
    $stmt_hist = mysqli_prepare($conexion, $sql_historial);
    mysqli_stmt_bind_param($stmt_hist, "ii", $data['prestamo_id'], $pago_id);
    mysqli_stmt_execute($stmt_hist);
    $res_hist = mysqli_stmt_get_result($stmt_hist);
    $fila_hist = mysqli_fetch_assoc($res_hist);
    
    // Total pagado hasta la fecha de este recibo
    $total_pagado_al_momento = floatval($fila_hist['total_acumulado']);
    $monto_total_prestamo = floatval($data['monto_prestamo_total']);
    
    // El saldo pendiente EN ESE MOMENTO es: Total Préstamo - Lo que se lleva pagado hasta ese ticket
    $saldo_historico = $monto_total_prestamo - $total_pagado_al_momento;
    
    // Seguridad para no mostrar negativos
    $saldo_historico = max(0, $saldo_historico);

    // =================================================================================

    // Obtener información de la boleta
    $sql_boleta = "SELECT valor_boleta, numero_boleta, boleta_descontada, gano_rifa, fecha_rifa, observacion_rifa 
                   FROM boletas_prestamos WHERE prestamo_id = ?";
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "i", $data['prestamo_id']);
    mysqli_stmt_execute($stmt_boleta);
    $result_boleta = mysqli_stmt_get_result($stmt_boleta);
    $boleta = mysqli_fetch_assoc($result_boleta);

    // Verificar si es el primer pago (para lógica de descuento de boleta)
    $sql_count = "SELECT COUNT(*) as total FROM pagos 
                  WHERE prestamo_id = ? AND id < ?";
    $stmt_count = mysqli_prepare($conexion, $sql_count);
    mysqli_stmt_bind_param($stmt_count, "ii", $data['prestamo_id'], $pago_id);
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $count_data = mysqli_fetch_assoc($result_count);
    $es_primer_pago = ($count_data['total'] == 0); // Si hay 0 pagos antes de este, es el primero

    // Calcular tipo de pago
    $valor_boleta = $boleta ? floatval($boleta['valor_boleta']) : 0;
    $cuota_diaria = floatval($data['cuota_diaria']);
    
    // Determinar etiqueta de pago completo/parcial
    // Nota: Usamos un margen de 100 pesos por posibles decimales
    if ($es_primer_pago && $valor_boleta > 0) {
        $cuota_esperada = $cuota_diaria + $valor_boleta; // En el primer pago se espera Cuota + Boleta (según tu lógica de registrar_prestamo)
        // Ojo: En tu lógica anterior restabas la boleta, pero en el registro la sumabas al primer cobro. 
        // Ajusta esto según tu preferencia visual. Aquí asumo que si paga lo que se le pide es "COMPLETA".
        $tipo_pago = ($data['monto_pagado'] >= ($cuota_diaria - 100)) ? 'CUOTA COMPLETA' : 'PAGO PARCIAL';
    } else {
        $tipo_pago = ($data['monto_pagado'] >= ($cuota_diaria - 100)) ? 'CUOTA COMPLETA' : 'PAGO PARCIAL';
    }

    // Generar número de comprobante
    $numero_comprobante = str_pad($data['pago_id'], 6, '0', STR_PAD_LEFT);

    $comprobante = [
        'success' => true,
        'numero_comprobante' => $numero_comprobante,
        'fecha' => date('d/m/Y h:i A', strtotime($data['fecha_pago'])), // Formato amigable
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
            'cuota_diaria' => $cuota_diaria,
            'saldo_pendiente' => $saldo_historico, // <--- AQUÍ USAMOS EL VALOR CALCULADO
            'interes' => $data['interes']
        ],
        'cobrador' => $data['cobrador_nombre'] ?? 'Sistema',
        'es_primer_pago' => $es_primer_pago,
        'boleta' => ($boleta && $valor_boleta > 0) ? [
            'valor' => $valor_boleta,
            'numero' => $boleta['numero_boleta'], // Agregué el número de boleta
            'descontada' => (bool)$boleta['boleta_descontada'],
            'cuota_esperada' => $cuota_diaria // Solo informativo
        ] : null
    ];

    echo json_encode($comprobante);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>