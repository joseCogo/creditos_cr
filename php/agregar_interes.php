<?php
// archivo: php/agregar_interes.php (VERSIÓN SIMPLE)
header('Content-Type: application/json');
date_default_timezone_set('America/Bogota');
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$prestamo_id = $_POST['prestamo_id'] ?? '';
$monto_extra = floatval($_POST['monto_extra'] ?? 0);

// Validación simple
if (empty($prestamo_id) || $monto_extra <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

mysqli_begin_transaction($conexion);

try {
    // 1. Obtener datos actuales
    $sql = "SELECT monto_total, saldo_pendiente, c.nombre as cliente_nombre
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = ?";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $prestamo_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $prestamo = mysqli_fetch_assoc($result);

    if (!$prestamo) {
        throw new Exception('Préstamo no encontrado');
    }

    // 2. Calcular nuevos valores
    $nuevo_total = floatval($prestamo['monto_total']) + $monto_extra;
    $nuevo_saldo = floatval($prestamo['saldo_pendiente']) + $monto_extra;
    
    // 3. Actualizar préstamo
    $sql_update = "UPDATE prestamos 
                   SET monto_total = ?, 
                       saldo_pendiente = ?,
                       estado = 'activo',
                       fecha_fin = NOW()
                   WHERE id = ?";
    
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ddi", $nuevo_total, $nuevo_saldo, $prestamo_id);
    
    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Error al actualizar');
    }

    mysqli_commit($conexion);

    echo json_encode([
        'success' => true,
        'message' => 'Interés agregado exitosamente',
        'datos' => [
            'cliente' => $prestamo['cliente_nombre'],
            'monto_agregado' => $monto_extra,
            'nuevo_saldo' => $nuevo_saldo,
            'nuevo_total' => $nuevo_total
        ]
    ]);

} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>