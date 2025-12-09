<?php
session_start();
header('Content-Type: application/json');
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Verificar que sea administrador
if ($_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
    exit;
}

$monto = $_POST['monto'] ?? 0;
$concepto = $_POST['concepto'] ?? 'Ingreso de capital';
$usuario_id = $_SESSION['usuario_id'];

if ($monto <= 0) {
    echo json_encode(['success' => false, 'message' => 'El monto debe ser mayor a cero']);
    exit;
}

/* ========================================================
   1️⃣ Verificar si existe la caja (id = 1)
   Si no existe, crearla automáticamente
======================================================== */
$sql_check = "SELECT id FROM caja WHERE id = 1";
$result_check = mysqli_query($conexion, $sql_check);

if (!$result_check || mysqli_num_rows($result_check) == 0) {
    // Crear caja inicial
    $crear = mysqli_query($conexion, "INSERT INTO caja (id, saldo_actual) VALUES (1, 0)");
    if (!$crear) {
        echo json_encode(['success' => false, 'message' => 'Error al crear caja inicial']);
        exit;
    }
}

mysqli_begin_transaction($conexion);

try {
    // Actualizar saldo en caja
    $sql_update = "UPDATE caja SET saldo_actual = saldo_actual + ? WHERE id = 1";
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "d", $monto);
    
    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Error al actualizar saldo');
    }
    
    // Registrar movimiento
    $sql_movimiento = "INSERT INTO movimientos_caja (tipo, monto, concepto, usuario_id) VALUES ('ingreso', ?, ?, ?)";
    $stmt_movimiento = mysqli_prepare($conexion, $sql_movimiento);
    mysqli_stmt_bind_param($stmt_movimiento, "dsi", $monto, $concepto, $usuario_id);
    
    if (!mysqli_stmt_execute($stmt_movimiento)) {
        throw new Exception('Error al registrar movimiento');
    }
    
    // Obtener nuevo saldo
    $result = mysqli_query($conexion, "SELECT saldo_actual FROM caja WHERE id = 1");
    $nuevo_saldo = mysqli_fetch_assoc($result)['saldo_actual'];
    
    mysqli_commit($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => 'Saldo agregado exitosamente',
        'nuevo_saldo' => $nuevo_saldo
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conexion);
?>
