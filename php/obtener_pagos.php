<?php
header('Content-Type: application/json');
include("conexion.php");

$prestamo_id = $_GET['prestamo_id'] ?? '';
$fecha = $_GET['fecha'] ?? '';

/* ===========================================================
   CONSULTA POR PRESTAMO ESPECÍFICO
   =========================================================== */
if (!empty($prestamo_id)) {

    $sql = "SELECT pg.*, 
                c.nombre as cliente_nombre, 
                c.cedula, 
                p.cuota_diaria, 
                u.nombre as cobrador,
                b.numero_boleta
            FROM pagos pg
            INNER JOIN prestamos p ON pg.prestamo_id = p.id
            INNER JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN usuarios u ON pg.usuario_id = u.id
            LEFT JOIN boletas_prestamos b ON p.id = b.prestamo_id
            WHERE pg.prestamo_id = ?
            ORDER BY pg.fecha_pago DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $prestamo_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

/* ===========================================================
   CONSULTA POR FECHA ESPECÍFICA
   =========================================================== */
} else if (!empty($fecha)) {

    $sql = "SELECT pg.*, 
                c.nombre as cliente_nombre, 
                c.cedula, 
                p.cuota_diaria, 
                u.nombre as cobrador,
                b.numero_boleta
            FROM pagos pg
            INNER JOIN prestamos p ON pg.prestamo_id = p.id
            INNER JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN usuarios u ON pg.usuario_id = u.id
            LEFT JOIN boletas_prestamos b ON p.id = b.prestamo_id
            WHERE DATE(pg.fecha_pago) = ?
            ORDER BY pg.fecha_pago DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $fecha);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

/* ===========================================================
   CONSULTA GENERAL (ULTIMOS 100)
   =========================================================== */
} else {

    $sql = "SELECT pg.*, 
                c.nombre as cliente_nombre, 
                c.cedula, 
                p.cuota_diaria, 
                u.nombre as cobrador,
                b.numero_boleta
            FROM pagos pg
            INNER JOIN prestamos p ON pg.prestamo_id = p.id
            INNERJOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN usuarios u ON pg.usuario_id = u.id
            LEFT JOIN boletas_prestamos b ON p.id = b.prestamo_id
            ORDER BY pg.fecha_pago DESC
            LIMIT 100";

    $resultado = mysqli_query($conexion, $sql);
}

/* ===========================================================
   FORMAR JSON DE RESPUESTA
   =========================================================== */

$pagos = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $pagos[] = $row;
}

echo json_encode($pagos);
mysqli_close($conexion);
?>
