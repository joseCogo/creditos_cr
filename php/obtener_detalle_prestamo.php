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

    echo json_encode([
        'success' => true,
        'prestamo' => $prestamo,
        'pagos' => $pagos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conexion);
?>