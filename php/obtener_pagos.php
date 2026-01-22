<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once(__DIR__ . '/config_seguridad.php');
require_once(__DIR__ . '/conexion.php');
require_once(__DIR__ . '/verificar_sesion.php');

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

    // Aplicar filtro de fecha usando Prepared Statement (NO más SQL Injection)
    if (!empty($fecha_filtro)) {
        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_filtro)) {
            throw new Exception("Formato de fecha inválido");
        }
        $sql .= " WHERE DATE(pg.fecha_pago) = ?";
    }

    $sql .= " ORDER BY pg.fecha_pago DESC, pg.id DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    
    if (!empty($fecha_filtro)) {
        mysqli_stmt_bind_param($stmt, "s", $fecha_filtro);
    }
    
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (!$resultado) {
        throw new Exception("Error SQL: " . mysqli_error($conexion));
    }

    $pagos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Asegurar tipos numéricos y escapar strings
        $row['id'] = (int)$row['id'];
        $row['monto_pagado'] = (float)$row['monto_pagado'];
        $row['cliente_nombre'] = Seguridad::escapar_html($row['cliente_nombre']);
        $row['cobrador'] = Seguridad::escapar_html($row['cobrador']);
        $row['metodo_pago'] = Seguridad::escapar_html($row['metodo_pago']);
        
        $pagos[] = $row;
    }

    echo json_encode($pagos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>