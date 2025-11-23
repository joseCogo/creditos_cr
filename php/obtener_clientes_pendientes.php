<?php
header('Content-Type: application/json');
include("conexion.php");

try {
    // Obtener la fecha actual
    $fecha_actual = date('Y-m-d');
    
    // Consulta para obtener préstamos activos con información del cliente
    $sql = "SELECT 
                p.id as prestamo_id,
                p.cliente_id,
                p.cuota_diaria,
                p.saldo_pendiente,
                p.estado,
                p.fecha_inicio,
                c.cedula,
                c.nombre as cliente_nombre,
                c.telefono,
                c.direccion,
                DATEDIFF(CURDATE(), p.fecha_inicio) as dias_transcurridos
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            WHERE p.estado = 'activo' AND p.saldo_pendiente > 0
            ORDER BY c.nombre ASC";
    
    $resultado = mysqli_query($conexion, $sql);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . mysqli_error($conexion));
    }
    
    $clientes_pendientes = [];
    
    while ($prestamo = mysqli_fetch_assoc($resultado)) {
        // Verificar si el cliente ha pagado HOY
        $sql_pagos_hoy = "SELECT 
                            COALESCE(SUM(monto_pagado), 0) as total_pagado_hoy
                          FROM pagos 
                          WHERE prestamo_id = ? 
                          AND DATE(fecha_pago) = ?";
        
        $stmt = mysqli_prepare($conexion, $sql_pagos_hoy);
        mysqli_stmt_bind_param($stmt, "is", $prestamo['prestamo_id'], $fecha_actual);
        mysqli_stmt_execute($stmt);
        $result_pagos = mysqli_stmt_get_result($stmt);
        $pago_hoy = mysqli_fetch_assoc($result_pagos);
        
        $total_pagado_hoy = floatval($pago_hoy['total_pagado_hoy']);
        $cuota_diaria = floatval($prestamo['cuota_diaria']);
        
        // Si NO ha pagado hoy O si pagó menos de la cuota, agregar a pendientes
        if ($total_pagado_hoy < $cuota_diaria) {
            // Calcular días de mora (días sin pagar)
            $sql_ultimo_pago = "SELECT MAX(DATE(fecha_pago)) as ultimo_pago 
                               FROM pagos 
                               WHERE prestamo_id = ?";
            
            $stmt_ultimo = mysqli_prepare($conexion, $sql_ultimo_pago);
            mysqli_stmt_bind_param($stmt_ultimo, "i", $prestamo['prestamo_id']);
            mysqli_stmt_execute($stmt_ultimo);
            $result_ultimo = mysqli_stmt_get_result($stmt_ultimo);
            $ultimo_pago_data = mysqli_fetch_assoc($result_ultimo);
            
            $dias_mora = 0;
            
            if ($ultimo_pago_data['ultimo_pago']) {
                // Calcular días desde el último pago
                $fecha_ultimo_pago = new DateTime($ultimo_pago_data['ultimo_pago']);
                $fecha_hoy = new DateTime($fecha_actual);
                $diferencia = $fecha_hoy->diff($fecha_ultimo_pago);
                $dias_mora = $diferencia->days;
            } else {
                // Si nunca ha pagado, contar desde la fecha de inicio
                $fecha_inicio = new DateTime($prestamo['fecha_inicio']);
                $fecha_hoy = new DateTime($fecha_actual);
                $diferencia = $fecha_hoy->diff($fecha_inicio);
                $dias_mora = $diferencia->days;
            }
            
            // Agregar información adicional
            $cliente_pendiente = [
                'prestamo_id' => $prestamo['prestamo_id'],
                'cliente_id' => $prestamo['cliente_id'],
                'cedula' => $prestamo['cedula'],
                'cliente_nombre' => $prestamo['cliente_nombre'],
                'telefono' => $prestamo['telefono'],
                'direccion' => $prestamo['direccion'],
                'cuota_diaria' => $prestamo['cuota_diaria'],
                'saldo_pendiente' => $prestamo['saldo_pendiente'],
                'estado' => $prestamo['estado'],
                'dias_mora' => $dias_mora,
                'pagado_hoy' => $total_pagado_hoy,
                'falta_pagar' => $cuota_diaria - $total_pagado_hoy
            ];
            
            $clientes_pendientes[] = $cliente_pendiente;
        }
    }
    
    echo json_encode([
        'success' => true,
        'clientes' => $clientes_pendientes,
        'fecha_consulta' => $fecha_actual,
        'total_pendientes' => count($clientes_pendientes)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'clientes' => []
    ]);
}

mysqli_close($conexion);
?>