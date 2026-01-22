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

    // 2. Obtener pagos del préstamo
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
    $total_pagado_real = 0;

    while ($pago = mysqli_fetch_assoc($result_pagos)) {
        $total_pagado_real += floatval($pago['monto_pagado']);
        $pagos[] = $pago;
    }

    // 3. Recalcular saldo (Sin lógica de boletas)
    // Saldo Real = Monto Total - Total Pagado
    $saldo_real_calculado = floatval($prestamo['monto_total']) - $total_pagado_real;
    
    // Evitar negativos visuales
    $prestamo['saldo_pendiente'] = max(0, $saldo_real_calculado);

    // 4. Construir respuesta (Enviamos 'boleta' como null explícitamente)
    echo json_encode([
        'success' => true,
        'prestamo' => $prestamo,
        'pagos' => $pagos,
        'boleta' => null 
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>