<?php
header('Content-Type: application/json');
include("conexion.php");

$prestamo_id = $_GET['id'] ?? '';

if (empty($prestamo_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de préstamo no proporcionado']);
    exit;
}

try {
    // 1. Obtener información del préstamo
    $sql_prestamo = "SELECT 
                        p.*,
                        c.nombre as cliente_nombre,
                        c.cedula,
                        c.telefono,
                        c.direccion
                      FROM prestamos p
                      INNER JOIN clientes c ON p.cliente_id = c.id
                      WHERE p.id = ?";
    
    $stmt = mysqli_prepare($conexion, $sql_prestamo);
    mysqli_stmt_bind_param($stmt, "i", $prestamo_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $prestamo = mysqli_fetch_assoc($result);

    if (!$prestamo) {
        echo json_encode(['success' => false, 'message' => 'Préstamo no encontrado']);
        exit;
    }

    // 2. Obtener pagos del préstamo Y CALCULAR EL TOTAL PAGADO REAL
    $sql_pagos = "SELECT 
                    pg.*,
                    u.nombre as cobrador_nombre
                  FROM pagos pg
                  LEFT JOIN usuarios u ON pg.usuario_id = u.id
                  WHERE pg.prestamo_id = ?
                  ORDER BY pg.fecha_pago DESC";
    
    $stmt_pagos = mysqli_prepare($conexion, $sql_pagos);
    mysqli_stmt_bind_param($stmt_pagos, "i", $prestamo_id);
    mysqli_stmt_execute($stmt_pagos);
    $result_pagos = mysqli_stmt_get_result($stmt_pagos);

    $pagos = [];
    $total_pagado_real = 0; // Acumulador

    while ($pago = mysqli_fetch_assoc($result_pagos)) {
        $total_pagado_real += floatval($pago['monto_pagado']); // Sumar cada pago
        $pagos[] = $pago;
    }

    // 🔥 CORRECCIÓN: Recalcular el saldo pendiente en vivo
    // Saldo Real = Monto Total del Préstamo - Suma de todos los pagos
    $saldo_real_calculado = floatval($prestamo['monto_total']) - $total_pagado_real;

    // Sobrescribimos el valor que viene de la BD con el cálculo real
    // (Opcional: Si es negativo por error, lo dejamos en 0)
    $prestamo['saldo_pendiente'] = max(0, $saldo_real_calculado);


    // 3. Obtener información de la boleta
    $sql_boleta = "SELECT valor_boleta, numero_boleta, boleta_descontada, fecha_descuento, 
                          gano_rifa, fecha_rifa, observacion_rifa 
                   FROM boletas_prestamos 
                   WHERE prestamo_id = ?";
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "i", $prestamo_id);
    mysqli_stmt_execute($stmt_boleta);
    $result_boleta = mysqli_stmt_get_result($stmt_boleta);
    $boleta = mysqli_fetch_assoc($result_boleta);

    // Construir respuesta
    $response = [
        'success' => true,
        'prestamo' => $prestamo,
        'pagos' => $pagos,
        'boleta' => $boleta ? [
            'valor_boleta' => floatval($boleta['valor_boleta']),
            'numero_boleta' => $boleta['numero_boleta'],
            'boleta_descontada' => (bool)$boleta['boleta_descontada'],
            'fecha_descuento' => $boleta['fecha_descuento'],
            'gano_rifa' => (bool)$boleta['gano_rifa'],
            'fecha_rifa' => $boleta['fecha_rifa'],
            'observacion_rifa' => $boleta['observacion_rifa']
        ] : null,
        // Debug: enviamos el total pagado calculado para verificar
        'debug_total_pagado' => $total_pagado_real 
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conexion);
?>