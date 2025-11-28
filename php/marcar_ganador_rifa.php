<?php
// CREAR NUEVO ARCHIVO: /php/marcar_ganador_rifa.php

header('Content-Type: application/json');
session_start();
include("conexion.php");

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
    exit;
}

$prestamo_id = $_POST['prestamo_id'] ?? '';
$observacion = $_POST['observacion'] ?? 'Cliente ganador de rifa';

if (empty($prestamo_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de préstamo requerido']);
    exit;
}

mysqli_begin_transaction($conexion);

try {
    // Verificar que el préstamo existe y está activo
    $sql_check = "SELECT p.id, p.estado, p.saldo_pendiente, b.gano_rifa 
                  FROM prestamos p
                  LEFT JOIN boletas_prestamos b ON p.id = b.prestamo_id
                  WHERE p.id = ?";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $prestamo_id);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);
    $prestamo = mysqli_fetch_assoc($result);
    
    if (!$prestamo) {
        throw new Exception('Préstamo no encontrado');
    }
    
    if ($prestamo['estado'] !== 'activo') {
        throw new Exception('Solo se pueden marcar préstamos activos como ganadores');
    }
    
    if ($prestamo['gano_rifa']) {
        throw new Exception('Este cliente ya fue marcado como ganador anteriormente');
    }
    
    // Actualizar boleta como ganadora
    $sql_boleta = "UPDATE boletas_prestamos 
                   SET gano_rifa = TRUE, 
                       fecha_rifa = CURDATE(), 
                       observacion_rifa = ?
                   WHERE prestamo_id = ?";
    
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "si", $observacion, $prestamo_id);
    
    if (!mysqli_stmt_execute($stmt_boleta)) {
        throw new Exception('Error al actualizar boleta');
    }
    
    // Cancelar el préstamo y poner saldo en 0
    $sql_prestamo = "UPDATE prestamos 
                     SET saldo_pendiente = 0,
                         estado = 'cancelado'
                     WHERE id = ?";
    
    $stmt_prestamo = mysqli_prepare($conexion, $sql_prestamo);
    mysqli_stmt_bind_param($stmt_prestamo, "i", $prestamo_id);
    
    if (!mysqli_stmt_execute($stmt_prestamo)) {
        throw new Exception('Error al cancelar préstamo');
    }
    
    mysqli_commit($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => '¡Cliente marcado como ganador de rifa! El préstamo ha sido cancelado.',
        'prestamo_id' => $prestamo_id
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>