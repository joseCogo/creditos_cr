<?php
header('Content-Type: application/json');
include("conexion.php");

$prestamo_id = $_GET['prestamo_id'] ?? '';
$fecha = $_GET['fecha'] ?? '';

if (!empty($prestamo_id)) {
    // Obtener pagos de un préstamo específico
    $sql = "SELECT pg.*, u.nombre as cobrador 
            FROM pagos pg 
            LEFT JOIN usuarios u ON pg.usuario_id = u.id 
            WHERE pg.prestamo_id = ? 
            ORDER BY pg.fecha_pago DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $prestamo_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
} else if (!empty($fecha)) {
    // Obtener pagos de una fecha específica
    $sql = "SELECT pg.*, c.nombre as cliente_nombre, c.cedula, p.cuota_diaria, u.nombre as cobrador
            FROM pagos pg
            INNER JOIN prestamos p ON pg.prestamo_id = p.id
            INNER JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN usuarios u ON pg.usuario_id = u.id
            WHERE DATE(pg.fecha_pago) = ?
            ORDER BY pg.fecha_pago DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $fecha);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
} else {
    // Obtener todos los pagos (limitar a últimos 100 para mejor rendimiento)
    $sql = "SELECT pg.*, c.nombre as cliente_nombre, c.cedula, p.cuota_diaria, u.nombre as cobrador
            FROM pagos pg
            INNER JOIN prestamos p ON pg.prestamo_id = p.id
            INNER JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN usuarios u ON pg.usuario_id = u.id
            ORDER BY pg.fecha_pago DESC
            LIMIT 100";
    $resultado = mysqli_query($conexion, $sql);
}

$pagos = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $pagos[] = $row;
}

echo json_encode($pagos);
mysqli_close($conexion);
?>