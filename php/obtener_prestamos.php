<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once(__DIR__ . '/config_seguridad.php');
require_once(__DIR__ . '/conexion.php');
require_once(__DIR__ . '/verificar_sesion.php');

$estado_filtro = $_GET['estado'] ?? '';

try {
    // Consulta simplificada
    $sql = "SELECT 
                p.id,
                p.cliente_id,
                p.monto,
                p.interes,
                p.monto_total,
                p.saldo_pendiente,
                p.estado,
                p.fecha_inicio,
                p.fecha_fin,
                c.nombre as cliente_nombre,
                c.cedula as cliente_cedula,
                c.telefono
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id";

    // Aplicar filtro usando Prepared Statement (NO más SQL Injection)
    if (!empty($estado_filtro)) {
        // Validar que el estado sea uno de los permitidos
        $estados_validos = ['activo', 'cancelado', 'vencido'];
        if (!in_array($estado_filtro, $estados_validos)) {
            throw new Exception("Estado de préstamo inválido");
        }
        $sql .= " WHERE p.estado = ?";
    }

    $sql .= " ORDER BY p.id DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta");
    }

    if (!empty($estado_filtro)) {
        mysqli_stmt_bind_param($stmt, "s", $estado_filtro);
    }
    
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (!$resultado) {
        throw new Exception("Error SQL: " . mysqli_error($conexion));
    }

    $prestamos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Asegurar tipos de datos numéricos y escapar strings
        $row['id'] = (int)$row['id'];
        $row['cliente_id'] = (int)$row['cliente_id'];
        $row['monto'] = (float)$row['monto'];
        $row['interes'] = (float)$row['interes'];
        $row['monto_total'] = (float)$row['monto_total'];
        $row['saldo_pendiente'] = (float)$row['saldo_pendiente'];
        $row['cliente_nombre'] = Seguridad::escapar_html($row['cliente_nombre']);
        $row['cliente_cedula'] = Seguridad::escapar_html($row['cliente_cedula']);

        $prestamos[] = $row;
    }

    echo json_encode($prestamos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
