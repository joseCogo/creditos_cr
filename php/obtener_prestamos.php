<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
include("conexion.php");

$estado = $_GET['estado'] ?? '';

/* LOGICA DE CÁLCULO EN VIVO + AUTOCORRECCIÓN (SELF-HEALING):
   1. Traemos la suma real de pagos.
   2. Traemos el saldo que está guardado en BD.
   3. Si son diferentes, actualizamos la BD silenciosamente.
*/

$sql = "SELECT 
            p.id,
            p.cliente_id,
            p.monto,
            p.interes,
            p.cuotas,
            p.cuota_diaria,
            p.fecha_inicio,
            p.monto_total,
            p.saldo_pendiente, -- IMPORTANTE: Traer este campo para comparar
            p.estado,
            -- Columna auxiliar con la suma real de pagos
            (SELECT COALESCE(SUM(monto_pagado), 0) FROM pagos WHERE prestamo_id = p.id) as total_pagado_real,
            c.nombre as cliente_nombre,
            c.cedula as cliente_cedula
        FROM prestamos p
        INNER JOIN clientes c ON p.cliente_id = c.id";

// Aplicar filtro de estado si existe
if (!empty($estado)) {
    $sql .= " WHERE p.estado = '$estado'";
}

$sql .= " ORDER BY p.id DESC";

$result = mysqli_query($conexion, $sql);

if (!$result) {
    echo json_encode([]);
    exit;
}

$prestamos = [];

while ($row = mysqli_fetch_assoc($result)) {
    
    // 1. Obtener datos para el cálculo
    $monto_total = floatval($row['monto_total']);
    $total_pagado = floatval($row['total_pagado_real']);
    $saldo_en_bd = floatval($row['saldo_pendiente']); // El valor que está en la BD ahora mismo
    
    // 2. Calcular el saldo real matemático
    $saldo_real = $monto_total - $total_pagado;
    $saldo_real = max(0, $saldo_real); // Evitamos números negativos
    
    // 3. --- AQUÍ OCURRE LA AUTOCORRECCIÓN ---
    // Comparamos el saldo de la BD con el saldo real calculado.
    // Usamos abs() > 10 para dar un pequeño margen de error por decimales, pero detectará cambios grandes.
    if (abs($saldo_en_bd - $saldo_real) > 10) {
        
        // ¡DETECTAMOS UN ERROR EN LA BD! -> Lo corregimos ahora mismo.
        $id_prestamo = $row['id'];
        
        // Ejecutamos el UPDATE directo a la base de datos
        $update_sql = "UPDATE prestamos SET saldo_pendiente = $saldo_real WHERE id = $id_prestamo";
        mysqli_query($conexion, $update_sql);
        
        // Actualizamos el valor en la fila actual para que el usuario ya lo vea corregido en pantalla
        $row['saldo_pendiente'] = $saldo_real;
        
    } else {
        // Si la BD estaba bien (o casi bien), simplemente mostramos el saldo calculado para exactitud visual
        $row['saldo_pendiente'] = $saldo_real;
    }

    $prestamos[] = $row;
}

echo json_encode($prestamos);

mysqli_close($conexion);
?>