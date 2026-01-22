<?php
// archivo: php/obtener_prestamos.php
header('Content-Type: application/json');
// Desactivar errores visuales para no romper el JSON
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");
include("verificar_sesion.php");

$estado_filtro = $_GET['estado'] ?? '';

try {
    // Consulta simplificada: Ya no pedimos cuotas ni periodicidad
    $sql = "SELECT 
                p.id,
                p.cliente_id,
                p.monto,        -- Capital prestado
                p.interes,      -- Porcentaje
                p.monto_total,  -- Deuda total
                p.saldo_pendiente,
                p.estado,
                p.fecha_inicio,
                p.fecha_fin,    -- Último movimiento
                c.nombre as cliente_nombre,
                c.cedula as cliente_cedula,
                c.telefono
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id";

    // Aplicar filtro si existe
    if (!empty($estado_filtro)) {
        $sql .= " WHERE p.estado = '" . mysqli_real_escape_string($conexion, $estado_filtro) . "'";
    }

    $sql .= " ORDER BY p.id DESC";

    $resultado = mysqli_query($conexion, $sql);

    if (!$resultado) {
        throw new Exception("Error SQL: " . mysqli_error($conexion));
    }

    $prestamos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Aseguramos tipos de datos numéricos para JS
        $row['id'] = (int)$row['id'];
        $row['monto'] = (float)$row['monto'];
        $row['interes'] = (float)$row['interes'];
        $row['monto_total'] = (float)$row['monto_total'];
        $row['saldo_pendiente'] = (float)$row['saldo_pendiente'];

        $prestamos[] = $row;
    }

    echo json_encode($prestamos);
} catch (Exception $e) {
    // En caso de error, devolver un JSON con el error (no HTML)
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
