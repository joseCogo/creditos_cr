<?php
// archivo: php/obtener_pagos.php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include("conexion.php");
include("verificar_sesion.php");

$fecha_filtro = $_GET['fecha'] ?? '';

try {
    $sql = "SELECT 
                pg.id,
                pg.fecha_pago,
                pg.monto_pagado,
                pg.metodo_pago,
                pg.observacion,
                c.nombre as cliente_nombre,
                u.nombre as cobrador
            FROM pagos pg
            INNER JOIN prestamos p ON pg.prestamo_id = p.id
            INNER JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN usuarios u ON pg.usuario_id = u.id";

    // Aplicar filtro de fecha si existe
    if (!empty($fecha_filtro)) {
        $sql .= " WHERE DATE(pg.fecha_pago) = '" . mysqli_real_escape_string($conexion, $fecha_filtro) . "'";
    }

    $sql .= " ORDER BY pg.fecha_pago DESC, pg.id DESC";

    $resultado = mysqli_query($conexion, $sql);

    if (!$resultado) {
        throw new Exception("Error SQL: " . mysqli_error($conexion));
    }

    $pagos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Asegurar tipos numéricos
        $row['id'] = (int)$row['id'];
        $row['monto_pagado'] = (float)$row['monto_pagado'];
        
        $pagos[] = $row;
    }

    echo json_encode($pagos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>