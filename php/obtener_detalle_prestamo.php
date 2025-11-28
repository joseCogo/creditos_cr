<?php
header('Content-Type: application/json');
include("conexion.php");

$prestamo_id = $_GET['id'] ?? '';

if (empty($prestamo_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de préstamo no proporcionado'
    ]);
    exit;
}

try {
    // Obtener información del préstamo
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
        echo json_encode([
            'success' => false,
            'message' => 'Préstamo no encontrado'
        ]);
        exit;
    }

    // Obtener pagos del préstamo
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
    while ($pago = mysqli_fetch_assoc($result_pagos)) {
        $pagos[] = $pago;
    }

    // Obtener información de la boleta (incluye numero_boleta)
    $sql_boleta = "SELECT valor_boleta, numero_boleta, boleta_descontada, fecha_descuento, 
                          gano_rifa, fecha_rifa, observacion_rifa 
                   FROM boletas_prestamos 
                   WHERE prestamo_id = ?";
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "i", $prestamo_id);
    mysqli_stmt_execute($stmt_boleta);
    $result_boleta = mysqli_stmt_get_result($stmt_boleta);
    $boleta = mysqli_fetch_assoc($result_boleta);

    // Construir respuesta única con toda la información
    $response = [
        'success' => true,
        'prestamo' => $prestamo,
        'pagos' => $pagos,
        'boleta' => $boleta ? [
            'valor_boleta' => floatval($boleta['valor_boleta']),
            'numero_boleta' => $boleta['numero_boleta'], // NUEVO
            'boleta_descontada' => (bool)$boleta['boleta_descontada'],
            'fecha_descuento' => $boleta['fecha_descuento'],
            'gano_rifa' => (bool)$boleta['gano_rifa'],
            'fecha_rifa' => $boleta['fecha_rifa'],
            'observacion_rifa' => $boleta['observacion_rifa']
        ] : null
    ];

    // Enviar respuesta UNA SOLA VEZ
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conexion);
?>