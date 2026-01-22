<?php
// archivo: php/obtener_clientes_pendientes.php
header('Content-Type: application/json');
include("conexion.php");

// Obtenemos todos los que deben dinero (saldo > 0)
// Ordenamos por fecha_fin ASC (los que tienen fecha más vieja primero)
$sql = "SELECT 
            p.id as prestamo_id,
            c.nombre as cliente_nombre,
            c.cedula,
            c.telefono,
            p.monto_total,
            p.saldo_pendiente,
            p.fecha_inicio,
            p.fecha_fin as ultimo_pago
        FROM prestamos p
        INNER JOIN clientes c ON p.cliente_id = c.id
        WHERE p.estado = 'activo' AND p.saldo_pendiente > 0
        ORDER BY p.fecha_fin ASC";

$result = mysqli_query($conexion, $sql);
$clientes = [];
$hoy = new DateTime();

while ($row = mysqli_fetch_assoc($result)) {
    // Calcular días sin pagar
    $ultimo_pago = new DateTime($row['ultimo_pago']);
    $dias_sin_pagar = $hoy->diff($ultimo_pago)->days;

    $clientes[] = [
        'prestamo_id' => $row['prestamo_id'],
        'cliente_nombre' => $row['cliente_nombre'],
        'cedula' => $row['cedula'],
        'saldo_total' => $row['saldo_pendiente'], // Lo que debe
        'dias_sin_pagar' => $dias_sin_pagar,
        'ultimo_pago' => $row['ultimo_pago'],
        // Campos para compatibilidad con el frontend anterior (para que no rompa la tabla)
        'falta_pagar' => $row['saldo_pendiente'], 
        'valor_cuota' => 0, 
        'cuotas_atrasadas' => 0,
        'periodicidad' => 'Flexible'
    ];
}

echo json_encode(['success' => true, 'clientes' => $clientes]);
?>