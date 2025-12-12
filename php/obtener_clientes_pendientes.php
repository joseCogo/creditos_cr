<?php
header('Content-Type: application/json');
include("conexion.php");

try {
    $fecha_actual = date('Y-m-d');
    
    // 1. Obtener préstamos activos
    $sql = "SELECT 
                p.id as prestamo_id,
                p.cliente_id,
                p.cuota_diaria as valor_cuota, 
                p.saldo_pendiente,
                p.periodicidad,
                p.fecha_inicio,
                p.monto_total,
                c.cedula,
                c.nombre as cliente_nombre,
                c.telefono,
                c.direccion
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            WHERE p.estado = 'activo' AND p.saldo_pendiente > 0
            ORDER BY c.nombre ASC";
    
    $resultado = mysqli_query($conexion, $sql);
    
    $clientes_atrasados = [];
    
    while ($prestamo = mysqli_fetch_assoc($resultado)) {
        
        // 2. Definir días del periodo
        $dias_periodo = 1; // Default diario
        if ($prestamo['periodicidad'] === 'semanal') $dias_periodo = 7;
        if ($prestamo['periodicidad'] === 'quincenal') $dias_periodo = 15;

        // 3. Obtener PAGOS REALES (Lo necesitamos para calcular la próxima fecha)
        $sql_pagos = "SELECT COALESCE(SUM(monto_pagado), 0) as total_pagado FROM pagos WHERE prestamo_id = ?";
        $stmt = mysqli_prepare($conexion, $sql_pagos);
        mysqli_stmt_bind_param($stmt, "i", $prestamo['prestamo_id']);
        mysqli_stmt_execute($stmt);
        $pago_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $total_pagado_real = floatval($pago_data['total_pagado']);

        // --- A. CALCULAR PRÓXIMA FECHA DE PAGO (Lógica Nueva) ---
        // Calculamos cuántas cuotas enteras ha cubierto el dinero pagado.
        // Ejemplo: Si cuota es 100 y pagó 250, cubrió 2 cuotas. Va por la 3.
        $cuotas_pagadas_completas = floor($total_pagado_real / floatval($prestamo['valor_cuota']));
        
        // Calculamos los días a sumar desde la fecha de inicio
        $dias_a_sumar = $cuotas_pagadas_completas * $dias_periodo;
        
        $fecha_obj = new DateTime($prestamo['fecha_inicio']);
        $fecha_obj->modify("+$dias_a_sumar days");
        $proximo_pago = $fecha_obj->format('Y-m-d');
        // -------------------------------------------------------

        // --- B. CALCULAR SI ESTÁ ATRASADO (Tu Lógica Original) ---
        
        // Calcular tiempo transcurrido para saber cuánto DEBERÍA haber pagado
        $fecha_inicio = new DateTime($prestamo['fecha_inicio']);
        $fecha_hoy = new DateTime($fecha_actual);
        $diferencia = $fecha_inicio->diff($fecha_hoy);
        $dias_transcurridos = $diferencia->days;

        // Si la fecha inicio es futuro, saltamos
        if ($fecha_hoy < $fecha_inicio) continue;

        // Fórmula de cuotas esperadas por tiempo
        $ciclos_pasados = floor($dias_transcurridos / $dias_periodo);
        $cuotas_esperadas = 1 + $ciclos_pasados; 

        // Calcular dinero esperado
        $monto_esperado = $cuotas_esperadas * floatval($prestamo['valor_cuota']);
        
        // Validar tope
        if ($monto_esperado > $prestamo['monto_total']) {
            $monto_esperado = floatval($prestamo['monto_total']);
        }

        // Determinar si está atrasado (Deuda > 0)
        if ($total_pagado_real < ($monto_esperado - 100)) {
            
            $deuda_mora = $monto_esperado - $total_pagado_real;
            
            // Calcular número de cuotas atrasadas
            $cuotas_atrasadas = ceil($deuda_mora / $prestamo['valor_cuota']);

            $clientes_atrasados[] = [
                'prestamo_id' => $prestamo['prestamo_id'],
                'cedula' => $prestamo['cedula'],
                'cliente_nombre' => $prestamo['cliente_nombre'],
                'telefono' => $prestamo['telefono'],
                'valor_cuota' => $prestamo['valor_cuota'],
                'falta_pagar' => $deuda_mora,
                'saldo_total' => $prestamo['saldo_pendiente'],
                'cuotas_atrasadas' => $cuotas_atrasadas,
                'periodicidad' => ucfirst($prestamo['periodicidad']),
                'proximo_pago' => $proximo_pago // <--- Enviamos la nueva fecha calculada
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'clientes' => $clientes_atrasados,
        'fecha_consulta' => $fecha_actual,
        'total_pendientes' => count($clientes_atrasados)
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